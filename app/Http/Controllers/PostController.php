<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Tag;
use App\Services\PostSearchService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PostController extends Controller
{
    public function __construct(protected PostSearchService $search) {}

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
            : Tag::whereHas('posts', fn($q) => $q->whereIn('posts.id', $matchingPostIds))
            ->orderByDesc('post_count')
            ->limit(40)
            ->get();

        $singleTag = $this->resolveSingleTag($tagsQuery);

        return view('posts.index', [
            'posts' => $posts,
            'tagQuery' => $tagsQuery,
            'sidebarTags' => $sidebarTags,
            'singleTagName' => $singleTag?->name,
            'singleTagCategory' => $singleTag?->category,
        ]);
    }

    public function show(Request $request, Post $post)
    {
        $post->load('tags', 'uploader');

        $tagsQuery = $request->string('tags')->toString();

        $scopedIds = $this->search->search($tagsQuery)->pluck('id');

        $prevId = $scopedIds->filter(fn($id) => $id > $post->id)->min();
        $nextId = $scopedIds->filter(fn($id) => $id < $post->id)->max();

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

        if (! in_array($direction, ['up', 'down'], true)) {
            return response()->json(['message' => 'Invalid direction'], 422);
        }

        $voted = session('voted_posts', []);
        $existing = $voted[$post->id] ?? null;

        if ($existing === $direction) {
            $post->increment('score', $direction === 'up' ? -1 : 1);
            unset($voted[$post->id]);
            $newVote = null;
        } elseif ($existing) {
            $post->increment('score', $direction === 'up' ? 2 : -2);
            $voted[$post->id] = $direction;
            $newVote = $direction;
        } else {
            $post->increment('score', $direction === 'up' ? 1 : -1);
            $voted[$post->id] = $direction;
            $newVote = $direction;
        }

        session(['voted_posts' => $voted]);

        return response()->json([
            'score' => $post->fresh()->score,
            'voted' => $newVote,
        ]);
    }

    protected function resolveSingleTag(string $tagsQuery): ?Tag
    {
        $tokens = collect(explode(' ', trim($tagsQuery)))->filter();

        if ($tokens->count() !== 1) {
            return null;
        }

        $token = $tokens->first();

        if (Str::startsWith($token, ['-', 'rating:'])) {
            return null;
        }

        return Tag::where('name', $token)->first();
    }

    public function download(Post $post)
    {
        $post->load('tags');

        $grouped = $post->tags->groupBy('category');

        $character = ($grouped->get('character', collect())->pluck('name')->join('_'));
        $copyright = ($grouped->get('copyright', collect())->pluck('name')->join('_'));
        $artist = ($grouped->get('artist', collect())->pluck('name')->join('_'));
        $prefix = collect([$character, $copyright, $artist ? 'drawn_by_'.$artist : null])->filter()->implode('_');

        $baseName = pathinfo($post->file_name, PATHINFO_FILENAME);
        $extension = pathinfo($post->file_name, PATHINFO_EXTENSION);

        $fileName = '__'.$prefix.'__'.$baseName.'.'.$extension;

        return \Illuminate\Support\Facades\Storage::disk('public')->download($post->file_path, $fileName);
    }
}
