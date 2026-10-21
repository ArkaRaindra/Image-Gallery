<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use App\Support\NoteFormatter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * The wiki page of a tag is the tag's description, the same text that the
 * Wiki tab on the posts page shows.
 */
class WikiPageController extends Controller
{
    private const ORDERS = ['updated', 'title'];

    public function index(Request $request): View
    {
        $order = $request->query('order');

        $filters = [
            'title' => trim($request->string('title')->toString()),
            'other_names' => trim($request->string('other_names')->toString()),
            'text' => trim($request->string('text')->toString()),
            'order' => in_array($order, self::ORDERS, true) ? $order : 'updated',
        ];

        $query = Tag::query()->withWiki();

        if ($filters['title'] !== '') {
            $query->where('name', 'like', '%'.Tag::normalizeName($filters['title']).'%');
        }

        if ($filters['other_names'] !== '') {
            $query->where('other_names', 'like', '%'.$filters['other_names'].'%');
        }

        if ($filters['text'] !== '') {
            $query->where('description', 'like', '%'.$filters['text'].'%');
        }

        if ($filters['order'] === 'title') {
            $query->orderBy('name');
        } else {
            $query->orderByDesc('wiki_updated_at')->orderByDesc('id');
        }

        return view('wiki.index', [
            'pages' => $query->paginate(25, ['id', 'name', 'category', 'wiki_updated_at'])->withQueryString(),
            'filters' => $filters,
        ]);
    }

    public function show(string $title): View|Response
    {
        $tag = $this->findTag($title);

        if ($tag === null || ! $tag->hasWiki()) {
            return response()->view('wiki.missing', [
                'title' => Tag::normalizeName($title),
                'tag' => $tag,
            ], 404);
        }

        $tag->load('wikiUpdater');

        return view('wiki.show', [
            'wikiTag' => $tag,
            'bodyHtml' => NoteFormatter::sanitize($tag->description),
        ]);
    }

    public function create(Request $request): View
    {
        $prefill = Tag::normalizeName((string) ($request->old('tag') ?? $request->query('tag', '')));

        $existing = $prefill !== '' ? $this->findTag($prefill) : null;

        return view('wiki.create', [
            'prefillTag' => $prefill,
            'existingWikiTag' => $existing?->hasWiki() ? $existing : null,
        ]);
    }

    /**
     * Writes the wiki of a tag that exists but has no wiki yet.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->merge(['tag' => Tag::normalizeName((string) $request->input('tag'))]);

        $data = $request->validate([
            'tag' => ['required', 'string', 'max:255'],
            'other_names' => ['nullable', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:200000'],
        ]);

        $tag = $this->findTag($data['tag']);

        if ($tag === null) {
            throw ValidationException::withMessages([
                'tag' => 'This tag does not exist. A wiki page can only be created for an existing tag.',
            ]);
        }

        if ($tag->hasWiki()) {
            throw ValidationException::withMessages([
                'tag' => 'The tag "'.$tag->name.'" already has a wiki page, so no new page was created.',
            ]);
        }

        $tag->update([
            'description' => $this->cleanBody($data['body']),
            'other_names' => $this->cleanOtherNames($data['other_names'] ?? null),
        ]);

        return redirect()->route('wiki.show', $tag->name)
            ->with('status', 'Wiki page created.');
    }

    public function edit(string $title): View
    {
        $tag = $this->findTag($title);

        abort_if($tag === null || ! $tag->hasWiki(), 404);

        return view('wiki.edit', [
            'wikiTag' => $tag,
            'body' => NoteFormatter::sanitize($tag->description),
        ]);
    }

    public function update(Request $request, string $title): RedirectResponse
    {
        $tag = $this->findTag($title);

        abort_if($tag === null || ! $tag->hasWiki(), 404);

        $data = $request->validate([
            'other_names' => ['nullable', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:200000'],
        ]);

        $body = $this->cleanBody($data['body']);
        $otherNames = $this->cleanOtherNames($data['other_names'] ?? null);

        $unchanged = $body === (string) $tag->description
            && (string) $otherNames === (string) $tag->other_names;

        if ($unchanged) {
            return redirect()->route('wiki.show', $tag->name)
                ->with('status', 'No changes were made.');
        }

        $tag->update([
            'description' => $body,
            'other_names' => $otherNames,
        ]);

        return redirect()->route('wiki.show', $tag->name)
            ->with('status', 'Wiki page updated.');
    }

    /**
     * Image for the wiki editor. Stored next to the images of the admin editor.
     */
    public function uploadImage(Request $request): JsonResponse
    {
        $request->validate([
            'image' => ['required', 'image', 'max:5120'],
        ]);

        $path = $request->file('image')->store('wiki', 'public');

        return response()->json([
            'url' => Storage::disk('public')->url($path),
        ]);
    }

    private function findTag(string $name): ?Tag
    {
        return Tag::query()->where('name', Tag::normalizeName($name))->first();
    }

    /**
     * Remove unsafe HTML (scripts, event handlers, ...) and refuse an empty page.
     */
    private function cleanBody(string $body): string
    {
        $html = str_replace(["\r\n", "\r"], "\n", NoteFormatter::sanitize($body));

        if (! Tag::htmlHasContent($html)) {
            throw ValidationException::withMessages([
                'body' => 'The wiki content cannot be empty.',
            ]);
        }

        return $html;
    }

    private function cleanOtherNames(?string $value): ?string
    {
        $names = trim((string) preg_replace('/\s+/', ' ', (string) $value));

        return $names === '' ? null : $names;
    }
}