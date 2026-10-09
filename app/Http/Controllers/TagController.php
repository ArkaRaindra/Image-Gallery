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

    /**
     * Category of each given tag name that already exists, as {name: category}.
     * Names that do not exist yet are left out. Used by the upload form.
     */
    public function lookup(Request $request): JsonResponse
    {
        $names = collect((array) $request->query('names', []))
            ->filter(fn ($name) => is_string($name))
            ->map(fn (string $name) => Tag::normalizeName($name))
            ->filter()
            ->unique()
            ->take(100)
            ->values();

        return response()->json((object) Tag::whereIn('name', $names)->pluck('category', 'name')->all());
    }
}