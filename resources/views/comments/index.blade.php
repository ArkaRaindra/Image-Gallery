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
            <div class="flex-1 relative" data-tag-autocomplete-wrapper>
                <input type="text" name="tags" value="{{ $filters['tags'] ?? '' }}" autocomplete="off"
                    data-tag-autocomplete class="w-full px-2 py-1 rounded bg-white border border-gray-700">
            </div>
        </div>
        <div class="flex items-center gap-2">
            <label class="w-24 font-semibold text-right">Score</label>
            <input type="text" name="score" value="{{ $filters['score'] ?? '' }}" placeholder="e.g. 5"
                class="flex-1 px-2 py-1 rounded bg-white border border-gray-700">
        </div>
        <div class="flex items-center gap-2">
            <label class="w-24 font-semibold text-right">Order</label>
            <select name="order" class="flex-1 px-2 py-1 rounded bg-white border border-gray-700">
                <option value="newest" @selected(($filters['order'] ?? '') === '' || ($filters['order'] ?? '') === 'newest')>Newest</option>
                <option value="oldest" @selected(($filters['order'] ?? '') === 'oldest')>Oldest</option>
                <option value="updated" @selected(($filters['order'] ?? '') === 'updated')>Updated</option>
                <option value="score_desc" @selected(($filters['order'] ?? '') === 'score_desc')>Score (highest)</option>
                <option value="score_asc" @selected(($filters['order'] ?? '') === 'score_asc')>Score (lowest)</option>
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
            @php $votedDirection = $votedComments[$comment->id] ?? null; @endphp
            <div class="flex gap-4">
                @if ($comment->post)
                    <a href="{{ route('posts.show', $comment->post) }}"
                        class="flex items-center justify-center w-24 h-24 rounded overflow-hidden bg-gray-900 shrink-0">
                        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($comment->post->thumbnail_path) }}"
                            alt="post {{ $comment->post->id }}" class="max-w-full max-h-full object-contain">
                    </a>
                @endif
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="font-semibold text-sky-700">
                            @if ($comment->user)
                                <a href="{{ route('users.show', $comment->user) }}"
                                    class="hover:underline">{{ $comment->author_name }}</a>
                            @else
                                {{ $comment->author_name }}
                            @endif
                        </span>
                        <span class="text-xs text-gray-500">{{ $comment->created_at->diffForHumans() }}</span>
                        @if ($comment->parent_id)
                            <span class="text-xs text-gray-400 italic">(reply)</span>
                        @endif
                    </div>
                    <div class="text-sm text-gray-800 mb-1">
                        {!! \App\Support\SimpleMarkdown::toHtml(\Illuminate\Support\Str::limit($comment->body, 300)) !!}
                    </div>
                    <div class="flex items-center gap-3 text-xs">
                        <span class="flex items-center gap-1" data-comment-vote-widget
                            data-comment-id="{{ $comment->id }}" data-voted="{{ $votedDirection }}">
                            <button type="button" data-comment-vote="up"
                                class="{{ $votedDirection === 'up' ? 'text-green-400' : 'hover:text-green-400' }} cursor-pointer">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"
                                    class="w-3 h-3">
                                    <path d="M12 20V4M5 11l7-7 7 7" />
                                </svg>
                            </button>
                            <span data-comment-score>{{ $comment->score }}</span>
                            <button type="button" data-comment-vote="down"
                                class="{{ $votedDirection === 'down' ? 'text-red-400' : 'hover:text-red-400' }} cursor-pointer">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"
                                    class="w-3 h-3">
                                    <path d="M12 4v16M5 13l7 7 7-7" />
                                </svg>
                            </button>
                        </span>
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
