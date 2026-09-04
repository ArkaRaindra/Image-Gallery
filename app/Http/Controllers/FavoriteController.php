<?php

namespace App\Http\Controllers;

use App\Models\Post;
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
        $posts = $request->user()->favorites()->paginate(24);
        
        return view('favorites.index', [
            'posts' => $posts,
        ]);
    }
}