@php
    $siblingCount = $siblings->count();
    $childCount = $children->count();

    // Parent first, then every sibling (including the current post) by id.
    $siblingBox = $parent
        ? $siblings->concat([$post])->sortBy('id')->values()->prepend($parent)
        : collect();

    // Current post first, then its children by id.
    $childBox = $children->isNotEmpty()
        ? $children->values()->prepend($post)
        : collect();
@endphp

@if ($parent)
    <div class="mb-3 rounded border border-amber-500 bg-amber-100 p-3 text-sm text-gray-900" data-family-box>
        <div>
            This post belongs to a
            <a href="{{ route('posts.show', $parent) }}" class="text-sky-700 hover:underline">parent</a>
            @if ($siblingCount > 0)
                and has {{ $siblingCount }} {{ \Illuminate\Support\Str::plural('sibling', $siblingCount) }}
            @endif
            (<a href="#" data-family-help class="text-sky-700 hover:underline">learn more</a>)
            <a href="#" data-family-toggle class="text-sky-700 hover:underline">« hide</a>
        </div>
        <div data-family-content class="mt-2">
            <div class="flex flex-wrap gap-1">
                @foreach ($siblingBox as $item)
                    @include('partials.post-family-thumb', ['item' => $item, 'current' => $post])
                @endforeach
            </div>
        </div>
    </div>
@endif

@if ($children->isNotEmpty())
    <div class="mb-3 rounded border border-emerald-600 bg-emerald-100 p-3 text-sm text-gray-900" data-family-box>
        <div>
            This post has
            <a href="{{ route('posts.index', ['tags' => 'parent:' . $post->id]) }}"
                class="text-sky-700 hover:underline">{{ $childCount }}
                {{ \Illuminate\Support\Str::plural('child', $childCount) }}</a>
            (<a href="#" data-family-help class="text-sky-700 hover:underline">learn more</a>)
            <a href="#" data-family-toggle class="text-sky-700 hover:underline">« hide</a>
        </div>
        <div data-family-content class="mt-2">
            <div class="flex flex-wrap gap-1">
                @foreach ($childBox as $item)
                    @include('partials.post-family-thumb', ['item' => $item, 'current' => $post])
                @endforeach
            </div>
        </div>
    </div>
@endif

@if ($parent || $children->isNotEmpty())
    <div id="family-help" class="hidden mb-3 rounded border border-gray-500 bg-white p-3 text-xs text-gray-800">
        <p class="mb-1">A parent post groups related posts such as alternate versions of the same picture.
            Posts that share a parent are siblings.</p>
        <p class="mb-1">A green border marks a post that has children, an orange border marks a post that has a parent. A border split in two colors (green top/left, orange bottom/right) marks a post that has both.</p>
        <p>Search with <code>parent:123</code>, <code>parent:none</code>, <code>parent:any</code>,
            <code>child:none</code> or <code>child:any</code>.</p>
    </div>
@endif