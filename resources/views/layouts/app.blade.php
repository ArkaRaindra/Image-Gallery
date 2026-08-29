<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Booru Lokal')</title>
    @vite('resources/css/app.css')
</head>
<body class="bg-gray-950 text-gray-200 min-h-screen">
    <nav class="bg-gray-900 border-b border-gray-800 sticky top-0 z-10">
        <div class="max-w-7xl mx-auto px-4 py-3 flex items-center gap-4">
            <a href="{{ route('posts.index') }}" class="font-bold text-lg text-sky-400 shrink-0">Booru Lokal</a>
            <form method="GET" action="{{ route('posts.index') }}" class="flex-1">
                <input type="text" name="tags" value="{{ $tagQuery ?? '' }}" placeholder="cari tag... contoh: 1girl -weapon rating:general" class="w-full px-3 py-1.5 rounded bg-gray-800 border border-gray-700 text-sm focus:outline-none focus:border-sky-500">
            </form>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 py-6">
        @yield('content')
    </div>
</body>
</html>