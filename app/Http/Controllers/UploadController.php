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
        return view('posts.upload', ['categories' => Tag::CATEGORIES]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'file' => ['required', 'file', 'mimes:jpg,jpeg,png,gif,webp,mp4', 'max:102400'],
            'rating' => ['required', 'in:general,sensitive,questionable,explicit'],
            'tags' => ['required', 'string'],
            'tag_categories' => ['nullable', 'json'],
            'source' => ['nullable', 'url', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'parent_id' => ['nullable', 'integer', 'min:1', 'exists:posts,id'],
        ]);

        $path = $request->file('file')->store('posts', 'public');
        $fullPath = Storage::disk('public')->path($path);

        $post = Post::create([
            'uploader_id' => $request->user()->id,
            'parent_id' => $data['parent_id'] ?? null,
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

        // Category picked for each new tag on the form, as {tag_name: category}.
        // It only applies to tags that do not exist yet: an existing tag keeps its category.
        $chosenCategories = json_decode($data['tag_categories'] ?? '[]', true);
        $chosenCategories = is_array($chosenCategories) ? $chosenCategories : [];

        $tagIds = collect(preg_split('/\s+/', trim($data['tags'])))
            ->map(fn ($name) => Tag::normalizeName($name))
            ->filter()
            ->unique()
            ->map(function (string $name) use ($chosenCategories) {
                $category = $chosenCategories[$name] ?? 'general';

                if (! is_string($category) || ! in_array($category, Tag::CATEGORIES, true)) {
                    $category = 'general';
                }

                return Tag::firstOrCreate(['name' => $name], ['category' => $category])->id;
            });

        $post->tags()->sync($tagIds);

        Tag::recalculateAllPostCounts();

        return redirect()->route('posts.show', $post)->with(
            'status',
            $post->is_approved ? 'Post uploaded!' : 'Post uploaded and is pending admin approval.'
        );
    }
}