<?php

namespace App\Http\Controllers;

use App\Models\Note;
use App\Models\NoteVersion;
use App\Models\Post;
use App\Support\NoteFormatter;
use Illuminate\Http\Request;

class NoteController extends Controller
{
    public function index(Request $request)
    {
        $query = Note::query()->with('post')->latest('id');

        if ($noteText = $request->string('note')->toString()) {
            $query->where('body', 'like', "%{$noteText}%");
        }

        if ($tag = $request->string('tags')->toString()) {
            $query->whereHas('post.tags', fn ($q) => $q->where('name', $tag));
        }

        $notes = $query->paginate(25)->withQueryString();

        return view('notes.index', [
            'notes' => $notes,
            'filters' => $request->only(['note', 'tags']),
        ]);
    }

    public function store(Request $request, Post $post)
    {
        abort_unless($post->canManageNotes($request->user()), 403);
        abort_if($post->isVideo(), 400, 'Notes are only supported on image posts.');

        $data = $request->validate([
            'body' => ['nullable', 'string', 'max:5000'],
            'x' => ['required', 'numeric', 'min:0', 'max:100'],
            'y' => ['required', 'numeric', 'min:0', 'max:100'],
            'width' => ['required', 'numeric', 'min:0.05', 'max:100'],
            'height' => ['required', 'numeric', 'min:0.05', 'max:100'],
        ]);

        $note = $post->notes()->create([
            'creator_id' => $request->user()->id,
            'body' => NoteFormatter::sanitize($data['body'] ?? ''),
            'x' => $data['x'],
            'y' => $data['y'],
            'width' => $data['width'],
            'height' => $data['height'],
            'version' => 1,
        ]);

        $note->recordVersion($request->user()->id, true);

        return response()->json(['note' => $note->toOverlayArray()]);
    }

    public function update(Request $request, Note $note)
    {
        abort_unless($note->post->canManageNotes($request->user()), 403);

        $data = $request->validate([
            'body' => ['nullable', 'string', 'max:5000'],
            'x' => ['required', 'numeric', 'min:0', 'max:100'],
            'y' => ['required', 'numeric', 'min:0', 'max:100'],
            'width' => ['required', 'numeric', 'min:0.05', 'max:100'],
            'height' => ['required', 'numeric', 'min:0.05', 'max:100'],
        ]);

        $note->update([
            'body' => NoteFormatter::sanitize($data['body'] ?? ''),
            'x' => $data['x'],
            'y' => $data['y'],
            'width' => $data['width'],
            'height' => $data['height'],
            'version' => $note->version + 1,
        ]);

        $note->recordVersion($request->user()->id, false);

        return response()->json(['note' => $note->toOverlayArray()]);
    }

    public function destroy(Request $request, Note $note)
    {
        abort_unless($note->post->canManageNotes($request->user()), 403);

        $note->delete();

        return response()->json(['message' => 'deleted']);
    }

    public function previewBody(Request $request)
    {
        $data = $request->validate([
            'body' => ['nullable', 'string', 'max:5000'],
        ]);

        return response()->json([
            'html' => NoteFormatter::sanitize($data['body'] ?? ''),
        ]);
    }

    public function changes(Request $request)
    {
        $query = NoteVersion::query()->with(['note.post', 'updater'])->latest('id');

        $versions = $query->paginate(25)->withQueryString();

        return view('notes.changes', [
            'versions' => $versions,
        ]);
    }

    public function history(Request $request, Note $note)
    {
        $note->load('post');

        $versions = $note->versions()->with('updater')->paginate(25);

        if ($request->wantsJson()) {
            return response()->json([
                'note' => [
                    'id' => $note->id,
                    'post_id' => $note->post_id,
                ],
                'versions' => $versions->getCollection()->map(fn (NoteVersion $version) => [
                    'version' => $version->version,
                    'body' => $version->body,
                    'is_new' => $version->is_new,
                    'updater' => $version->updater?->name,
                    'updater_url' => $version->updater ? route('users.show', $version->updater) : null,
                    'created_at' => $version->created_at->format('Y-m-d H:i'),
                ])->values(),
                'current_page' => $versions->currentPage(),
                'last_page' => $versions->lastPage(),
            ]);
        }

        return view('notes.history', [
            'note' => $note,
            'versions' => $versions,
        ]);
    }

    public function revert(Request $request, Note $note, NoteVersion $version)
    {
        abort_unless($note->post->canManageNotes($request->user()), 403);
        abort_unless($version->note_id === $note->id, 404);

        $note->update([
            'body' => $version->body,
            'x' => $version->x,
            'y' => $version->y,
            'width' => $version->width,
            'height' => $version->height,
            'version' => $note->version + 1,
        ]);

        $note->recordVersion($request->user()->id, false);

        return redirect()->route('notes.history', $note)
            ->with('status', "Reverted to version {$version->version}.");
    }
}