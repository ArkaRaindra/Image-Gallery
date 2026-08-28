<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Post #{{ $post->id }}</title>
    @vite('resources/css/app.css')
</head>
<body class="bg-gray-900 text-gray-100">
    <div class="max-w-5xl mx-auto p-4 grid md:grid-cols-4 gap-6">
        <div class="md:col-span-3">
            <img
                src="{{ Storage::disk('public')->url($post->file_path) }}"
                alt="post {{ $post->id }}"
                class="w-full rounded"
            >
        </div>

        <div>
            <h2 class="font-bold mb-2">Info</h2>
            <ul class="text-sm text-gray-400 mb-4 space-y-1">
                <li>Rating: {{ $post->rating }}</li>
                <li>Score: {{ $post->score }}</li>
                <li>Ukuran: {{ $post->width }}x{{ $post->height }}</li>
                @if ($post->source)
                    <li>Sumber: <a href="{{ $post->source }}" class="underline" target="_blank">link</a></li>
                @endif
            </ul>

            <h2 class="font-bold mb-2">Tags</h2>
            <div class="flex flex-wrap gap-1">
                @foreach ($post->tags as $tag)
                    
                        href="{{ route('posts.index', ['tags' => $tag->name]) }}"
                        class="text-xs px-2 py-1 bg-gray-800 rounded hover:bg-gray-700"
                    >
                        {{ $tag->name }}
                    </a>
                @endforeach
            </div>
        </div>
    </div>
</body>
</html>