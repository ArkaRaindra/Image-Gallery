<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Image Gallery')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gallery-green text-gray-900 min-h-screen">
    <header class="bg-gallery-green border-b border-green-800 sticky top-0 z-20">
        <div class="max-w-7xl mx-auto px-4 py-2 flex items-center gap-6 text-sm font-medium">
            <a href="{{ route('posts.index') }}">
            <img src="{{ asset('images/VSC.png') }}" alt="Logo" width="80" height="80">
            </a>
            <span class="text-red-400 cursor-not-allowed">Login</span>
            <a href="{{ route('posts.index') }}" class="text-green-900">Posts</a>
            <span class="text-gray-600 cursor-not-allowed">Comments</span>
            <span class="text-gray-600 cursor-not-allowed">Notes</span>
            <span class="text-gray-600 cursor-not-allowed">Artists</span>
            <span class="text-gray-600 cursor-not-allowed">Tags</span>
            <span class="text-gray-600 cursor-not-allowed">Pools</span>
            <span class="text-gray-600 cursor-not-allowed">Wiki</span>
            <span class="text-gray-600 cursor-not-allowed">Forum</span>
            <span class="text-gray-600 cursor-not-allowed">More »</span>
        </div>
        <div class="bg-green-700/70 border-t border-green-300">
            <div class="max-w-7xl mx-auto px-4 py-1.5 flex items-center gap-5 text-xs text-white-400">
                <a href="{{ route('posts.index') }}" class="hover:text-gray-900">Listing</a>
                <a href="/admin/posts/create" class="hover:text-gray-900">Upload</a>
                <span class="cursor-not-allowed">Hot</span>
                <span class="cursor-not-allowed">Changes</span>
                <span class="cursor-not-allowed">Help</span>
            </div>
        </div>
    </header>

    <div class="max-w-7xl mx-auto px-4 py-6 overflow-x-hidden">
        @yield('content')
    </div>
</body>
</html>