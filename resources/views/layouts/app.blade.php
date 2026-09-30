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
        $isAccountSection = request()->routeIs('account.*');
        $isCommentsSection = request()->routeIs('comments.index') || request()->routeIs('comments.search');
        $isNotesSection = request()->routeIs('notes.index') || request()->routeIs('notes.changes');
    @endphp
    <header class="bg-gallery-green ">
        {{-- Baris logo (paling atas, mobile & desktop) --}}
        <div class="w-full px-4 md:px-6 py-2 flex items-center justify-between">
            <a href="{{ route('home') }}" class="inline-block">
                <img src="{{ asset('images/VSC.png') }}" alt="Logo" class="w-14 h-14 md:w-20 md:h-20 object-contain">
            </a>

            <button type="button" id="mobile-menu-btn" aria-label="Toggle menu" aria-expanded="false"
                class="md:hidden p-2 text-green-900 cursor-pointer">
                <svg id="menu-icon-open" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2.5" stroke-linecap="round" class="w-8 h-8">
                    <path d="M4 6h16M4 12h16M4 18h16" />
                </svg>
                <svg id="menu-icon-close" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2.5" stroke-linecap="round" class="w-8 h-8 hidden">
                    <path d="M6 6l12 12M18 6L6 18" />
                </svg>
            </button>
        </div>

        {{-- Desktop: navigasi utama + sub navigasi --}}
        <div
            class="hidden md:flex w-full px-6 py-2 flex-wrap items-center gap-x-6 gap-y-1 text-sm font-medium">
            @include('partials.nav-main')
        </div>
        <div class="hidden md:block bg-green-700/70 border-t border-green-300">
            <div class="w-full px-6 py-1.5 flex flex-wrap items-center gap-x-5 gap-y-1 text-xs text-white-400">
                @include('partials.nav-sub')
            </div>
        </div>

        {{-- Mobile: menu hamburger --}}
        <div id="mobile-menu" class="hidden md:hidden border-t border-green-800">
            <div class="px-4 py-3 flex flex-wrap items-center gap-x-5 gap-y-2 text-sm font-medium">
                @include('partials.nav-main')
            </div>
            <div class="bg-green-700/70 border-t border-green-300 px-4 py-3 flex flex-wrap items-center gap-x-5 gap-y-2 text-xs">
                @include('partials.nav-sub')
            </div>
        </div>
    </header>

    <div class="w-full px-3 md:px-6 py-4 md:py-6 overflow-x-hidden">
        @if (session('status'))
            <div class="mb-4 px-4 py-2 rounded bg-sky-800 text-white text-sm">{{ session('status') }}</div>
        @endif
        @yield('content')
    </div>

    <script>
        (function() {
            const btn = document.getElementById('mobile-menu-btn');
            const menu = document.getElementById('mobile-menu');
            const iconOpen = document.getElementById('menu-icon-open');
            const iconClose = document.getElementById('menu-icon-close');
            if (!btn || !menu) return;

            btn.addEventListener('click', () => {
                const open = menu.classList.toggle('hidden') === false;
                iconOpen.classList.toggle('hidden', open);
                iconClose.classList.toggle('hidden', !open);
                btn.setAttribute('aria-expanded', open ? 'true' : 'false');
            });
        })();
    </script>
</body>

</html>