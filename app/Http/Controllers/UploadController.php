<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Tag;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UploadController extends Controller
{
    public function create()
    {
        return view('posts.upload');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'file' => ['required', 'image', 'max:20480'],
            'rating' => ['required', 'in:general,sensitive,questionable,explicit'],
            'tags' => ['required', 'string'],
            'source' => ['nullable', 'url', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
        ]);

        $path = $request->file('file')->store('posts', 'public');
        $fullPath = Storage::disk('public')->path($path);

        $post = Post::create([
            'uploader_id' => $request->user()->id,
            'file_path' => $path,
            'file_name' => basename($path),
            'file_ext' => pathinfo($path, PATHINFO_EXTENSION),
            'file_size' => Storage::disk('public')->size($path),
            'width' => 0,
            'height' => 0,
            'thumbnail_path' => $path,
            'md5' => md5_file($fullPath),
            'rating' => $data['rating'],
            'source' => $data['source'] ?? null,
            'description' => $data['description'] ?? null,
            'score' => 0,
            'is_approved' => $request->user()->isAdmin(),
        ]);

        if ($imageSize = @getimagesize($fullPath)) {
            $post->update(['width' => $imageSize[0], 'height' => $imageSize[1]]);
        }

        $tagIds = collect(explode(' ', trim($data['tags'])))
            ->map(fn ($t) => trim($t))
            ->filter()
            ->map(fn ($name) => Tag::firstOrCreate(['name' => $name], ['category' => 'general'])->id);

        $post->tags()->sync($tagIds);

        Tag::recalculateAllPostCounts();

        return redirect()->route('posts.show', $post)->with(
            'status',
            $post->is_approved ? 'Post uploaded!' : 'Post uploaded and is pending admin approval.'
        );
    }
}