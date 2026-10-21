<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use App\Support\NoteFormatter;
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
            'description' => NoteFormatter::sanitize($tag->description),
            'other_names' => $tag->otherNameList(),
        ]);
    }

    public function autocomplete(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));

        if ($q === '') {
            return response()->json([]);
        }

        $query = Tag::where('name', 'like', $q.'%');

        // The wiki search only wants tags that have a wiki page.
        if ($request->boolean('with_wiki')) {
            $query->withWiki();
        }

        $tags = $query
            ->orderByDesc('post_count')
            ->limit(15)
            ->get(['name', 'category', 'post_count', 'description'])
            ->map(fn (Tag $tag) => [
                'name' => $tag->name,
                'category' => $tag->category,
                'post_count' => $tag->post_count,
                'has_wiki' => $tag->hasWiki(),
            ]);

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