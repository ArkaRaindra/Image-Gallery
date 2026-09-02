<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CommentController extends Controller
{
    public function store(Request $request, Post $post): RedirectResponse
    {
        $data = $request->validate([
            'body' => ['required', 'string', 'max:5000'],
        ]);

        $post->comments()->create([
            'user_id' => $request->user()->id,
            'author_name' => $request->user()->name,
            'body' => $data['body'],
        ]);

        $tagsQuery = $request->string('tags')->toString();
        $url = route('posts.show', ['post' => $post, 'tags' => $tagsQuery]) . '#comments';

        return redirect($url);
    }

    public function uploadImage(Request $request): JsonResponse
    {
        $request->validate([
            'image' => ['required', 'image', 'max:5120'],
        ]);

        $path = $request->file('image')->store('comments', 'public');

        return response()->json([
            'url' => Storage::disk('public')->url($path),
        ]);
    }
}