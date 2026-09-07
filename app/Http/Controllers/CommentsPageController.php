<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use Illuminate\Http\Request;

class CommentsPageController extends Controller
{
    public function index(Request $request)
    {
        $query = Comment::query()->with(['post', 'user'])->latest();

        if ($commenter = $request->string('commenter')->toString()) {
            $query->where('author_name', 'like', "%{$commenter}%");
        }

        if ($text = $request->string('text')->toString()) {
            $query->where('body', 'like', "%{$text}%");
        }

        if ($tag = $request->string('tags')->toString()) {
            $query->whereHas('post.tags', fn ($q) => $q->where('name', $tag));
        }

        if ($request->string('order')->toString() === 'oldest') {
            $query->reorder('created_at', 'asc');
        }

        if ($request->boolean('on_my_uploads') && $request->user()) {
            $query->whereHas('post', fn ($q) => $q->where('uploader_id', $request->user()->id));
        }

        $comments = $query->paginate(20)->withQueryString();

        return view('comments.index', [
            'comments' => $comments,
            'filters' => $request->only(['commenter', 'text', 'tags', 'order']),
        ]);
    }
}