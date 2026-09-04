@extends('layouts.app')

@section('title', $user->name)

@section('content')
    <div class="flex items-center gap-4 mb-6">
        <div class="w-20 h-20 rounded-full overflow-hidden bg-gray-300 flex items-center justify-center shrink-0">
            @if ($user->avatarUrl())
                <img src="{{ $user->avatarUrl() }}" alt="{{ $user->name }}" class="w-full h-full object-cover">
            @else
                <span class="text-2xl font-bold text-gray-600">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
            @endif
        </div>

        <div>
            <h1 class="text-xl font-bold text-sky-700 mb-2">{{ $user->name }}</h1>
            <form method="POST" action="{{ route('account.avatar') }}" enctype="multipart/form-data"
                class="flex items-center gap-2">
                @csrf
                <input type="file" name="avatar" accept="image/*" required class="text-xs">
                <button type="submit"
                    class="px-3 py-1 rounded bg-green-700 hover:bg-green-800 text-white text-xs cursor-pointer">
                    Update Photo
                </button>
            </form>
            @error('avatar')
                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <h2 class="font-semibold mb-2">Statistics</h2>
    <table class="text-sm mb-8">
        <tbody>
            <tr><td class="pr-6 py-0.5 text-gray-600">User ID</td><td>{{ $user->id }}</td></tr>
            <tr><td class="pr-6 py-0.5 text-gray-600">Join Date</td><td>{{ $user->created_at->format('Y-m-d') }}</td></tr>
            <tr><td class="pr-6 py-0.5 text-gray-600">Email Address</td><td>{{ $user->email }}</td></tr>
            <tr><td class="pr-6 py-0.5 text-gray-600">Level</td><td>Member</td></tr>
            <tr><td class="pr-6 py-0.5 text-gray-600">Posts</td><td>{{ $stats['posts'] }}</td></tr>
            <tr><td class="pr-6 py-0.5 text-gray-600">Deleted Posts</td><td>0</td></tr>
            <tr><td class="pr-6 py-0.5 text-gray-600">Favorites</td><td>{{ $stats['favorites'] }}</td></tr>
            <tr><td class="pr-6 py-0.5 text-gray-600">Favorite Groups</td><td>0</td></tr>
            <tr><td class="pr-6 py-0.5 text-gray-600">Post Changes</td><td>0</td></tr>
            <tr><td class="pr-6 py-0.5 text-gray-600">Wiki Page Changes</td><td>0</td></tr>
            <tr><td class="pr-6 py-0.5 text-gray-600">Artist Changes</td><td>0</td></tr>
            <tr><td class="pr-6 py-0.5 text-gray-600">Pool Changes</td><td>0</td></tr>
            <tr><td class="pr-6 py-0.5 text-gray-600">Forum Posts</td><td>0</td></tr>
            <tr><td class="pr-6 py-0.5 text-gray-600">Comments</td><td>{{ $stats['comments'] }}</td></tr>
            <tr><td class="pr-6 py-0.5 text-gray-600">Appeals</td><td>0</td></tr>
            <tr><td class="pr-6 py-0.5 text-gray-600">Flags</td><td>0</td></tr>
        </tbody>
    </table>

    <div class="mb-8">
        <div class="flex items-center justify-between border-b border-gray-400 pb-1 mb-3">
            <h2 class="font-semibold">Favorites ({{ $stats['favorites'] }})</h2>
            <a href="{{ route('favorites.index') }}" class="text-sm text-sky-700 hover:underline">All Favorites</a>
        </div>
        <div class="flex flex-wrap gap-2">
            @forelse ($recentFavorites as $post)
                <a href="{{ route('posts.show', $post) }}" class="block w-24 h-24 rounded overflow-hidden bg-gray-900">
                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($post->thumbnail_path) }}"
                        alt="post {{ $post->id }}" class="w-full h-full object-cover">
                </a>
            @empty
                <p class="text-sm text-gray-500">No favorites yet.</p>
            @endforelse
        </div>
    </div>

    <div class="mb-8">
        <div class="flex items-center justify-between border-b border-gray-400 pb-1 mb-3">
            <h2 class="font-semibold">Your Posts ({{ $stats['posts'] }})</h2>
            <a href="{{ route('posts.index') }}" class="text-sm text-sky-700 hover:underline">All Posts</a>
        </div>
        <div class="flex flex-wrap gap-2">
            @forelse ($recentPosts as $post)
                <a href="{{ route('posts.show', $post) }}" class="block w-24 h-24 rounded overflow-hidden bg-gray-900">
                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($post->thumbnail_path) }}"
                        alt="post {{ $post->id }}" class="w-full h-full object-cover">
                </a>
            @empty
                <p class="text-sm text-gray-500">You haven't uploaded any posts yet.</p>
            @endforelse
        </div>
    </div>
@endsection