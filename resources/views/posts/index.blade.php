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
                    @forelse ($sidebarTags as $tag)
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
                            <a href="{{ route('posts.index', ['tags' => $tag->name]) }}"
                                class="truncate {{ $tagColor }} hover:underline">{{ $tag->name }}</a>
                            <span class="text-gray-600">{{ $tag->post_count }}</span>
                        </li>
                    @empty
                        <li class="text-gray-600">No tags found.</li>
                    @endforelse
                </ul>
            </div>

            <div>
                <h3 class="text-xs font-semibold uppercase text-gray-500 mb-2">Rating</h3>
                <ul class="space-y-1 text-sm">
                    @foreach (['general' => 'General', 'sensitive' => 'Sensitive', 'questionable' => 'Questionable', 'explicit' => 'Explicit'] as $key => $label)
                        <li><a href="{{ route('posts.index', ['tags' => trim($tagQuery . ' rating:' . $key)]) }}"
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
                        <option value="90">Small</option>
                        <option value="150" selected>Medium</option>
                        <option value="220">Large</option>
                        <option value="320">Huge</option>
                        <option value="450">Gigantic</option>
                        <option value="650">Absurd</option>
                    </select>
                </div>
            </div>

            <div id="panel-posts">
                <div id="thumb-grid" class="grid gap-3"
                    style="--thumb-size: 150px; grid-template-columns: repeat(auto-fill, minmax(var(--thumb-size), 1fr));">
                    @forelse ($posts as $post)
                        @php
                            $borderColor = match ($post->rating) {
                                'sensitive' => 'border-sky-700',
                                'questionable' => 'border-amber-700',
                                'explicit' => 'border-red-700',
                                default => 'border-transparent',
                            };
                        @endphp
                        <a href="{{ route('posts.show', $post) }}"
                            class="group block rounded overflow-hidden border-2 {{ $borderColor }} bg-gray-900 hover:border-sky-500 transition">
                            <div class="aspect-square overflow-hidden bg-gray-800">
                                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($post->thumbnail_path) }}"
                                    alt="post {{ $post->id }}" loading="lazy"
                                    class="w-full h-full object-cover group-hover:opacity-80 transition">
                            </div>
                            <div class="flex items-center justify-between px-2 py-1 text-xs text-gray-500">
                                <span>#{{ $post->id }}</span>
                                <span>★ {{ $post->score }}</span>
                            </div>
                        </a>
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
        })();
    </script>
@endsection
