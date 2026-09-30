<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\NoteVersion;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function autocomplete(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));

        if ($q === '') {
            return response()->json([]);
        }

        $users = User::where('name', 'like', $q . '%')
            ->orderBy('name')
            ->limit(10)
            ->get(['id', 'name', 'avatar_path']);

        return response()->json($users->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'initial' => strtoupper(substr($user->name, 0, 1)),
                'avatar' => $user->avatarUrl(),
                'profile_url' => route('users.show', $user),
            ];
        }));
    }

    /**
     * Data for the username hover card.
     */
    public function card(User $user): JsonResponse
    {
        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'initial' => strtoupper(substr($user->name, 0, 1)),
            'avatar_url' => $user->avatarUrl(),
            'profile_url' => route('users.show', $user),
            'role_label' => $user->roleLabel(),
            'badge_class' => $user->roleBadgeBgClass() . ' ' . $user->roleTextClass(),
            'name_class' => $user->roleDarkTextClass(),
            'joined_at' => $user->created_at?->format('Y-m-d'),
            'stats' => [
                'uploads' => Post::where('uploader_id', $user->id)->count(),
                'tag_edits' => 0,
                'note_edits' => NoteVersion::where('updater_id', $user->id)->count(),
                'favorites' => $user->favorites()->count(),
                'comments' => Comment::where('user_id', $user->id)->count(),
                'forum_posts' => 0,
            ],
        ]);
    }
}