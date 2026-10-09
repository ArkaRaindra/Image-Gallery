@php
    // Green border = has children, orange border = has a parent.
    // When it is both, ONE border is split in two colors:
    // top + left green (children), bottom + right orange (parent).
    $hasChildren = ($item->children_count ?? 0) > 0;
    $hasParent = (bool) $item->parent_id;
    $borderClass = match (true) {
        $hasChildren && $hasParent => 'border-t-green-600 border-l-green-600 border-b-orange-500 border-r-orange-500',
        $hasChildren => 'border-green-600',
        $hasParent => 'border-orange-500',
        default => 'border-transparent',
    };
    $thumbUrl = \Illuminate\Support\Facades\Storage::disk('public')->url($item->thumbnail_path);
    $aspectStyle = $item->width > 0 && $item->height > 0 ? 'aspect-ratio: ' . $item->width . ' / ' . $item->height . ';' : '';
@endphp
<div class="p-1.5 rounded {{ $item->is($current) ? 'bg-gray-900/25' : '' }}">
    <a href="{{ route('posts.show', $item) }}" title="Post #{{ $item->id }}" class="block">
        @if ($item->thumbnailIsVideo())
            <video src="{{ $thumbUrl }}" muted preload="metadata" style="{{ $aspectStyle }}"
                class="block h-36 w-auto max-w-none object-contain border-2 {{ $borderClass }}"></video>
        @else
            <img src="{{ $thumbUrl }}" alt="post {{ $item->id }}" loading="lazy" style="{{ $aspectStyle }}"
                class="block h-36 w-auto max-w-none object-contain border-2 {{ $borderClass }}">
        @endif
    </a>
</div>