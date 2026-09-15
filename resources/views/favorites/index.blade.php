@extends('layouts.app')

@section('title', 'My Favorites')

@section('content')
    <h1 class="text-lg font-semibold mb-4">My Favorites ({{ $posts->total() }})</h1>

    <div class="grid gap-3" style="grid-template-columns: repeat(auto-fill, minmax(min(200px, 100%), 1fr));">
        @forelse ($posts as $post)
            <a href="{{ route('posts.show', $post) }}" class="block rounded overflow-hidden">
                <div class="flex items-center justify-center rounded overflow-hidden" style="width: 200px; height: 200px;">
                    @if ($post->thumbnailIsVideo())
                        <video src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($post->thumbnail_path) }}"
                            class="max-w-full max-h-full object-contain" muted loop playsinline preload="metadata"
                            onmouseover="this.play()" onmouseout="this.pause(); this.currentTime = 0;"></video>
                    @else
                        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($post->thumbnail_path) }}"
                            alt="post {{ $post->id }}" loading="lazy" class="max-w-full max-h-full object-contain">
                    @endif
                </div>
            </a>
        @empty
            <p class="col-span-full text-center text-gray-500 py-12">You haven't favorited any posts yet.</p>
        @endforelse
    </div>

    <div class="mt-6">
        {{ $posts->links() }}
    </div>
@endsection