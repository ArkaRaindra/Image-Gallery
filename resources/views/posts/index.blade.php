@extends('layouts.app')

@section('title', 'Home')

@section('content')
    <div class="flex flex-col md:flex-row md:items-start gap-6">
        @include('partials.post-sidebar', ['tagQuery' => $tagQuery, 'sidebarTags' => $sidebarTags])

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
                    <button id="mobile-search-toggle" type="button"
                        class="md:hidden pb-2 border-b-2 border-transparent text-gray-900 cursor-pointer">
                        Search »
                    </button>
                </div>

                <div class="flex items-center gap-3 text-sm text-gray-500">
                    <span class="hidden md:inline">{{ $posts->total() }} posts</span>
                    <select id="thumb-size"
                        class="bg-white border border-gray-700 rounded text-xs px-2 py-1 focus:outline-none">
                        <option value="small">Small</option>
                        <option value="medium" selected>Medium</option>
                        <option value="large">Large</option>
                        <option value="huge">Huge</option>
                        <option value="gigantic" data-desktop-only>Gigantic</option>
                        <option value="absurd" data-desktop-only>Absurd</option>
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

            <div id="mobile-search" class="hidden md:hidden relative mb-3" data-tag-autocomplete-wrapper>
                ...
            </div>

            <div id="panel-posts">
                <div id="thumb-grid" data-size="medium" class="grid gap-2 md:gap-3 items-start">
                    @forelse ($posts as $post)
                        @php $votedDirection = $votedPosts[$post->id] ?? null; @endphp
                        <div class="relative group">
                            <a href="{{ route('posts.show', $post) }}" class="block rounded overflow-hidden cursor-default">
                                <div class="relative flex items-center justify-center rounded-t overflow-hidden"
                                    data-thumb-container>
                                    <div class="absolute inset-0" data-thumb-fit>
                                        @include('partials.duration-badge', ['post' => $post])
                                        @if ($post->thumbnailIsVideo())
                                            <video data-thumb-media
                                                src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($post->thumbnail_path) }}"
                                                class="block w-full h-full object-contain cursor-pointer" muted loop
                                                playsinline preload="metadata" onmouseover="this.play()"
                                                onmouseout="this.pause(); this.currentTime = 0;"
                                                onloadedmetadata="this.closest('.group').querySelector('[data-post-dims]').textContent = this.videoWidth + '×' + this.videoHeight;"></video>
                                        @else
                                            <img data-thumb-media
                                                src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($post->thumbnail_path) }}"
                                                alt="post {{ $post->id }}" loading="lazy"
                                                class="block w-full h-full object-contain cursor-pointer">
                                        @endif
                                    </div>
                                </div>
                                <div class="flex items-center justify-center gap-1.5 text-xs text-gray-500 py-1"
                                    data-vote-widget data-post-id="{{ $post->id }}" data-voted="{{ $votedDirection }}">
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

                            @include('partials.post-hover-panel', ['post' => $post])
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
                (function() {
            const select = document.getElementById('thumb-size');
            const grid = document.getElementById('thumb-grid');
            if (!select || !grid) return;

            // Simpan daftar opsi asli (6 ukuran). Di mobile hanya 4 yang ditampilkan.
            const allOptions = [...select.options].map((o) => ({
                value: o.value,
                label: o.textContent.trim(),
                desktopOnly: o.hasAttribute('data-desktop-only'),
            }));
            const mobileQuery = window.matchMedia('(max-width: 767px)');

            function buildOptions() {
                const current = select.value;
                select.innerHTML = '';

                allOptions
                    .filter((o) => !(mobileQuery.matches && o.desktopOnly))
                    .forEach((o) => select.add(new Option(o.label, o.value)));

                const stillValid = [...select.options].some((o) => o.value === current);
                select.value = stillValid ? current : (mobileQuery.matches ? 'huge' : 'medium');
                grid.dataset.size = select.value;
                window.recomputeThumbFits?.();
            }

            select.addEventListener('change', (e) => {
                grid.dataset.size = e.target.value;
                window.recomputeThumbFits?.();
            });

            mobileQuery.addEventListener('change', buildOptions);
            buildOptions();
        })();

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
