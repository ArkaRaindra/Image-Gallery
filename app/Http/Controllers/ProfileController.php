<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;


class ProfileController extends Controller
{
    public function show(User $user)
    {
        $stats = [
            'posts' => Post::where('uploader_id', $user->id)->count(),
            'comments' => Comment::where('user_id', $user->id)->count(),
            'favorites' => $user->favorites()->count(),
        ];

        $recentPosts = Post::where('uploader_id', $user->id)
            ->approved()
            ->latest()
            ->take(5)
            ->get();

        return view('users.show', [
            'profileUser' => $user,
            'stats' => $stats,
            'recentPosts' => $recentPosts,
        ]);
    }
}