<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user();

        $stats = [
            'posts' => Post::where('uploader_id', $user->id)->count(),
            'comments' => Comment::where('user_id', $user->id)->count(),
        ];

        return view('account.show' , [
            'user' => $user,
            'stats' => $stats,
        ]);
    }
}