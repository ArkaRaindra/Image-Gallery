<?php

namespace App\Http\Controllers;

use App\Models\Post;
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

        return view('posts.index', [
            'posts' => $posts,
            'tags' => $tags,
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