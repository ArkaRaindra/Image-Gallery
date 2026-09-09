<?php

namespace App\Http\Controllers;

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
}