@extends('layouts.app')

@section('title', 'Post #' . $post->id)

@section('content')
    <div class="grid grid-cols-1 md:grid-cols-[200px_1fr] gap-6">
        <aside class="space-y-4 text-sm">
            <div>
                <h3 class="text-xs font-semibold uppercase text-gray-500 mb-2">Info</h3>
                @php
                    $ratingColor = match($post->rating) {
                        'sensitive' => 'text-sky-400',
                        'questionable' => 'text-amber-400',
                        'explicit' => 'text-red-400',
                        default => 'text-green-400',
                    };
                @endphp
                <ul class="space-y-1 text-gray-400">
                    <li>Rating: <span class="{{ $ratingColor }}">{{ $post->rating }}</span></li>
                    <li>Skor: {{ $post->score }}</li>
                    <li>Ukuran: {{ $post->width }}×{{ $post->height }}</li>
                    <li>Diunggah: {{ $post->created_at->diffForHumans() }}</li>
                    @if ($post->source)
                        <li><a href="{{ $post->source }}" target="_blank" class="text-sky-400 hover:underline">Sumber</a></li>
                    @endif
                </ul>
            </div>

            @foreach (['artist' => 'Artist', 'copyright' => 'Copyright', 'character' => 'Character', 'general' => 'General', 'meta' => 'Meta'] as $category => $label)
                @php
                    $tagsInCategory = $post->tags->where('category', $category);
                    $catColor = match($category) {
                        'artist' => 'text-red-400',
                        'character' => 'text-green-400',
                        'copyright' => 'text-purple-400',
                        'meta' => 'text-amber-400',
                        default => 'text-sky-400',
                    };
                @endphp
                @if ($tagsInCategory->isNotEmpty())
                    <div>
                        <h3 class="text-xs font-semibold uppercase text-gray-500 mb-2">{{ $label }}</h3>
                        <ul class="space-y-1">
                            @foreach ($tagsInCategory as $tag)
                                <li><a href="{{ route('posts.index', ['tags' => $tag->name]) }}" class="{{ $catColor }} hover:underline">{{ $tag->name }}</a></li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            @endforeach
        </aside>

        <main>
            <div class="bg-gray-900 rounded p-2 mb-4">
                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($post->file_path) }}" alt="post {{ $post->id }}" class="w-full max-h-[80vh] object-contain rounded">
            </div>

            @if ($post->description)
                <div class="bg-gray-900 rounded p-3 text-sm text-gray-300">
                    {{ $post->description }}
                </div>
            @endif
        </main>
    </div>
@endsection