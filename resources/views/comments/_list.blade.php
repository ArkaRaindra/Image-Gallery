<div class="space-y-6">
    @forelse ($comments as $comment)
        @php $votedDirection = $votedComments[$comment->id] ?? null; @endphp
        <div class="flex gap-4">
            @if ($comment->post)
                <a href="{{ route('posts.show', $comment->post) }}" data-thumb-container
                    class="relative flex items-center justify-center w-[200px] h-[200px] rounded overflow-hidden shrink-0 cursor-default">
                    <div class="absolute inset-0" data-thumb-fit>
                        @include('partials.duration-badge', ['post' => $comment->post])
                        @if ($comment->post->thumbnailIsVideo())
                            <video data-thumb-media
                                src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($comment->post->thumbnail_path) }}"
                                class="block w-full h-full object-contain cursor-pointer" muted loop playsinline
                                preload="metadata" onmouseover="this.play()"
                                onmouseout="this.pause(); this.currentTime = 0;"></video>
                        @else
                            <img data-thumb-media
                                src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($comment->post->thumbnail_path) }}"
                                alt="post {{ $comment->post->id }}"
                                class="block w-full h-full object-contain cursor-pointer">
                        @endif
                    </div>
                </a>
            @endif
            <div class="flex-1 min-w-0">
                @if ($comment->post)
                    @include('comments._post-meta', ['comment' => $comment])
                @endif

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
    {{ $comments->links('partials.pagination-centered') }}
</div>