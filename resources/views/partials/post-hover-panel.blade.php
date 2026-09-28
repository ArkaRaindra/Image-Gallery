<div data-hover-panel style="bottom: 100%;"
    class="absolute z-30 opacity-0 invisible transition-[opacity,visibility] duration-300 left-0 w-72 bg-gray-900/95 border border-gray-700 rounded shadow-xl p-2 text-xs">
    <div class="flex items-center justify-between text-gray-300 mb-1">
        <span class="font-medium truncate">{{ $post->uploader?->name ?? 'Admin' }}</span>
        <span
            class="text-gray-500 shrink-0 ml-2">{{ $post->created_at->diffForHumans() }}</span>
    </div>
    <div class="flex items-center gap-2 text-gray-500 mb-2">
        <span>{{ strtoupper(substr($post->rating, 0, 1)) }}</span>
        <span>{{ $post->humanFileSize() }}</span>
        <span>.{{ $post->file_ext }}, <span
                data-post-dims>{{ $post->width }}×{{ $post->height }}</span></span>
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
                <a href="{{ route('posts.index', ['tags' => $tag->name]) }}"
                    class="{{ $hoverTagColor }} hover:underline">{{ $tag->name }}</a>
            @endforeach
        @endforeach
    </div>
</div>