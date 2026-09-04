<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" href="{{ asset('images/horse.png') }}" type="image/png">
    <title>@yield('title', 'Image Gallery')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gallery-green text-gray-900 min-h-screen">
    @php
        $isAuthSection = request()->routeIs('login') || request()->routeIs('register');
    @endphp
    <header class="bg-gallery-green border-b border-green-800">
        <div class="w-full px-6 py-2 flex flex-wrap items-center gap-x-6 gap-y-1 text-sm font-medium">
            <a href="{{ route('posts.index') }}">
                <img src="{{ asset('images/VSC.png') }}" alt="Logo" width="80" height="80">
            </a>

            @auth
                <a href="{{ route('account.show') }}" class="text-green-900 {{ request()->routeIs('account.*') ? 'font-bold text-base' : '' }}">My Account</a>
            @else
                <a href="{{ route('login') }}" class="text-red-400">Login</a>
            @endauth

            <a href="{{ route('posts.index') }}" class="text-green-900 {{ request()->routeIs('posts.index') || request()->routeIs('posts.show') ? 'font-bold text-base' : '' }}">Posts</a>
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
            <div class="w-full px-6 py-1.5 flex flex-wrap items-center gap-x-5 gap-y-1 text-xs text-white-400">
                @if ($isAuthSection)
                    <a href="{{ route('register') }}" class="hover:text-gray-200 cursor-pointer">Sign up</a>
                    <a href="{{ route('login') }}" class="hover:text-gray-200 cursor-pointer">Login</a>
                    <span class="cursor-not-allowed">Forgot password</span>
                @else
                    <a href="{{ route('posts.index') }}" class="hover:text-gray-200 cursor-pointer {{ request()->routeIs('posts.index') || request()->routeIs('posts.show') ? 'font-bold text-gray-200' : '' }}">Listing</a>
                    <a href="/admin/posts/create" class="hover:text-gray-200 cursor-pointer">Upload</a>
                    <span class="cursor-not-allowed">Hot</span>
                    @auth
                        <span class="cursor-not-allowed">Favorites</span>
                        <span class="cursor-not-allowed">Fav groups</span>
                        <span class="cursor-not-allowed">Saved searches</span>
                    @endauth
                    <span class="cursor-not-allowed">Changes</span>
                    <span class="cursor-not-allowed">Help</span>
                @endif
            </div>
        </div>
    </header>

    <div class="w-full px-6 py-6 overflow-x-hidden">
        @yield('content')
    </div>
</body>
</html>