<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Booru Lokal</title>
    @vite('resources/css/app.css')
</head>
<body class="bg-gray-900 text-gray-100">
    <div class="max-w-6xl mx-auto p-4">
        <form method="GET" action="{{ route('posts.index') }}" class="mb-6">
            <input
                type="text"
                name="tags"
                value="{{ $tagQuery }}"
                placeholder="contoh: 1girl -weapon rating:general"
                class="w-full px-4 py-2 rounded bg-gray-800 border border-gray-700"
            >
        </form>

        <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-6 gap-3">
            @foreach ($posts as $post)
                <a href="{{ route('posts.show', $post) }}" class="block group">
                    <img
                        src="{{ Storage::disk('public')->url($post->thumbnail_path) }}"
                        alt="post {{ $post->id }}"
                        class="w-full h-40 object-cover rounded group-hover:opacity-80"
                    >
                    <div class="text-xs mt-1 text-gray-400">
                        {{ $post->rating }} · score {{ $post->score }}
                    </div>
                </a>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $posts->links() }}
        </div>
    </div>
</body>
</html>