@extends('layouts.app')

@section('title', 'Post #' . $post->id)

@section('content')
    <div class="grid grid-cols-1 md:grid-cols-[220px_1fr] gap-6">
        <aside class="space-y-4 text-sm">
            <div>
                <h3 class="text-xs font-semibold uppercase text-gray-500 mb-2">Search</h3>
                <div class="relative" data-tag-autocomplete-wrapper>
                    <form method="GET" action="{{ route('posts.index') }}" class="flex gap-1">
                        <input type="text" name="tags" value="{{ $tagQuery }}"
                            placeholder="e.g. 1girl -weapon rating:general" autocomplete="off"
                            data-tag-autocomplete
                            class="flex-1 min-w-0 px-2 py-1.5 rounded bg-gray-800 border border-gray-700 text-sm focus:outline-none focus:border-sky-500">
                        <button type="submit" class="px-3 rounded bg-sky-600 hover:bg-sky-500 text-sm">🔍</button>
                    </form>
                </div>
            </div>

            @foreach (['artist' => 'Artist', 'copyright' => 'Copyright', 'character' => 'Character', 'general' => 'General', 'meta' => 'Meta'] as $category => $label)
                @php
                    $tagsInCategory = $post->tags->where('category', $category);
                    $catColor = match ($category) {
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
                                <li><a href="{{ route('posts.index', ['tags' => $tag->name]) }}"
                                        class="{{ $catColor }} hover:underline">{{ $tag->name }}</a></li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            @endforeach

            <div>
                <h3 class="text-xs font-semibold uppercase text-gray-500 mb-2">Information</h3>
                @php
                    $ratingColor = match ($post->rating) {
                        'sensitive' => 'text-sky-400',
                        'questionable' => 'text-amber-400',
                        'explicit' => 'text-red-400',
                        default => 'text-green-400',
                    };
                @endphp
                                <ul class="space-y-1 text-gray-400">
                    <li>ID: {{ $post->id }}</li>
                    <li>Uploader: {{ $post->uploader?->name ?? 'Anonymous' }}</li>
                    <li>Date: {{ $post->created_at->diffForHumans() }}</li>
                    <li>Size: {{ $post->humanFileSize() }} .{{ $post->file_ext }} ({{ $post->width }}×{{ $post->height }})</li>
                    @if ($post->source)
                        <li><a href="{{ $post->source }}" target="_blank" class="text-sky-400 hover:underline">Source</a></li>
                    @endif
                    <li>Rating: <span class="{{ $ratingColor }}">{{ ucfirst($post->rating) }}</span></li>
                    <li class="flex items-center gap-2">
                        Score:
                        @php $votedDirection = $votedPosts[$post->id] ?? null; @endphp
                        <span class="flex items-center gap-1" data-vote-widget data-post-id="{{ $post->id }}" data-voted="{{ $votedDirection }}">
                            <button type="button" data-vote="up" {{ $votedDirection ? 'disabled' : '' }}
                                class="{{ $votedDirection === 'up' ? 'text-green-400' : 'hover:text-green-400' }} disabled:opacity-40 disabled:cursor-not-allowed">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-3 h-3">
                                    <path fill-rule="evenodd" d="M9.47 6.47a.75.75 0 0 1 1.06 0l4.5 4.5a.75.75 0 1 1-1.06 1.06L10 7.06l-3.97 3.97a.75.75 0 0 1-1.06-1.06l4.5-4.5Z" clip-rule="evenodd" />
                                </svg>
                            </button>
                            <span data-score>{{ $post->score }}</span>
                            <button type="button" data-vote="down" {{ $votedDirection ? 'disabled' : '' }}
                                class="{{ $votedDirection === 'down' ? 'text-red-400' : 'hover:text-red-400' }} disabled:opacity-40 disabled:cursor-not-allowed">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-3 h-3">
                                    <path fill-rule="evenodd" d="M10.53 13.53a.75.75 0 0 1-1.06 0l-4.5-4.5a.75.75 0 1 1 1.06-1.06L10 11.94l3.97-3.97a.75.75 0 1 1 1.06 1.06l-4.5 4.5Z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </span>
                    </li>
                    <li>Status: {{ $post->is_approved ? 'Approved' : 'Pending' }}</li>
                </ul>
            </div>
        </aside>

        <main>
            <div class="bg-gray-900 rounded p-2 mb-3">
                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($post->file_path) }}"
                    alt="post {{ $post->id }}" class="w-full max-h-[80vh] object-contain rounded">
            </div>

            <div class="flex items-center justify-between text-sm mb-4 px-1">
                @if ($prevId)
                    <a href="{{ route('posts.show', ['post' => $prevId, 'tags' => $tagQuery]) }}"
                        class="text-sky-400 hover:underline">‹ prev</a>
                @else
                    <span class="text-gray-700">‹ prev</span>
                @endif

                @if ($tagQuery)
                    <span class="text-gray-500">Search: {{ $tagQuery }}</span>
                @endif

                @if ($nextId)
                    <a href="{{ route('posts.show', ['post' => $nextId, 'tags' => $tagQuery]) }}"
                        class="text-sky-400 hover:underline">next ›</a>
                @else
                    <span class="text-gray-700">next ›</span>
                @endif
            </div>

            @if ($post->description)
                <div class="bg-gray-900 rounded p-3 text-sm text-gray-300">
                    {{ $post->description }}
                </div>
            @endif
        </main>
    </div>
@endsection