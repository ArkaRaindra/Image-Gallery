<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function store(Request $request, Post $post): RedirectResponse
    {
        $data = $request->validate([
            'author_name' => ['nullable', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:5000'],
        ]);

        $post->comments()->create([
            'author_name' => filled($data['author_name'] ?? null) ? $data['author_name'] : 'Anonymous',
            'body' => $data['body'],
        ]);

        $tagsQuery = $request->string('tags')->toString();
        $url = route('posts.show', ['post' => $post, 'tags' => $tagsQuery]);

        return redirect($url);
    }
}