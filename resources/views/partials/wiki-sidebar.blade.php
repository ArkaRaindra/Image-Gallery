<aside class="space-y-4 md:w-56 md:shrink-0 order-last md:order-none">
    <div>
        <h3 class="font-semibold text-gray-900 mb-2">
            Recent Changes (<a href="{{ route('wiki.versions') }}" class="text-sky-700 hover:underline">all</a>)
        </h3>
        <ul class="space-y-0.5 text-sm">
            @forelse ($recentWikiPages as $recentTag)
                <li>
                    <a href="{{ route('wiki.show', $recentTag->name) }}"
                        class="{{ $recentTag->categoryTextClass() }} hover:underline break-words">{{ $recentTag->displayName() }}</a>
                </li>
            @empty
                <li class="text-gray-600">No wiki pages yet.</li>
            @endforelse
        </ul>
    </div>

    @if (!empty($wikiTag))
        <div>
            <h3 class="font-semibold text-gray-900 mb-2">Options</h3>
            <ul class="space-y-0.5 text-sm">
                <li>
                    <a href="{{ route('wiki.versions', ['tag_id' => $wikiTag->id]) }}"
                        class="text-sky-700 hover:underline">Wiki History</a>
                </li>
            </ul>
        </div>
    @endif
</aside>