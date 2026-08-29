<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Tag;
use App\Services\PostSearchService;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function __construct(protected PostSearchService $search)
    {
    }

    public function index(Request $request)
    {
        $tags = $request->string('tags')->toString();

        $posts = $this->search
            ->search($tags)
            ->with('tags')
            ->paginate(24)
            ->withQueryString();

        $popularTags = Tag::orderByDesc('post_count')->limit(20)->get();

        return view('posts.index', [
            'posts' => $posts,
            'tagQuery' => $tags,
            'popularTags' => $popularTags,
        ]);
    }

    public function show(Post $post)
    {
        $post->load('tags', 'uploader');

        return view('posts.show', [
            'post' => $post,
        ]);
    }
}