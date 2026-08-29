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
        ]);
    }

    public function show(Post $post)
    {
        $post->load('tags', 'uploader');

        return view('posts.show', [
            'post' => $post,
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