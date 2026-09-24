@php
    $post = $comment->post;

    $categoryOrder = ['artist' => 0, 'copyright' => 1, 'character' => 2, 'general' => 3, 'meta' => 4];
    $tags = $post->tags
        ->sortBy('name')
        ->sortBy(fn ($tag) => $categoryOrder[$tag->category] ?? 99)
        ->values();

    $ratingColor = match ($post->rating) {
        'sensitive' => 'text-sky-700',
        'questionable' => 'text-amber-700',
        'explicit' => 'text-red-700',
        default => 'text-green-700',
    };
@endphp

<div class="text-sm mb-3 space-y-1">
    <div class="flex flex-wrap items-center gap-x-4 gap-y-1">
        <span>
            <span class="font-semibold">Date</span>
            <span class="text-gray-700">{{ $post->created_at->format('Y-m-d H:i') }}</span>
        </span>
        <span>
            <span class="font-semibold">Uploader</span>
            @if ($post->uploader)
                <a href="{{ route('users.show', $post->uploader) }}"
                    class="text-sky-700 hover:underline">{{ $post->uploader->name }}</a>
            @else
                <span class="text-gray-700">Admin</span>
            @endif
        </span>
        <span>
            <span class="font-semibold">Rating</span>
            <span class="{{ $ratingColor }}">{{ ucfirst($post->rating) }}</span>
        </span>
    </div>

    @if ($tags->isNotEmpty())
        <div class="flex flex-wrap items-baseline gap-x-2 gap-y-0.5">
            <span class="font-semibold">Tags</span>
            @foreach ($tags as $tag)
                @php
                    $tagColor = match ($tag->category) {
                        'artist' => 'text-red-700',
                        'character' => 'text-green-700',
                        'copyright' => 'text-purple-700',
                        'meta' => 'text-amber-700',
                        default => 'text-sky-700',
                    };
                @endphp
                <a href="{{ route('posts.index', ['tags' => $tag->name]) }}"
                    class="{{ $tagColor }} hover:underline">{{ $tag->name }}</a>
            @endforeach
        </div>
    @endif
</div>