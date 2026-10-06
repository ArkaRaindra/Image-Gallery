@php
    // Green border = has children, orange border = has a parent
    // (green with an orange ring when it is both).
    $hasChildren = ($item->children_count ?? 0) > 0;
    $hasParent = (bool) $item->parent_id;
    $borderClass = match (true) {
        $hasChildren => 'border-green-600',
        $hasParent => 'border-orange-500',
        default => 'border-transparent',
    };
    $ringClass = $hasChildren && $hasParent ? 'ring-2 ring-orange-500' : '';
    $thumbUrl = \Illuminate\Support\Facades\Storage::disk('public')->url($item->thumbnail_path);
    $aspectStyle = $item->width > 0 && $item->height > 0 ? 'aspect-ratio: ' . $item->width . ' / ' . $item->height . ';' : '';
@endphp
<div class="p-1.5 rounded {{ $item->is($current) ? 'bg-gray-900/25' : '' }}">
    <a href="{{ route('posts.show', $item) }}" title="Post #{{ $item->id }}" class="block">
        @if ($item->thumbnailIsVideo())
            <video src="{{ $thumbUrl }}" muted preload="metadata" style="{{ $aspectStyle }}"
                class="block h-36 w-auto max-w-none object-contain border-2 {{ $borderClass }} {{ $ringClass }}"></video>
        @else
            <img src="{{ $thumbUrl }}" alt="post {{ $item->id }}" loading="lazy" style="{{ $aspectStyle }}"
                class="block h-36 w-auto max-w-none object-contain border-2 {{ $borderClass }} {{ $ringClass }}">
        @endif
    </a>
</div>