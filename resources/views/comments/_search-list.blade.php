@php
    $runs = [];
    foreach ($comments as $item) {
        $last = count($runs) - 1;
        if ($last >= 0 && $runs[$last]['post_id'] === $item->post_id) {
            $runs[$last]['comments'][] = $item;
        } else {
            $runs[] = ['post_id' => $item->post_id, 'post' => $item->post, 'comments' => [$item]];
        }
    }
@endphp

<div class="space-y-8">
    @forelse ($runs as $run)
        @php $post = $run['post']; @endphp
        <div class="flex gap-4">
            @if ($post)
                <a href="{{ route('posts.show', $post) }}" data-thumb-container
                    class="relative flex items-center justify-center w-[200px] h-[200px] rounded overflow-hidden shrink-0 cursor-default">
                    <div class="absolute inset-0" data-thumb-fit>
                        @include('partials.duration-badge', ['post' => $post])
                        @if ($post->thumbnailIsVideo())
                            <video data-thumb-media
                                src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($post->thumbnail_path) }}"
                                class="block w-full h-full object-contain cursor-pointer" muted loop playsinline
                                preload="metadata" onmouseover="this.play()"
                                onmouseout="this.pause(); this.currentTime = 0;"></video>
                        @else
                            <img data-thumb-media
                                src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($post->thumbnail_path) }}"
                                alt="post {{ $post->id }}" class="block w-full h-full object-contain cursor-pointer">
                        @endif
                    </div>
                </a>
            @endif

            <div class="flex-1 min-w-0 space-y-5">
                @foreach ($run['comments'] as $comment)
                    @php
                        $votedDirection = $votedComments[$comment->id] ?? null;
                        $canEdit = auth()->id() && auth()->id() === $comment->user_id;
                        $isAdmin = auth()->user()?->isAdmin();
                    @endphp
                    <div class="flex gap-6">
                        {{-- Kolom kiri: author + waktu --}}
                        <div class="w-36 shrink-0 text-sm">
                            <div class="font-semibold text-sky-700 break-words">
                                @if ($comment->user)
                                    <a href="{{ route('users.show', $comment->user) }}"
                                        class="hover:underline">{{ $comment->author_name }}</a>
                                @else
                                    {{ $comment->author_name }}
                                @endif
                            </div>
                            <div class="text-xs italic text-gray-500">{{ $comment->created_at->diffForHumans() }}</div>
                        </div>

                        {{-- Kolom kanan: quote (jika reply), isi, aksi --}}
                        <div class="flex-1 min-w-0">
                            @if ($comment->parent)
                                <div class="border-l-4 border-gray-400 pl-3 mb-2 text-sm text-gray-500">
                                    <div class="mb-1">
                                        {{ $comment->parent->author_name }} said in
                                        @if ($post)
                                            <a href="{{ route('posts.show', $post) }}#comments"
                                                class="text-sky-700 hover:underline">comment #{{ $comment->parent->id }}</a>:
                                        @else
                                            comment #{{ $comment->parent->id }}:
                                        @endif
                                    </div>
                                    <div>
                                        {!! \App\Support\SimpleMarkdown::toHtml(\Illuminate\Support\Str::limit($comment->parent->body, 200)) !!}
                                    </div>
                                </div>
                            @endif

                            <div class="text-sm text-gray-800 mb-1">
                                {!! \App\Support\SimpleMarkdown::toHtml(\Illuminate\Support\Str::limit($comment->body, 300)) !!}
                            </div>

                            <div class="flex items-center gap-3 text-xs">
                                <span class="flex items-center gap-1" data-comment-vote-widget
                                    data-comment-id="{{ $comment->id }}" data-voted="{{ $votedDirection }}">
                                    <button type="button" data-comment-vote="up"
                                        class="{{ $votedDirection === 'up' ? 'text-green-400' : 'hover:text-green-400' }} cursor-pointer">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                                            stroke-linejoin="round" class="w-3 h-3">
                                            <path d="M12 20V4M5 11l7-7 7 7" />
                                        </svg>
                                    </button>
                                    <span data-comment-score>{{ $comment->score }}</span>
                                    <button type="button" data-comment-vote="down"
                                        class="{{ $votedDirection === 'down' ? 'text-red-400' : 'hover:text-red-400' }} cursor-pointer">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                                            stroke-linejoin="round" class="w-3 h-3">
                                            <path d="M12 4v16M5 13l7 7 7-7" />
                                        </svg>
                                    </button>
                                </span>

                                @if ($post)
                                    <a href="{{ route('posts.show', $post) }}#comments"
                                        class="text-sky-700 hover:underline">Reply</a>
                                @endif

                                @if ($canEdit || $isAdmin)
                                    <details class="relative">
                                        <summary
                                            class="list-none cursor-pointer text-gray-500 hover:text-gray-800 select-none">
                                            &middot;&middot;&middot;</summary>
                                        <div
                                            class="absolute left-0 top-5 z-10 min-w-[7rem] rounded border border-gray-300 bg-white shadow py-1">
                                            @if ($canEdit && $post)
                                                <a href="{{ route('posts.show', $post) }}#comments"
                                                    class="block px-3 py-1 text-sky-700 hover:bg-gray-100">Edit</a>
                                            @endif
                                            @if ($isAdmin)
                                                <form method="POST" action="{{ route('comments.destroy', $comment) }}"
                                                    onsubmit="return confirm('Delete this comment?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                        class="block w-full text-left px-3 py-1 text-red-600 hover:bg-gray-100 cursor-pointer">Delete</button>
                                                </form>
                                            @endif
                                        </div>
                                    </details>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @empty
        <p class="text-gray-500">No comments found.</p>
    @endforelse
</div>

<div class="mt-6">
    {{ $comments->links('partials.pagination-centered') }}
</div>