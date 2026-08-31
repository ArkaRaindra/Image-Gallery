@extends('layouts.app')

@section('title', 'Home')

@section('content')
    <div class="flex flex-col md:flex-row md:items-start gap-6">
        <aside class="space-y-4 md:w-52 md:shrink-0 md:sticky md:top-4 order-last md:order-none">
            <div>
                <h3 class="text-xm font-semibold uppercase text-gray-900 mb-2">Search</h3>
                <div class="relative" data-tag-autocomplete-wrapper>
                    <form method="GET" action="{{ route('posts.index') }}" class="flex gap-1">
                        <input type="text" name="tags" value="{{ $tagQuery }}"
                            placeholder="e.g. 1girl -weapon rating:general" autocomplete="off" data-tag-autocomplete
                            class="flex-1 min-w-0 px-2 py-1.5 rounded bg-white border border-gray-700 text-sm focus:outline-none focus:border-sky-500">
                        <button type="submit"
                            class="px-3 rounded bg-green-700 hover:bg-green-800 text-white text-sm cursor-pointer">
                            Search
                        </button>
                    </form>
                </div>
            </div>

            <div>
                <h3 class="text-xm font-semibold uppercase text-gray-900 mb-2">Tags</h3>
                <ul class="space-y-1 text-sm">
                    @if ($sidebarTags->isEmpty())
                        <li class="text-gray-900">No tags found.</li>
                    @endif
                    @php
                        $categoryOrder = ['artist', 'copyright', 'character', 'general', 'meta'];
                        $groupedSidebarTags = $sidebarTags->groupBy('category');
                    @endphp
                    @foreach ($categoryOrder as $cat)
                        @foreach (($groupedSidebarTags[$cat] ?? collect())->sortByDesc('post_count') as $tag)
                            @php
                                $tagColor = match ($tag->category) {
                                    'artist' => 'text-red-700',
                                    'character' => 'text-green-700',
                                    'copyright' => 'text-purple-700',
                                    'meta' => 'text-amber-700',
                                    default => 'text-sky-700',
                                };
                            @endphp
                            <li class="flex justify-between gap-2">
                                <span class="truncate">
                                    <a href="{{ route('posts.index', ['tags' => $tag->name, 'wiki' => 1]) }}"
                                        class="text-gray-600 hover:text-gray-900 mr-1">?</a>
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
                <h3 class="text-xm font-semibold uppercase text-gray-900 mb-2">Rating</h3>
                @php
                    $tagQueryWithoutRating = collect(explode(' ', $tagQuery))
                        ->filter(fn($t) => $t !== '' && !str_starts_with($t, 'rating:'))
                        ->implode(' ');
                @endphp
                <ul class="space-y-1 text-sm">
                    @foreach (['general' => 'General', 'sensitive' => 'Sensitive', 'questionable' => 'Questionable', 'explicit' => 'Explicit'] as $key => $label)
                        <li><a href="{{ route('posts.index', ['tags' => trim($tagQueryWithoutRating . ' rating:' . $key)]) }}"
                                class="hover:text-gray-900">{{ $label }}</a></li>
                    @endforeach
                </ul>
            </div>
        </aside>

        <main class="flex-1 min-w-0">
            <div class="flex items-center justify-between mb-3 border-b border-gray-800 pb-2">
                <div class="flex items-center gap-4">
                    <button id="tab-posts" type="button"
                        class="tab-btn pb-2 border-b-2 text-gray-900 border-gray-900 hover:text-gray-950 cursor-pointer">Posts</button>
                    @if ($singleTagName)
                        <button id="tab-wiki" type="button" data-tag="{{ $singleTagName }}"
                            class="tab-btn pb-2 border-b-2 text-gray-900 border-transparent hover:text-gray-950 cursor-pointer">
                            {{ $singleTagCategory === 'artist' ? 'Artist' : 'Wiki' }}
                        </button>
                    @endif
                </div>

                <div class="flex items-center gap-3 text-sm text-gray-500">
                    <span>{{ $posts->total() }} posts</span>
                    <select id="thumb-size"
                        class="bg-white border border-gray-700 rounded text-xs px-2 py-1 focus:outline-none">
                        <option value="110">Small</option>
                        <option value="200" selected>Medium</option>
                        <option value="300">Large</option>
                        <option value="400">Huge</option>
                        <option value="550">Gigantic</option>
                        <option value="800">Absurd</option>
                    </select>

                    <div class="relative">
                        <button type="button" id="more-menu-btn"
                            class="px-2 py-1 rounded border border-gray-700 bg-white hover:bg-gray-100 cursor-pointer leading-none">
                            •••
                        </button>
                        <div id="more-menu"
                            class="hidden absolute right-0 mt-1 w-40 bg-white border border-gray-300 rounded shadow-lg z-40 text-sm">
                            <button type="button" id="hide-scores-toggle"
                                class="w-full text-left px-3 py-2 hover:bg-gray-100 cursor-pointer">
                                Hide scores
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div id="panel-posts">
                <div id="thumb-grid" class="grid gap-3 items-start"
                    style="--thumb-size: 220px; grid-template-columns: repeat(auto-fill, minmax(min(var(--thumb-size), 100%), 1fr));">
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
                                        class="{{ $votedDirection === 'up' ? 'text-green-800' : 'hover:text-green-700 cursor-pointer' }}">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                                            stroke-linejoin="round" class="w-3.5 h-3.5">
                                            <path d="M12 20V4M5 11l7-7 7 7" />
                                        </svg>
                                    </button>
                                    <span data-score>{{ $post->score }}</span>
                                    <button type="button" data-vote="down"
                                        class="{{ $votedDirection === 'down' ? 'text-red-800' : 'hover:text-red-700 cursor-pointer' }}">
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
                        <p class="col-span-full text-center text-gray-900 py-12">No posts match this search.</p>
                    @endforelse
                </div>

                <div class="mt-6">
                    {{ $posts->links() }}
                </div>
            </div>

            @if ($singleTagName)
                <div id="panel-wiki" class="hidden">
                    <p class="text-sm text-gray-900">Loading…</p>
                </div>
            @endif
        </main>
    </div>

    <script>
        document.getElementById('thumb-size')?.addEventListener('change', function(e) {
            document.getElementById('thumb-grid').style.setProperty('--thumb-size', e.target.value + 'px');
        });

        (function() {
            const moreBtn = document.getElementById('more-menu-btn');
            const moreMenu = document.getElementById('more-menu');
            const hideScoresToggle = document.getElementById('hide-scores-toggle');

            moreBtn?.addEventListener('click', (e) => {
                e.stopPropagation();
                moreMenu.classList.toggle('hidden');
            });

            document.addEventListener('click', () => moreMenu?.classList.add('hidden'));

            function applyHideScores(hide) {
                document.querySelectorAll('[data-vote-widget]').forEach((w) => {
                    w.style.display = hide ? 'none' : '';
                });
                if (hideScoresToggle) {
                    hideScoresToggle.textContent = hide ? 'Show scores' : 'Hide scores';
                }
            }

            if (hideScoresToggle) {
                const saved = localStorage.getItem('hideScores') === '1';
                applyHideScores(saved);

                hideScoresToggle.addEventListener('click', () => {
                    const next = !(localStorage.getItem('hideScores') === '1');
                    localStorage.setItem('hideScores', next ? '1' : '0');
                    applyHideScores(next);
                });
            }
        })();

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
                activeTab.classList.add('text-gray-900', 'border-gray-900', 'font-bold');
                activeTab.classList.remove('text-gray-500', 'border-transparent');
                if (inactiveTab) {
                    inactiveTab.classList.remove('text-gray-900', 'border-gray-400', 'font-bold');
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
                            <div class="wiki-content text-sm text-gray-800">${data.description || 'No wiki content yet for this tag.'}</div>
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

    <style>
        .wiki-content img {
            max-width: 100%;
            border-radius: 0.25rem;
            margin: 0.5rem 0;
        }

        .wiki-content p {
            margin-bottom: 0.5rem;
        }

        .wiki-content a {
            color: #0369a1;
            text-decoration: underline;
        }
    </style>
@endsection
