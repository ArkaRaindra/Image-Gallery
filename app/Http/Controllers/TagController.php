<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TagController extends Controller
{
    public function wiki(Tag $tag): JsonResponse
    {
        return response()->json([
            'name' => $tag->name,
            'category' => $tag->category,
            'post_count' => $tag->post_count,
            'description' => $tag->description,
        ]);
    }

    public function autocomplete(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));

        if ($q === '') {
            return response()->json([]);
        }

        $tags = Tag::where('name', 'like', $q.'%')
            ->orderByDesc('post_count')
            ->limit(15)
            ->get(['name', 'category', 'post_count']);

        return response()->json($tags);
    }
}