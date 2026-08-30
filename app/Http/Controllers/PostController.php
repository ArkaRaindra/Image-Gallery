<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Tag;
use App\Services\PostSearchService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PostController extends Controller
{
    public function __construct(protected PostSearchService $search)
    {
    }

    public function index(Request $request)
    {
        $tagsQuery = $request->string('tags')->toString();

        $posts = $this->search
            ->search($tagsQuery)
            ->with('tags')
            ->paginate(24)
            ->withQueryString();

        $matchingPostIds = $this->search->search($tagsQuery)->pluck('id');

        $sidebarTags = blank($tagsQuery)
            ? Tag::orderByDesc('post_count')->limit(40)->get()
            : Tag::whereHas('posts', fn ($q) => $q->whereIn('posts.id', $matchingPostIds))
                ->orderByDesc('post_count')
                ->limit(40)
                ->get();

        return view('posts.index', [
            'posts' => $posts,
            'tagQuery' => $tagsQuery,
            'sidebarTags' => $sidebarTags,
            'singleTagName' => $this->resolveSingleTagName($tagsQuery),
            'votedPosts' => session('voted_posts', []),
        ]);
    }

    public function show(Request $request, Post $post)
    {
        $post->load('tags', 'uploader');

        $tagsQuery = $request->string('tags')->toString();

        $scopedIds = $this->search->search($tagsQuery)->pluck('id');

        // list dipesan id terbaru dulu (descending), jadi "prev" = id lebih besar, "next" = id lebih kecil
        $prevId = $scopedIds->filter(fn ($id) => $id > $post->id)->min();
        $nextId = $scopedIds->filter(fn ($id) => $id < $post->id)->max();

        return view('posts.show', [
            'post' => $post,
            'tagQuery' => $tagsQuery,
            'prevId' => $prevId,
            'nextId' => $nextId,
            'votedPosts' => session('voted_posts', []),
        ]);
    }

    public function vote(Request $request, Post $post)
    {
        $direction = $request->string('direction')->toString();
        $voted = session('voted_posts', []);

        if (isset($voted[$post->id])) {
            return response()->json([
                'score' => $post->score,
                'voted' => $voted[$post->id],
            ]);
        }

        if ($direction === 'up') {
            $post->increment('score');
        } elseif ($direction === 'down') {
            $post->decrement('score');
        } else {
            return response()->json(['message' => 'Invalid direction'], 422);
        }

        $voted[$post->id] = $direction;
        session(['voted_posts' => $voted]);

        return response()->json([
            'score' => $post->fresh()->score,
            'voted' => $direction,
        ]);
    }

    protected function resolveSingleTagName(string $tagsQuery): ?string
    {
        $tokens = collect(explode(' ', trim($tagsQuery)))->filter();

        if ($tokens->count() !== 1) {
            return null;
        }

        $token = $tokens->first();

        if (Str::startsWith($token, ['-', 'rating:'])) {
            return null;
        }

        return Tag::where('name', $token)->exists() ? $token : null;
    }
}