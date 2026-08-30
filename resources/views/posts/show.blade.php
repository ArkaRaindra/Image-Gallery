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
                            placeholder="e.g. 1girl -weapon rating:general" autocomplete="off" data-tag-autocomplete
                            class="flex-1 min-w-0 px-2 py-1.5 rounded bg-gray-800 border border-gray-700 text-sm focus:outline-none focus:border-sky-500">
                        <button type="submit" class="px-3 rounded bg-sky-600 hover:bg-sky-500 text-sm">Search</button>
                    </form>
                </div>
            </div>

            @foreach (['artist' => 'Artist', 'copyright' => 'Copyright', 'character' => 'Character', 'general' => 'General', 'meta' => 'Meta'] as $category => $label)
                @php
                    $tagsInCategory = $post->tags->where('category', $category)->sortByDesc('post_count');
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
                                <li>
                                    <a href="{{ route('posts.index', ['tags' => $tag->name, 'wiki' => 1]) }}"
                                        class="text-gray-600 hover:text-gray-400 mr-1">?</a>
                                    <a href="{{ route('posts.index', ['tags' => $tag->name]) }}"
                                        class="{{ $catColor }} hover:underline">{{ $tag->name }}</a>
                                </li>
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
                    <li>Size: {{ $post->humanFileSize() }} .{{ $post->file_ext }}
                        ({{ $post->width }}×{{ $post->height }})</li>
                    @if ($post->source)
                        <li><a href="{{ $post->source }}" target="_blank" class="text-sky-400 hover:underline">Source</a>
                        </li>
                    @endif
                    <li>Rating: <span class="{{ $ratingColor }}">{{ ucfirst($post->rating) }}</span></li>
                    <li class="flex items-center gap-2">
                        Score:
                        @php $votedDirection = $votedPosts[$post->id] ?? null; @endphp
                        <span class="flex items-center gap-1" data-vote-widget data-post-id="{{ $post->id }}"
                            data-voted="{{ $votedDirection }}">
                            <button type="button" data-vote="up"
                                class="{{ $votedDirection === 'up' ? 'text-green-400' : 'hover:text-green-400' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"
                                    class="w-3.5 h-3.5">
                                    <path d="M12 20V4M5 11l7-7 7 7" />
                                </svg>
                            </button>
                            <span data-score>{{ $post->score }}</span>
                            <button type="button" data-vote="down"
                                class="{{ $votedDirection === 'down' ? 'text-red-400' : 'hover:text-red-400' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"
                                    class="w-3.5 h-3.5">
                                    <path d="M12 4v16M5 13l7 7 7-7" />
                                </svg>
                            </button>
                        </span>
                    </li>
                    <li>Status: {{ $post->is_approved ? 'Approved' : 'Pending' }}</li>
                </ul>
            </div>
            <div>
                <h3 class="text-xs font-semibold uppercase text-gray-500 mb-2">Options</h3>
                <ul class="space-y-1">
                    <li>
                        <button type="button" id="resize-window" class="text-sky-400 hover:underline text-left">
                            Resize to window
                        </button>
                    </li>
                    <li>
                        <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($post->file_path) }}"
                            target="_blank" class="text-sky-400 hover:underline">View original</a>
                    </li>
                    <li>
                        <a href="{{ route('posts.download', $post) }}" class="text-sky-400 hover:underline">Download</a>
                    </li>
                </ul>
            </div>
        </aside>
        </aside>

        <main>
            <div class="rounded p-2 mb-3">
                <img id="post-image" src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($post->file_path) }}"
                    alt="post {{ $post->id }}" class="w-full max-h-[80vh] object-contain rounded mx-auto">
            </div>

            <div class="flex items-center justify-between text-sm mb-4 px-2 py-3 bg-gray-900">
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

    <div id="lightbox-backdrop" class="hidden fixed inset-0 bg-black/90 z-40"></div>

    <script>
        (function() {
            const img = document.getElementById('post-image');
            const backdrop = document.getElementById('lightbox-backdrop');
            const btn = document.getElementById('resize-window');
            const defaultClass = 'w-full max-h-[80vh] object-contain rounded mx-auto';
            let active = false;

            function activate() {
                active = true;
                backdrop.classList.remove('hidden');
                img.removeAttribute('class');
                img.style.position = 'fixed';
                img.style.top = '50%';
                img.style.left = '50%';
                img.style.transform = 'translate(-50%, -50%)';
                img.style.maxWidth = '96vw';
                img.style.maxHeight = '96vh';
                img.style.width = 'auto';
                img.style.height = 'auto';
                img.style.zIndex = '50';
                img.style.margin = '0';
                img.style.borderRadius = '0.25rem';
            }

            function deactivate() {
                active = false;
                backdrop.classList.add('hidden');
                img.removeAttribute('style');
                img.className = defaultClass;
            }

            btn?.addEventListener('click', (e) => {
                e.preventDefault();
                active ? deactivate() : activate();
            });

            backdrop?.addEventListener('click', deactivate);

            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && active) deactivate();
            });
        })();
    </script>
@endsection
