@php
    $isGif = strtolower($post->file_ext) === 'gif';
@endphp
@if ($post->isVideo() || $isGif)
    <div data-duration-badge data-kind="{{ $post->isVideo() ? 'video' : 'gif' }}"
        data-src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($post->file_path) }}"
        class="hidden absolute top-1 left-1 z-20 items-center gap-1 px-1.5 py-0.5 rounded bg-black/70 text-white text-[10px] font-medium leading-none pointer-events-none">
        <span data-duration-text></span>
        @if ($post->isVideo())
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-2.5 h-2.5 shrink-0">
                <path d="M3 9v6h4l5 5V4L7 9H3z" />
                <path d="M16.5 12c0-1.77-1-3.29-2.5-4.03v8.06c1.5-.74 2.5-2.26 2.5-4.03z" />
            </svg>
        @endif
    </div>
@endif