<?php

namespace App\Http\Controllers;

use App\Models\Artist;
use App\Models\Tag;
use App\Services\ArtistService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ArtistController extends Controller
{
    private const ORDERS = ['created', 'updated', 'name', 'post_count'];

    private const YES_NO = ['yes', 'no'];

    public function __construct(private readonly ArtistService $artists) {}

    public function index(Request $request): View
    {
        $order = $request->query('order');
        $deleted = $request->query('deleted');
        $hasTag = $request->query('has_tag');

        $filters = [
            'name' => trim($request->string('name')->toString()),
            'url' => trim($request->string('url')->toString()),
            'deleted' => in_array($deleted, self::YES_NO, true) ? $deleted : '',
            'has_tag' => in_array($hasTag, self::YES_NO, true) ? $hasTag : '',
            'order' => in_array($order, self::ORDERS, true) ? $order : 'created',
        ];

        $query = Artist::query()
            ->select('artists.*')
            ->join('tags', 'tags.id', '=', 'artists.tag_id')
            ->where('tags.category', 'artist')
            ->with('tag');

        if ($filters['name'] !== '') {
            $like = '%'.Tag::normalizeName($filters['name']).'%';

            $query->where(fn ($q) => $q
                ->where('tags.name', 'like', $like)
                ->orWhere('artists.other_names', 'like', $like)
                ->orWhere('artists.group_name', 'like', $like));
        }

        if ($filters['url'] !== '') {
            $query->whereHas('urls', fn ($q) => $q->where('url', 'like', '%'.$filters['url'].'%'));
        }

        if ($filters['deleted'] !== '') {
            $query->where('artists.is_deleted', $filters['deleted'] === 'yes');
        }

        // "Has tag" means that the tag of the artist has posts.
        if ($filters['has_tag'] === 'yes') {
            $query->where('tags.post_count', '>', 0);
        } elseif ($filters['has_tag'] === 'no') {
            $query->where('tags.post_count', 0);
        }

        match ($filters['order']) {
            'name' => $query->orderBy('tags.name'),
            'updated' => $query->orderByDesc('artists.updated_at')->orderByDesc('artists.id'),
            'post_count' => $query->orderByDesc('tags.post_count')->orderBy('tags.name'),
            default => $query->orderByDesc('artists.id'),
        };

        return view('artists.index', [
            'artists' => $query->paginate(25)->withQueryString(),
            'filters' => $filters,
        ]);
    }

    public function create(): View
    {
        return view('artists.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->merge(['name' => Tag::normalizeName((string) $request->input('name'))]);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'regex:/^[^,*\-][^,*]*$/'],
            'other_names' => ['nullable', 'string', 'max:255'],
            'group_name' => ['nullable', 'string', 'max:255'],
            'urls' => ['nullable', 'string', 'max:50000'],
        ], [
            'name.regex' => 'The name cannot contain commas or *, and cannot start with -.',
        ]);

        $artist = $this->artists->create($data['name'], [
            'other_names' => $this->artists->cleanOtherNames($data['other_names'] ?? null),
            'group_name' => $this->artists->cleanGroupName($data['group_name'] ?? null),
            'urls' => $this->artists->parseUrls($data['urls'] ?? null),
        ]);

        return redirect()->route('artists.index', ['name' => $artist->tag->name])
            ->with('status', 'Artist created.');
    }

    public function edit(Artist $artist): View
    {
        $artist->load(['tag', 'urls']);

        return view('artists.edit', ['artist' => $artist]);
    }

    public function update(Request $request, Artist $artist): RedirectResponse
    {
        $artist->load('tag');

        $request->merge(['name' => Tag::normalizeName((string) $request->input('name'))]);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'regex:/^[^,*\-][^,*]*$/'],
            'other_names' => ['nullable', 'string', 'max:255'],
            'group_name' => ['nullable', 'string', 'max:255'],
            'urls' => ['nullable', 'string', 'max:50000'],
            'is_deleted' => ['nullable', 'boolean'],
        ], [
            'name.regex' => 'The name cannot contain commas or *, and cannot start with -.',
        ]);

        $taken = Tag::query()
            ->where('name', $data['name'])
            ->where('id', '!=', $artist->tag_id)
            ->exists();

        if ($taken) {
            throw ValidationException::withMessages([
                'name' => 'A tag with this name already exists.',
            ]);
        }

        $changes = $this->artists->update($artist, [
            'name' => $data['name'],
            'other_names' => $this->artists->cleanOtherNames($data['other_names'] ?? null),
            'group_name' => $this->artists->cleanGroupName($data['group_name'] ?? null),
            'is_deleted' => $request->boolean('is_deleted'),
            'urls' => $this->artists->parseUrls($data['urls'] ?? null),
        ]);

        return redirect()->route('artists.index', ['name' => $data['name']])
            ->with('status', $changes === [] ? 'No changes were made.' : 'Artist updated.');
    }
}
