<?php

namespace App\Http\Controllers;

use App\Models\ArtistUrl;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ArtistUrlController extends Controller
{
    private const ORDERS = ['id', 'created', 'updated'];

    /**
     * Every URL of every artist.
     */
    public function index(Request $request): View
    {
        $order = $request->query('order');
        $active = $request->query('active');

        $filters = [
            'artist_name' => trim($request->string('artist_name')->toString()),
            'url' => trim($request->string('url')->toString()),
            'active' => in_array($active, ['true', 'false'], true) ? $active : '',
            'order' => in_array($order, self::ORDERS, true) ? $order : 'id',
        ];

        $query = ArtistUrl::query()
            ->select('artist_urls.*')
            ->join('artists', 'artists.id', '=', 'artist_urls.artist_id')
            ->join('tags', 'tags.id', '=', 'artists.tag_id')
            ->where('tags.category', 'artist')
            ->with('artist.tag');

        if ($filters['artist_name'] !== '') {
            $query->where('tags.name', 'like', '%'.Tag::normalizeName($filters['artist_name']).'%');
        }

        if ($filters['url'] !== '') {
            $query->where('artist_urls.url', 'like', '%'.$filters['url'].'%');
        }

        if ($filters['active'] !== '') {
            $query->where('artist_urls.is_active', $filters['active'] === 'true');
        }

        match ($filters['order']) {
            'created' => $query->orderByDesc('artist_urls.created_at')->orderByDesc('artist_urls.id'),
            'updated' => $query->orderByDesc('artist_urls.updated_at')->orderByDesc('artist_urls.id'),
            default => $query->orderByDesc('artist_urls.id'),
        };

        return view('artists.urls', [
            'urls' => $query->paginate(20)->withQueryString(),
            'filters' => $filters,
        ]);
    }
}
