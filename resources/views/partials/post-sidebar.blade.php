<aside class="space-y-4 md:w-52 md:shrink-0 md:sticky md:top-4 order-last md:order-none">
    <div id="sidebar-search" class="scroll-mt-4">
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
                        class="hover:text-gray-950">{{ $label }}</a></li>
            @endforeach
        </ul>
    </div>
</aside>