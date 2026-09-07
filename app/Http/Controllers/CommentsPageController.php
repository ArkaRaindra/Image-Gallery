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

        if (($score = $request->string('score')->toString()) && is_numeric($score)) {
            $query->where('score', (int) $score);
        }

        match ($request->string('order')->toString()) {
            'oldest' => $query->reorder('created_at', 'asc'),
            'updated' => $query->reorder('updated_at', 'desc'),
            'score_desc' => $query->reorder('score', 'desc'),
            'score_asc' => $query->reorder('score', 'asc'),
            default => null,
        };

        if ($request->boolean('on_my_uploads') && $request->user()) {
            $query->whereHas('post', fn ($q) => $q->where('uploader_id', $request->user()->id));
        }

        $comments = $query->paginate(20)->withQueryString();

        return view('comments.index', [
            'comments' => $comments,
            'filters' => $request->only(['commenter', 'text', 'tags', 'score', 'order']),
            'votedComments' => session('voted_comments', []),
        ]);
    }
}