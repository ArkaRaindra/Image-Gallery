@extends('layouts.app')

@section('title', 'Comments')

@section('content')
    <h1 class="text-2xl font-bold mb-4">Comments</h1>

    <form method="GET" action="{{ route('comments.index') }}" class="mb-6 max-w-md space-y-2 text-sm">
        <div class="flex items-center gap-2">
            <label class="w-24 font-semibold text-right">Commenter</label>
            <input type="text" name="commenter" value="{{ $filters['commenter'] ?? '' }}"
                class="flex-1 px-2 py-1 rounded bg-white border border-gray-700">
        </div>
        <div class="flex items-center gap-2">
            <label class="w-24 font-semibold text-right">Text</label>
            <input type="text" name="text" value="{{ $filters['text'] ?? '' }}"
                class="flex-1 px-2 py-1 rounded bg-white border border-gray-700">
        </div>
        <div class="flex items-center gap-2">
            <label class="w-24 font-semibold text-right">Tags</label>
            <input type="text" name="tags" value="{{ $filters['tags'] ?? '' }}"
                class="flex-1 px-2 py-1 rounded bg-white border border-gray-700">
        </div>
        <div class="flex items-center gap-2">
            <label class="w-24 font-semibold text-right text-gray-400">Score</label>
            <input type="text" disabled placeholder="Not available yet"
                class="flex-1 px-2 py-1 rounded bg-gray-100 border border-gray-300 text-gray-400 cursor-not-allowed">
        </div>
        <div class="flex items-center gap-2">
            <label class="w-24 font-semibold text-right">Order</label>
            <select name="order" class="flex-1 px-2 py-1 rounded bg-white border border-gray-700">
                <option value="newest" @selected(($filters['order'] ?? '') !== 'oldest')>Newest</option>
                <option value="oldest" @selected(($filters['order'] ?? '') === 'oldest')>Oldest</option>
            </select>
        </div>
        <div class="flex items-center gap-2">
            <span class="w-24"></span>
            <button type="submit"
                class="px-4 py-1.5 rounded bg-green-700 hover:bg-green-800 text-white cursor-pointer">Search</button>
        </div>
    </form>

    <div class="space-y-6">
        @forelse ($comments as $comment)
            <div class="flex gap-4">
                @if ($comment->post)
                    <a href="{{ route('posts.show', $comment->post) }}"
                        class="block w-24 h-24 rounded overflow-hidden bg-gray-900 shrink-0">
                        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($comment->post->thumbnail_path) }}"
                            alt="post {{ $comment->post->id }}" class="w-full h-full object-cover">
                    </a>
                @endif
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="font-semibold text-sky-700">{{ $comment->author_name }}</span>
                        <span class="text-xs text-gray-500">{{ $comment->created_at->diffForHumans() }}</span>
                    </div>
                    <div class="text-sm text-gray-800 mb-1">
                        {!! \App\Support\SimpleMarkdown::toHtml(\Illuminate\Support\Str::limit($comment->body, 300)) !!}
                    </div>
                    <div class="flex items-center gap-3 text-xs">
                        @if ($comment->post)
                            <a href="{{ route('posts.show', $comment->post) }}#comments"
                                class="text-sky-700 hover:underline">Reply</a>
                        @endif
                        @if (auth()->user()?->isAdmin())
                            <form method="POST" action="{{ route('comments.destroy', $comment) }}"
                                onsubmit="return confirm('Delete this comment?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:underline cursor-pointer">Delete</button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <p class="text-gray-500">No comments found.</p>
        @endforelse
    </div>

    <div class="mt-6">
        {{ $comments->links() }}
    </div>
@endsection