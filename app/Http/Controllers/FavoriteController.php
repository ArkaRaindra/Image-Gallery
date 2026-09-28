<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Tag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function toggle(Request $request, Post $post): JsonResponse
    {
        $user = $request->user();

        $alreadyFavorited = $post->favoritedBy()->where('users.id', $user->id)->exists();

        if ($alreadyFavorited) {
            $post->favoritedBy()->detach($user->id);
            $favorited = false;
        } else {
            $post->favoritedBy()->attach($user->id);
            $favorited = true;
        }

        return response()->json([
            'favorited' => $favorited,
            'count' => $post->favoritedBy()->count(),
        ]);
    }

    public function index(Request $request)
    {
        $user = $request->user();

        $posts = $user->favorites()->with(['tags', 'uploader'])->paginate(24);

        $favoritePostIds = $user->favorites()->pluck('posts.id');

        // Only tags that appear on favorited posts, but each tag still shows
        // its overall post_count (same as the main index sidebar).
        $sidebarTags = Tag::whereHas('posts', fn ($q) => $q->whereIn('posts.id', $favoritePostIds))
            ->where('post_count', '>', 0)
            ->orderByDesc('post_count')
            ->limit(40)
            ->get();

        return view('favorites.index', [
            'posts' => $posts,
            'tagQuery' => '',
            'sidebarTags' => $sidebarTags,
        ]);
    }
}