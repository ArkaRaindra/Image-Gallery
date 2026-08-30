@extends('layouts.app')

@section('title', 'Home')

@section('content')
    <div class="grid grid-cols-1 md:grid-cols-[220px_1fr] gap-6">
        <aside class="space-y-4">
            <div>
                <h3 class="text-xs font-semibold uppercase text-gray-500 mb-2">Search</h3>
                <div class="relative" data-tag-autocomplete-wrapper>
                    <form method="GET" action="{{ route('posts.index') }}" class="flex gap-1">
                        <input type="text" name="tags" value="{{ $tagQuery }}"
                            placeholder="e.g. 1girl -weapon rating:general" autocomplete="off" data-tag-autocomplete
                            class="flex-1 min-w-0 px-2 py-1.5 rounded bg-gray-800 border border-gray-700 text-sm focus:outline-none focus:border-sky-500">
                        <button type="submit" class="px-3 rounded bg-sky-600 hover:bg-sky-500 text-sm">
                            Search
                        </button>
                    </form>
                </div>
            </div>

            <div>
                <h3 class="text-xs font-semibold uppercase text-gray-500 mb-2">Tags</h3>
                <ul class="space-y-1 text-sm">
                    @if ($sidebarTags->isEmpty())
                        <li class="text-gray-600">No tags found.</li>
                    @endif
                    @php
                        $categoryOrder = ['artist', 'copyright', 'character', 'general', 'meta'];
                        $groupedSidebarTags = $sidebarTags->groupBy('category');
                    @endphp
                    @foreach ($categoryOrder as $cat)
                        @foreach (($groupedSidebarTags[$cat] ?? collect())->sortByDesc('post_count') as $tag)
                            @php
                                $tagColor = match ($tag->category) {
                                    'artist' => 'text-red-400',
                                    'character' => 'text-green-400',
                                    'copyright' => 'text-purple-400',
                                    'meta' => 'text-amber-400',
                                    default => 'text-sky-400',
                                };
                            @endphp
                            <li class="flex justify-between gap-2">
                                <span class="truncate">
                                    <a href="{{ route('posts.index', ['tags' => $tag->name, 'wiki' => 1]) }}"
                                        class="text-gray-600 hover:text-gray-400 mr-1">?</a>
                                    <a href="{{ route('posts.index', ['tags' => $tag->name]) }}"
                                        class="{{ $tagColor }} hover:underline">{{ $tag->name }}</a>
                                </span>
                                <span class="text-gray-600 shrink-0">{{ $tag->post_count }}</span>
                            </li>
                        @endforeach
                    @endforeach
                </ul>
            </div>

            <div>
                <h3 class="text-xs font-semibold uppercase text-gray-500 mb-2">Rating</h3>
                @php
                    $tagQueryWithoutRating = collect(explode(' ', $tagQuery))
                        ->filter(fn($t) => $t !== '' && !str_starts_with($t, 'rating:'))
                        ->implode(' ');
                @endphp
                <ul class="space-y-1 text-sm">
                    @foreach (['general' => 'General', 'sensitive' => 'Sensitive', 'questionable' => 'Questionable', 'explicit' => 'Explicit'] as $key => $label)
                        <li><a href="{{ route('posts.index', ['tags' => trim($tagQueryWithoutRating . ' rating:' . $key)]) }}"
                                class="hover:text-sky-400">{{ $label }}</a></li>
                    @endforeach
                </ul>
            </div>
        </aside>

        <main>
            <div class="flex items-center justify-between mb-3 border-b border-gray-800 pb-2">
                <div class="flex items-center gap-4">
                    <button id="tab-posts" type="button"
                        class="tab-btn pb-2 border-b-2 text-sky-400 border-sky-400">Posts</button>
                    @if ($singleTagName)
                        <button id="tab-wiki" type="button" data-tag="{{ $singleTagName }}"
                            class="tab-btn pb-2 border-b-2 text-gray-500 border-transparent hover:text-gray-300">Wiki</button>
                    @endif
                </div>

                <div class="flex items-center gap-3 text-sm text-gray-500">
                    <span>{{ $posts->total() }} posts</span>
                    <select id="thumb-size"
                        class="bg-gray-800 border border-gray-700 rounded text-xs px-2 py-1 focus:outline-none">
                        <option value="110">Small</option>
                        <option value="220" selected>Medium</option>
                        <option value="300">Large</option>
                        <option value="400">Huge</option>
                        <option value="550">Gigantic</option>
                        <option value="800">Absurd</option>
                    </select>
                </div>
            </div>

            <div id="panel-posts">
                <div id="thumb-grid" class="grid gap-3 items-start"
                    style="--thumb-size: 220px; grid-template-columns: repeat(auto-fill, minmax(var(--thumb-size), 1fr));">
                    @forelse ($posts as $post)
                        @php $votedDirection = $votedPosts[$post->id] ?? null; @endphp
                        <div class="relative group">
                            <a href="{{ route('posts.show', $post) }}" class="block rounded overflow-hidden">
                                <div class="flex items-center justify-center rounded-t overflow-hidden"
                                    style="height: var(--thumb-size);">
                                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($post->thumbnail_path) }}"
                                        alt="post {{ $post->id }}" loading="lazy"
                                        class="max-w-full max-h-full object-contain">
                                </div>
                                <div class="flex items-center justify-center gap-1.5 text-xs text-gray-500 py-1"
                                    data-vote-widget data-post-id="{{ $post->id }}"
                                    data-voted="{{ $votedDirection }}">
                                    <button type="button" data-vote="up"
                                        class="{{ $votedDirection === 'up' ? 'text-green-400' : 'hover:text-green-400' }}">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                                            stroke-linejoin="round" class="w-3.5 h-3.5">
                                            <path d="M12 20V4M5 11l7-7 7 7" />
                                        </svg>
                                    </button>
                                    <span data-score>{{ $post->score }}</span>
                                    <button type="button" data-vote="down"
                                        class="{{ $votedDirection === 'down' ? 'text-red-400' : 'hover:text-red-400' }}">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                                            stroke-linejoin="round" class="w-3.5 h-3.5">
                                            <path d="M12 4v16M5 13l7 7 7-7" />
                                        </svg>
                                    </button>
                                </div>
                            </a>

                            <div
                                class="absolute z-30 hidden group-hover:block bottom-full left-0 mb-1 w-72 bg-gray-900/95 border border-gray-700 rounded shadow-xl p-2 text-xs">
                                <div class="flex items-center justify-between text-gray-300 mb-1">
                                    <span class="font-medium truncate">{{ $post->uploader?->name ?? 'Anonymous' }}</span>
                                    <span
                                        class="text-gray-500 shrink-0 ml-2">{{ $post->created_at->diffForHumans() }}</span>
                                </div>
                                <div class="flex items-center gap-2 text-gray-500 mb-2">
                                    <span>{{ strtoupper(substr($post->rating, 0, 1)) }}</span>
                                    <span>{{ $post->humanFileSize() }}</span>
                                    <span>.{{ $post->file_ext }}, {{ $post->width }}×{{ $post->height }}</span>
                                </div>
                                @php
                                    $hoverCategoryOrder = ['artist', 'copyright', 'character', 'general', 'meta'];
                                    $groupedHoverTags = $post->tags->groupBy('category');
                                @endphp
                                <div class="flex flex-wrap gap-x-2 gap-y-0.5">
                                    @foreach ($hoverCategoryOrder as $cat)
                                        @foreach (($groupedHoverTags[$cat] ?? collect())->sortByDesc('post_count') as $tag)
                                            @php
                                                $hoverTagColor = match ($tag->category) {
                                                    'artist' => 'text-red-400',
                                                    'character' => 'text-green-400',
                                                    'copyright' => 'text-purple-400',
                                                    'meta' => 'text-amber-400',
                                                    default => 'text-sky-400',
                                                };
                                            @endphp
                                            <span class="{{ $hoverTagColor }}">{{ $tag->name }}</span>
                                        @endforeach
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="col-span-full text-center text-gray-500 py-12">No posts match this search.</p>
                    @endforelse
                </div>

                <div class="mt-6">
                    {{ $posts->links() }}
                </div>
            </div>

            @if ($singleTagName)
                <div id="panel-wiki" class="hidden">
                    <p class="text-sm text-gray-500">Loading…</p>
                </div>
            @endif
        </main>
    </div>

    <script>
        document.getElementById('thumb-size')?.addEventListener('change', function(e) {
            document.getElementById('thumb-grid').style.setProperty('--thumb-size', e.target.value + 'px');
        });

        (function() {
            const tabPosts = document.getElementById('tab-posts');
            const tabWiki = document.getElementById('tab-wiki');
            const panelPosts = document.getElementById('panel-posts');
            const panelWiki = document.getElementById('panel-wiki');
            let wikiLoaded = false;

            function escapeHtml(str) {
                const div = document.createElement('div');
                div.textContent = str ?? '';
                return div.innerHTML;
            }

            function activate(activeTab, inactiveTab) {
                activeTab.classList.add('text-sky-400', 'border-sky-400');
                activeTab.classList.remove('text-gray-500', 'border-transparent');
                if (inactiveTab) {
                    inactiveTab.classList.remove('text-sky-400', 'border-sky-400');
                    inactiveTab.classList.add('text-gray-500', 'border-transparent');
                }
            }

            tabPosts?.addEventListener('click', () => {
                activate(tabPosts, tabWiki);
                panelPosts.classList.remove('hidden');
                panelWiki?.classList.add('hidden');
            });

            tabWiki?.addEventListener('click', async () => {
                activate(tabWiki, tabPosts);
                panelPosts.classList.add('hidden');
                panelWiki.classList.remove('hidden');

                if (!wikiLoaded) {
                    try {
                        const res = await fetch('/wiki/' + encodeURIComponent(tabWiki.dataset.tag));
                        const data = await res.json();
                        panelWiki.innerHTML = `
                            <h2 class="text-lg font-semibold mb-1">${escapeHtml(data.name)}</h2>
                            <p class="text-xs text-gray-500 mb-4">${escapeHtml(data.category)} · ${data.post_count} posts</p>
                            <div class="text-sm text-gray-300 whitespace-pre-line">${escapeHtml(data.description) || 'No wiki content yet for this tag.'}</div>
                        `;
                        wikiLoaded = true;
                    } catch (e) {
                        panelWiki.innerHTML =
                            '<p class="text-sm text-red-400">Failed to load wiki content.</p>';
                    }
                }
            });

            const params = new URLSearchParams(window.location.search);
            if (params.get('wiki') === '1') {
                tabWiki?.click();
            }
        })();
    </script>
@endsection
