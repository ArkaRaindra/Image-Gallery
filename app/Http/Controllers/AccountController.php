<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AccountController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user();

        $stats = [
            'posts' => Post::where('uploader_id', $user->id)->count(),
            'comments' => Comment::where('user_id', $user->id)->count(),
            'favorites' => $user->favorites()->count(),
        ];

        return view('account.show' , [
            'user' => $user,
            'stats' => $stats,
            'recentFavorites' => $user->favorites()->take(5)->get(),
            'recentPosts' => Post::where('uploader_id', $user->id)->latest()->take(5)->get(),
        ]);
    }

    public function updateAvatar(Request $request): RedirectResponse
    {
        $request->validate([
            'avatar' => ['required', 'image', 'max:2048'],
        ]);

        $user = $request->user();

        if ($user->avatar_path) {
            Storage::disk('public')->delete($user->avatar_path);
        }

        $path = $request->file('avatar')->store('avatars', 'public');

        $user->update(['avatar_path' => $path]);

        return back();
    }
}