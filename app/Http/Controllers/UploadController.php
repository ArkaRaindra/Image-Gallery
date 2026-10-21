<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Tag;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class UploadController extends Controller
{
    /**
     * Rating tags of the autotagger (rating:g, rating:s, ...) and the rating they stand for.
     */
    private const AUTOTAGGER_RATINGS = [
        'g' => 'general',
        's' => 'sensitive',
        'q' => 'questionable',
        'e' => 'explicit',
    ];

    public function create()
    {
        return view('posts.upload', ['categories' => Tag::CATEGORIES]);
    }

    /**
     * Sends the picked image to the autotagger and returns its predicted tags
     * as {tags: [{name, score}], rating}. The browser cannot call the autotagger
     * itself (CORS), so the upload form asks this endpoint instead.
     */
    public function autotag(Request $request): JsonResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:jpg,jpeg,png,gif,webp', 'max:102400'],
        ]);

        $file = $request->file('file');
        $stream = fopen($file->getRealPath(), 'r');

        try {
            $response = Http::timeout((int) config('services.autotagger.timeout'))
                ->attach('file', $stream, 'image.'.$file->extension())
                ->post(rtrim((string) config('services.autotagger.url'), '/').'/evaluate', [
                    'format' => 'json',
                    'threshold' => '0.1',
                    'limit' => '50',
                ]);
        } catch (ConnectionException) {
            return response()->json([
                'message' => 'The autotagger is not reachable. Type the tags by hand or try again later.',
            ], 503);
        } finally {
            if (is_resource($stream)) {
                fclose($stream);
            }
        }

        $predicted = $response->json('0.tags');

        if ($response->failed() || ! is_array($predicted)) {
            return response()->json([
                'message' => 'The autotagger returned an unexpected response.',
            ], 502);
        }

        $tags = [];
        $rating = null;
        $ratingScore = 0.0;

        foreach ($predicted as $name => $score) {
            $name = (string) $name;
            $score = (float) $score;

            // A rating is not a tag: the most likely one preselects the rating buttons.
            if (str_starts_with($name, 'rating:')) {
                $mapped = self::AUTOTAGGER_RATINGS[substr($name, 7)] ?? null;

                if ($mapped !== null && $score > $ratingScore) {
                    $rating = $mapped;
                    $ratingScore = $score;
                }

                continue;
            }

            $tagName = Tag::normalizeName($name);

            if ($tagName !== '') {
                $tags[] = ['name' => $tagName, 'score' => round($score, 4)];
            }
        }

        return response()->json(['tags' => $tags, 'rating' => $rating]);
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $data = $request->validate([
            'file' => ['required', 'file', 'mimes:jpg,jpeg,png,gif,webp,mp4', 'max:102400'],
            'rating' => ['required', 'in:general,sensitive,questionable,explicit'],
            'tags' => ['required', 'string'],
            'tag_categories' => ['nullable', 'json'],
            'source' => ['nullable', 'string', 'max:255'],
            'original_title' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'translated_title' => ['nullable', 'string', 'max:255'],
            'translated_description' => ['nullable', 'string', 'max:5000'],
            'parent_id' => ['nullable', 'integer', 'min:1', 'exists:posts,id'],
        ]);

        $duplicate = Post::withoutGlobalScopes()
            ->where('md5', md5_file($request->file('file')->getRealPath()))
            ->first();

        if ($duplicate) {
            throw ValidationException::withMessages([
                'file' => 'This file has already been uploaded as post #'.$duplicate->id.'.',
            ]);
        }

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
            'original_title' => $data['original_title'] ?? null,
            'translated_title' => $data['translated_title'] ?? null,
            'description' => $data['description'] ?? null,
            'translated_description' => $data['translated_description'] ?? null,
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

        $status = $post->is_approved ? 'Post uploaded!' : 'Post uploaded and is pending admin approval.';

        // The upload form sends the post with fetch(), so it can show errors without losing the file.
        if ($request->expectsJson()) {
            session()->flash('status', $status);

            return response()->json(['redirect' => route('posts.show', $post)]);
        }

        return redirect()->route('posts.show', $post)->with('status', $status);
    }
}