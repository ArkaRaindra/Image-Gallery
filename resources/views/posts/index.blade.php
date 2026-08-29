@extends('layouts.app')

@section('title', 'Home')

@section('content')
    <div class="grid grid-cols-1 md:grid-cols-[200px_1fr] gap-6">
        <aside class="space-y-4">
            <div>
                <h3 class="text-xs font-semibold uppercase text-gray-500 mb-2">Rating</h3>
                <ul class="space-y-1 text-sm">
                    @foreach (['general' => 'Umum', 'sensitive' => 'Sensitif', 'questionable' => 'Questionable', 'explicit' => 'Explicit'] as $key => $label)
                        <li><a href="{{ route('posts.index', ['tags' => trim($tagQuery . ' rating:' . $key)]) }}" class="hover:text-sky-400">{{ $label }}</a></li>
                    @endforeach
                </ul>
            </div>

            <div>
                <h3 class="text-xs font-semibold uppercase text-gray-500 mb-2">Tags</h3>
                <ul class="space-y-1 text-sm">
                    @foreach ($popularTags as $tag)
                        @php
                            $tagColor = match($tag->category) {
                                'artist' => 'text-red-400',
                                'character' => 'text-green-400',
                                'copyright' => 'text-purple-400',
                                'meta' => 'text-amber-400',
                                default => 'text-sky-400',
                            };
                        @endphp
                        <li class="flex justify-between gap-2">
                            <a href="{{ route('posts.index', ['tags' => $tag->name]) }}" class="truncate {{ $tagColor }} hover:underline">{{ $tag->name }}</a>
                            <span class="text-gray-600">{{ $tag->post_count }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </aside>

        <main>
            <div class="flex items-center justify-between mb-3">
                <span class="text-sm text-gray-500">{{ $posts->total() }} post</span>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-3">
                @forelse ($posts as $post)
                    @php
                        $borderColor = match($post->rating) {
                            'sensitive' => 'border-sky-700',
                            'questionable' => 'border-amber-700',
                            'explicit' => 'border-red-700',
                            default => 'border-transparent',
                        };
                    @endphp
                    <a href="{{ route('posts.show', $post) }}" class="group block rounded overflow-hidden border-2 {{ $borderColor }} bg-gray-900 hover:border-sky-500 transition">
                        <div class="aspect-square overflow-hidden bg-gray-800">
                            <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($post->thumbnail_path) }}" alt="post {{ $post->id }}" loading="lazy" class="w-full h-full object-cover group-hover:opacity-80 transition">
                        </div>
                        <div class="flex items-center justify-between px-2 py-1 text-xs text-gray-500">
                            <span>#{{ $post->id }}</span>
                            <span>★ {{ $post->score }}</span>
                        </div>
                    </a>
                @empty
                    <p class="col-span-full text-center text-gray-500 py-12">Belum ada post yang cocok dengan pencarian.</p>
                @endforelse
            </div>

            <div class="mt-6">
                {{ $posts->links() }}
            </div>
        </main>
    </div>
@endsection