@extends('layouts.app')

@section('title', $profileUser->name)

@section('content')
    <div class="flex items-center gap-4 mb-6">
        <div class="w-20 h-20 rounded-full overflow-hidden bg-gray-300 flex items-center justify-center shrink-0">
            @if ($profileUser->avatarUrl())
                <img src="{{ $profileUser->avatarUrl() }}" alt="{{ $profileUser->name }}"
                    class="w-full h-full object-cover">
            @else
                <span class="text-2xl font-bold text-gray-600">{{ strtoupper(substr($profileUser->name, 0, 1)) }}</span>
            @endif
        </div>
        <div>
            <h1 class="text-xl font-bold text-sky-700">{{ $profileUser->name }}</h1>
            <p class="text-sm text-gray-600">{{ ucfirst($profileUser->role) }}</p>
        </div>
    </div>

    <h2 class="font-semibold mb-2">Statistics</h2>
    <table class="text-sm mb-8">
        <tbody>
            <tr>
                <td class="pr-6 py-0.5 text-gray-600">Join Date</td>
                <td>{{ $profileUser->created_at->format('Y-m-d') }}</td>
            </tr>
            <tr>
                <td class="pr-6 py-0.5 text-gray-600">Posts</td>
                <td>{{ $stats['posts'] }}</td>
            </tr>
            <tr>
                <td class="pr-6 py-0.5 text-gray-600">Comments</td>
                <td>{{ $stats['comments'] }}</td>
            </tr>
            <tr>
                <td class="pr-6 py-0.5 text-gray-600">Favorites</td>
                <td>{{ $stats['favorites'] }}</td>
            </tr>
        </tbody>
    </table>

    <div>
        <h2 class="font-semibold mb-3 border-b border-gray-400 pb-1">Posts</h2>
        <div class="flex flex-wrap gap-2">
            @forelse ($recentPosts as $post)
                <a href="{{ route('posts.show', $post) }}" class="block w-24 h-24 rounded overflow-hidden bg-gray-900">
                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($post->thumbnail_path) }}"
                        alt="post {{ $post->id }}" class="w-full h-full object-cover">
                </a>
            @empty
                <p class="text-sm text-gray-500">No public posts yet.</p>
            @endforelse
        </div>
    </div>
@endsection