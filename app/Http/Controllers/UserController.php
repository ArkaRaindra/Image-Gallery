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
            ->limit(0)
            ->get(['id', 'name']);

        return response()->json($users);
    }
}