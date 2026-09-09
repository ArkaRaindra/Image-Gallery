<?php

namespace App\Http\Controllers;

use App\Models\Comment;
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
            'parent_id' => ['nullable', 'integer', 'exists:comments,id'],
        ]);

        $post->comments()->create([
            'user_id' => $request->user()->id,
            'author_name' => $request->user()->name,
            'body' => $data['body'],
            'parent_id' => $data['parent_id'] ?? null,
        ]);

        $tagsQuery = $request->string('tags')->toString();
        $url = route('posts.show', ['post' => $post, 'tags' => $tagsQuery]) . '#comments';

        return redirect($url);
    }

    public function vote(Request $request, Comment $comment): JsonResponse
    {
        $direction = $request->string('direction')->toString();

        if (! in_array($direction, ['up', 'down'], true)) {
            return response()->json(['message' => 'Invalid direction'], 422);
        }

        $voted = session('voted_comments', []);
        $existing = $voted[$comment->id] ?? null;

        if ($existing === $direction) {
            $comment->increment('score', $direction === 'up' ? -1 : 1);
            unset($voted[$comment->id]);
            $newVote = null;
        } elseif ($existing) {
            $comment->increment('score', $direction === 'up' ? 2 : -2);
            $voted[$comment->id] = $direction;
            $newVote = $direction;
        } else {
            $comment->increment('score', $direction === 'up' ? 1 : -1);
            $voted[$comment->id] = $direction;
            $newVote = $direction;
        }

        session(['voted_comments' => $voted]);

        return response()->json([
            'score' => $comment->fresh()->score,
            'voted' => $newVote,
        ]);
    }

    public function destroy(Request $request, Comment $comment): RedirectResponse
    {
        abort_unless($request->user()->isAdmin(), 403);

        $post = $comment->post;
        $comment->delete();

        if ($post) {
            $tagsQuery = $request->string('tags')->toString();

            return redirect(route('posts.show', ['post' => $post, 'tags' => $tagsQuery]) . '#comments');
        }

        return redirect()->route('comments.index');
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

    public function update(Request $request, Comment $comment): RedirectResponse
    {
        abort_unless($request->user()->id === $comment->user_id, 403);

        $data = $request->validate([
            'body' => ['required', 'string', 'max:5000'],
        ]);

        $comment->update(['body' => $data['body']]);

        $post = $comment->post;

        if ($post) {
            $tagsQuery = $request->string('tags')->toString();

            return redirect(route('posts.show', ['post' => $post, 'tags' => $tagsQuery]) . '#comments');
        }

        return redirect()->route('comments.index');
    }
}
