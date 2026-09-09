@extends('layouts.app')

@section('title', 'Post #' . $post->id)

@section('content')
    <div class="flex flex-col md:flex-row md:items-start gap-6">
        <aside class="space-y-4 text-sm md:w-52 md:shrink-0 md:sticky md:top-4 order-last md:order-none">
            <div>
                <h3 class="text-xm font-semibold uppercase text-gray-900 mb-2">Search</h3>
                <div class="relative" data-tag-autocomplete-wrapper>
                    <form method="GET" action="{{ route('posts.index') }}" class="flex gap-1">
                        <input type="text" name="tags" value="{{ $tagQuery }}"
                            placeholder="e.g. 1girl -weapon rating:general" autocomplete="off" data-tag-autocomplete
                            class="flex-1 min-w-0 px-2 py-1.5 rounded bg-white border border-gray-700 text-sm focus:outline-none focus:border-sky-500">
                        <button type="submit"
                            class="px-3 rounded bg-green-700 hover:bg-green-800 text-white text-sm cursor-pointer">Search</button>
                    </form>
                </div>
            </div>

            @foreach (['artist' => 'Artist', 'copyright' => 'Copyright', 'character' => 'Character', 'general' => 'General', 'meta' => 'Meta'] as $category => $label)
                @php
                    $tagsInCategory = $post->tags->where('category', $category)->sortByDesc('post_count');
                    $catColor = match ($category) {
                        'artist' => 'text-red-700',
                        'character' => 'text-green-700',
                        'copyright' => 'text-purple-700',
                        'meta' => 'text-amber-700',
                        default => 'text-sky-700',
                    };
                @endphp
                @if ($tagsInCategory->isNotEmpty())
                    <div>
                        <h3 class="text-xm font-semibold uppercase text-gray-900 mb-2">{{ $label }}</h3>
                        <ul class="space-y-1">
                            @foreach ($tagsInCategory as $tag)
                                <li class="flex justify-between gap-2">
                                    <span class="truncate">
                                        <a href="{{ route('posts.index', ['tags' => $tag->name, 'wiki' => 1]) }}"
                                            class="text-gray-600 hover:text-gray-900 mr-1">?</a>
                                        <a href="{{ route('posts.index', ['tags' => $tag->name]) }}"
                                            class="{{ $catColor }} hover:underline">{{ $tag->name }}</a>
                                    </span>
                                    <span class="text-gray-600 shrink-0">{{ $tag->post_count }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            @endforeach

            <div>
                <h3 class="text-xm font-semibold uppercase text-gray-900 mb-2">Information</h3>
                @php
                    $ratingColor = match ($post->rating) {
                        'sensitive' => 'text-sky-700',
                        'questionable' => 'text-amber-700',
                        'explicit' => 'text-red-700',
                        default => 'text-green-700',
                    };
                @endphp
                <ul class="space-y-1 text-gray-900">
                    <li>ID: {{ $post->id }}</li>
                    <li>Uploader: {{ $post->uploader?->name ?? 'Admin' }}</li>
                    <li>Date: {{ $post->created_at->diffForHumans() }}</li>
                    <li>Size: {{ $post->humanFileSize() }} .{{ $post->file_ext }}
                        ({{ $post->width }}×{{ $post->height }})</li>
                    @if ($post->source)
                        <li> Source: <a href="{{ $post->source }}" target="_blank"
                                class="text-green-900 hover:underline">{{ $post->source }}</a>
                        </li>
                    @endif
                    <li>Rating: <span class="{{ $ratingColor }}">{{ ucfirst($post->rating) }}</span></li>
                    <li class="flex items-center gap-2">
                        Score:
                        @php $votedDirection = $votedPosts[$post->id] ?? null; @endphp
                        <span class="flex items-center gap-1" data-vote-widget data-post-id="{{ $post->id }}"
                            data-voted="{{ $votedDirection }}">
                            <button type="button" data-vote="up"
                                class="{{ $votedDirection === 'up' ? 'text-green-800' : 'hover:text-green-700 cursor-pointer' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"
                                    class="w-3.5 h-3.5">
                                    <path d="M12 20V4M5 11l7-7 7 7" />
                                </svg>
                            </button>
                            <span data-score>{{ $post->score }}</span>
                            <button type="button" data-vote="down"
                                class="{{ $votedDirection === 'down' ? 'text-red-800' : 'hover:text-red-800 cursor-pointer' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"
                                    class="w-3.5 h-3.5">
                                    <path d="M12 4v16M5 13l7 7 7-7" />
                                </svg>
                            </button>
                        </span>
                    </li>
                    <li>Favorites: <span id="fav-count">{{ $favoriteCount }}</span></li>
                    <li>Status: {{ $post->is_approved ? 'Approved' : 'Pending' }}</li>
                </ul>
            </div>
            <div>
                <h3 class="text-xm font-semibold uppercase text-gray-900 mb-2">Options</h3>
                <ul class="space-y-1">
                    <li>
                        <button type="button" id="resize-window"
                            class="text-gray-700 hover:underline text-left cursor-pointer">
                            Resize to window
                        </button>
                    </li>
                    <li>
                        <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($post->file_path) }}"
                            target="_blank" class="text-gray-700 hover:underline">View original</a>
                    </li>
                    <li>
                        <a href="{{ route('posts.download', $post) }}" class="text-gray-700 hover:underline">Download</a>
                    </li>
                </ul>
            </div>
        </aside>

        <main class="flex-1 min-w-0">
            <div id="fav-banner"
                class="hidden mb-4 px-4 py-2 rounded bg-sky-800 text-white text-sm flex items-center justify-between">
                <span id="fav-banner-text"></span>
                <button type="button" id="fav-banner-close"
                    class="text-white/70 hover:text-white cursor-pointer">×</button>
            </div>

            <div class="relative rounded p-2 mb-3">
                <img id="post-image" src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($post->file_path) }}"
                    alt="post {{ $post->id }}" class="w-full max-h-[80vh] object-contain rounded mx-auto">

                @auth
                    <button type="button" id="fav-btn" data-post-id="{{ $post->id }}"
                        data-favorited="{{ $isFavorited ? '1' : '0' }}"
                        class="absolute top-4 left-4 w-9 h-9 flex items-center justify-center rounded bg-gray-900/70 hover:bg-gray-900/90 cursor-pointer">
                        <svg id="fav-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round"
                            class="w-5 h-5 {{ $isFavorited ? 'fill-red-500 stroke-red-500' : 'fill-none stroke-white' }}">
                            <path
                                d="M12 21s-6.716-4.35-9.428-8.09C.6 10.02 1.02 6.51 3.6 4.86c2.1-1.34 4.77-.9 6.4 1.02L12 8l2-2.12c1.63-1.92 4.3-2.36 6.4-1.02 2.58 1.65 3 5.16 1.028 8.05C18.716 16.65 12 21 12 21Z" />
                        </svg>
                    </button>
                @endauth
            </div>

            <div class="flex items-center justify-between text-sm mb-4 px-2 py-3 bg-white border border-gray-950 rounded">
                @if ($prevId)
                    <a href="{{ route('posts.show', ['post' => $prevId, 'tags' => $tagQuery]) }}"
                        class="text-gray-900 hover:underline">‹ prev</a>
                @else
                    <span class="text-gray-700">‹ prev</span>
                @endif

                @if ($tagQuery)
                    <span class="text-gray-500">Search: {{ $tagQuery }}</span>
                @endif

                @if ($nextId)
                    <a href="{{ route('posts.show', ['post' => $nextId, 'tags' => $tagQuery]) }}"
                        class="text-green-900 hover:underline">next ›</a>
                @else
                    <span class="text-gray-700">next ›</span>
                @endif
            </div>

            @if ($post->description)
                <div class="bg-white border border-gray-950 rounded p-3 text-sm text-gray-900 mb-4">
                    {{ $post->description }}
                </div>
            @endif

            <div id="comments">
                <h3 class="text-xm font-semibold uppercase text-gray-900 mb-3">
                    Comments ({{ $post->comments->count() }})
                </h3>

                <div class="space-y-4 mb-4">
                    @php $topLevelComments = $post->comments->whereNull('parent_id'); @endphp
                    @forelse ($topLevelComments as $comment)
                        @php
                            $votedDirection = $votedComments[$comment->id] ?? null;
                            $replies = $post->comments->where('parent_id', $comment->id)->sortBy('created_at');
                        @endphp
                        <div class="rounded p-3">
                            <div class="flex gap-3">
                                <div
                                    class="w-10 h-10 rounded-full overflow-hidden bg-gray-300 flex items-center justify-center shrink-0">
                                    @if ($comment->user?->avatarUrl())
                                        <img src="{{ $comment->user->avatarUrl() }}" alt="{{ $comment->author_name }}"
                                            class="w-full h-full object-cover">
                                    @else
                                        <span class="text-sm font-bold text-gray-600">
                                            {{ strtoupper(substr($comment->author_name, 0, 1)) }}
                                        </span>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between text-xs text-gray-500 mb-1">
                                        <span class="font-medium text-gray-900">{{ $comment->author_name }}</span>
                                        <span>{{ $comment->created_at->diffForHumans() }}</span>
                                    </div>
                                    <div class="text-sm text-gray-800 mb-1">
                                        {!! \App\Support\SimpleMarkdown::toHtml($comment->body) !!}
                                    </div>
                                    <div class="flex items-center gap-3 text-xs">
                                        <span class="flex items-center gap-1" data-comment-vote-widget
                                            data-comment-id="{{ $comment->id }}" data-voted="{{ $votedDirection }}">
                                            <button type="button" data-comment-vote="up"
                                                class="{{ $votedDirection === 'up' ? 'text-green-400' : 'hover:text-green-400' }} cursor-pointer">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                                                    fill="none" stroke="currentColor" stroke-width="2.5"
                                                    stroke-linecap="round" stroke-linejoin="round" class="w-3 h-3">
                                                    <path d="M12 20V4M5 11l7-7 7 7" />
                                                </svg>
                                            </button>
                                            <span data-comment-score>{{ $comment->score }}</span>
                                            <button type="button" data-comment-vote="down"
                                                class="{{ $votedDirection === 'down' ? 'text-red-400' : 'hover:text-red-400' }} cursor-pointer">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                                                    fill="none" stroke="currentColor" stroke-width="2.5"
                                                    stroke-linecap="round" stroke-linejoin="round" class="w-3 h-3">
                                                    <path d="M12 4v16M5 13l7 7 7-7" />
                                                </svg>
                                            </button>
                                        </span>
                                        @auth
                                            <button type="button"
                                                class="reply-toggle-btn text-sky-700 hover:underline cursor-pointer"
                                                data-username="{{ $comment->author_name }}"
                                                data-comment-id="{{ $comment->id }}">Reply</button>
                                        @endauth
                                        @if (auth()->user()?->isAdmin())
                                            <form method="POST" action="{{ route('comments.destroy', $comment) }}"
                                                onsubmit="return confirm('Delete this comment?')">
                                                @csrf
                                                @method('DELETE')
                                                <input type="hidden" name="tags" value="{{ $tagQuery }}">
                                                <button type="submit"
                                                    class="text-red-600 hover:underline cursor-pointer">Delete</button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        @auth
                            <form method="POST" action="{{ route('comments.store', $post) }}"
                                class="reply-form hidden mt-2 ml-12 space-y-2" data-comment-id="{{ $comment->id }}">
                                @csrf
                                <input type="hidden" name="tags" value="{{ $tagQuery }}">
                                <input type="hidden" name="parent_id" value="{{ $comment->id }}">

                                @include('posts._comment-editor', ['rows' => 2, 'placeholder' => 'Write a reply...'])

                                <button type="submit"
                                    class="px-2 py-1 rounded bg-green-700 hover:bg-green-800 text-white text-xs cursor-pointer">
                                    Reply
                                </button>
                            </form>
                        @endauth

                        @if ($replies->isNotEmpty())
                            <button type="button"
                                class="view-replies-btn mt-2 ml-12 text-xs text-sky-700 hover:underline cursor-pointer"
                                data-comment-id="{{ $comment->id }}" data-count="{{ $replies->count() }}">
                                View {{ $replies->count() }} {{ Str::plural('reply', $replies->count()) }}
                            </button>

                            <div class="replies-wrapper hidden mt-3 ml-12 pl-3 border-l-2 border-gray-300 space-y-3"
                                data-comment-id="{{ $comment->id }}">
                                @foreach ($replies as $reply)
                                    @php $replyVoted = $votedComments[$reply->id] ?? null; @endphp
                                    <div class="flex gap-2 {{ $loop->index >= 3 ? 'extra-reply hidden' : '' }}">
                                        <div
                                            class="w-7 h-7 rounded-full overflow-hidden bg-gray-300 flex items-center justify-center shrink-0">
                                            @if ($reply->user?->avatarUrl())
                                                <img src="{{ $reply->user->avatarUrl() }}"
                                                    alt="{{ $reply->author_name }}"
                                                    class="w-full h-full object-cover">
                                            @else
                                                <span class="text-xs font-bold text-gray-600">
                                                    {{ strtoupper(substr($reply->author_name, 0, 1)) }}
                                                </span>
                                            @endif
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div
                                                class="flex items-center justify-between text-[11px] text-gray-500 mb-0.5">
                                                <span
                                                    class="font-medium text-gray-900">{{ $reply->author_name }}</span>
                                                <span>{{ $reply->created_at->diffForHumans() }}</span>
                                            </div>
                                            <div class="text-xs text-gray-800">
                                                {!! \App\Support\SimpleMarkdown::toHtml($reply->body) !!}
                                            </div>
                                            <div class="flex items-center gap-2 text-[11px] mt-0.5">
                                                <span class="flex items-center gap-1" data-comment-vote-widget
                                                    data-comment-id="{{ $reply->id }}"
                                                    data-voted="{{ $replyVoted }}">
                                                    <button type="button" data-comment-vote="up"
                                                        class="{{ $replyVoted === 'up' ? 'text-green-400' : 'hover:text-green-400' }} cursor-pointer">
                                                        <svg xmlns="http://www.w3.org/2000/svg"
                                                            viewBox="0 0 24 24" fill="none"
                                                            stroke="currentColor" stroke-width="2.5"
                                                            stroke-linecap="round" stroke-linejoin="round"
                                                            class="w-2.5 h-2.5">
                                                            <path d="M12 20V4M5 11l7-7 7 7" />
                                                        </svg>
                                                    </button>
                                                    <span data-comment-score>{{ $reply->score }}</span>
                                                    <button type="button" data-comment-vote="down"
                                                        class="{{ $replyVoted === 'down' ? 'text-red-400' : 'hover:text-red-400' }} cursor-pointer">
                                                        <svg xmlns="http://www.w3.org/2000/svg"
                                                            viewBox="0 0 24 24" fill="none"
                                                            stroke="currentColor" stroke-width="2.5"
                                                            stroke-linecap="round" stroke-linejoin="round"
                                                            class="w-2.5 h-2.5">
                                                            <path d="M12 4v16M5 13l7 7 7-7" />
                                                        </svg>
                                                    </button>
                                                </span>
                                                @if (auth()->user()?->isAdmin())
                                                    <form method="POST"
                                                        action="{{ route('comments.destroy', $reply) }}"
                                                        onsubmit="return confirm('Delete this comment?')" class="inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <input type="hidden" name="tags"
                                                            value="{{ $tagQuery }}">
                                                        <button type="submit"
                                                            class="text-red-600 hover:underline cursor-pointer">Delete</button>
                                                    </form>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach

                                @if ($replies->count() > 3)
                                    <button type="button"
                                        class="view-more-replies-btn text-xs text-sky-700 hover:underline cursor-pointer">
                                        View {{ $replies->count() - 3 }} more
                                        {{ Str::plural('reply', $replies->count() - 3) }}
                                    </button>
                                @endif
                            </div>
                        @endif
                    @empty
                        <p class="text-sm text-gray-500">There are no comments.</p>
                    @endforelse
                </div>

                @auth
                    <form method="POST" action="{{ route('comments.store', $post) }}" class="space-y-2">
                        @csrf
                        <input type="hidden" name="tags" value="{{ $tagQuery }}">

                        @include('posts._comment-editor', ['rows' => 4])

                        @error('body')
                            <p class="text-xs text-red-600">{{ $message }}</p>
                        @enderror

                        <button type="submit"
                            class="px-3 py-1.5 rounded bg-green-700 hover:bg-green-800 text-white text-sm cursor-pointer">
                            Comment
                        </button>
                    </form>
                @else
                    <p class="text-sm text-gray-600">
                        <a href="{{ route('login') }}" class="text-sky-700 hover:underline">Login</a> to leave a comment.
                    </p>
                @endauth
            </div>
        </main>
    </div>

    <script>
        (function() {
            const img = document.getElementById('post-image');
            const btn = document.getElementById('resize-window');
            const defaultClasses = ['w-full', 'max-h-[80vh]', 'object-contain', 'rounded', 'mx-auto'];
            const enlargedClasses = ['w-full', 'max-h-none', 'object-contain', 'rounded', 'mx-auto'];
            let active = false;

            function activate() {
                active = true;
                img.className = enlargedClasses.join(' ');
                if (btn) btn.textContent = 'Fit to window';
            }

            function deactivate() {
                active = false;
                img.className = defaultClasses.join(' ');
                if (btn) btn.textContent = 'Resize to window';
            }

            btn?.addEventListener('click', (e) => {
                e.preventDefault();
                active ? deactivate() : activate();
            });
        })();

        (function() {
            const favBtn = document.getElementById('fav-btn');
            const favIcon = document.getElementById('fav-icon');
            const favCount = document.getElementById('fav-count');
            const banner = document.getElementById('fav-banner');
            const bannerText = document.getElementById('fav-banner-text');
            const bannerClose = document.getElementById('fav-banner-close');

            favBtn?.addEventListener('click', async () => {
                const postId = favBtn.dataset.postId;

                try {
                    const res = await fetch(`/posts/${postId}/favorite`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                .content,
                            'Accept': 'application/json',
                        },
                    });
                    const data = await res.json();

                    favBtn.dataset.favorited = data.favorited ? '1' : '0';
                    favIcon.classList.toggle('fill-red-500', data.favorited);
                    favIcon.classList.toggle('stroke-red-500', data.favorited);
                    favIcon.classList.toggle('fill-none', !data.favorited);
                    favIcon.classList.toggle('stroke-white', !data.favorited);
                    if (favCount) favCount.textContent = data.count;

                    bannerText.textContent = data.favorited ? 'You have favorited this post' :
                        'You have unfavorited this post';
                    banner.classList.remove('hidden');
                } catch (e) {
                    console.error('Favorite toggle failed', e);
                }
            });

            bannerClose?.addEventListener('click', () => banner.classList.add('hidden'));
        })();

        (function() {
            function escapeHtml(str) {
                const div = document.createElement('div');
                div.textContent = str;
                return div.innerHTML;
            }

            function renderMiniMarkdown(text) {
                let html = escapeHtml(text);
                html = html.replace(/!\[([^\]]*)\]\((https?:\/\/[^\s)]+)\)/g,
                    '<img src="$2" alt="$1" class="max-w-full rounded my-1">');
                html = html.replace(/\[([^\]]+)\]\((https?:\/\/[^\s)]+)\)/g,
                    '<a href="$2" target="_blank" rel="noopener" class="text-sky-700 underline">$1</a>');
                html = html.replace(/@([a-zA-Z0-9_.]+)/g, '<span class="text-sky-700 font-semibold">@$1</span>');
                html = html.replace(/\*\*(.+?)\*\*/gs, '<strong>$1</strong>');
                html = html.replace(/\*(.+?)\*/gs, '<em>$1</em>');
                return html.replace(/\n/g, '<br>');
            }

            function setupRichEditor(container) {
                const textarea = container.querySelector('.re-textarea');
                if (!textarea) return;

                const previewBtn = container.querySelector('.re-preview');
                const previewBox = container.querySelector('.re-preview-box');
                const boldBtn = container.querySelector('.re-bold');
                const italicBtn = container.querySelector('.re-italic');
                const linkBtn = container.querySelector('.re-link');
                const imageBtn = container.querySelector('.re-image');
                const imageInput = container.querySelector('.re-image-input');
                const emojiBtn = container.querySelector('.re-emoji');
                const emojiPicker = container.querySelector('.re-emoji-picker');

                const EMOJIS = ['😀', '😂', '😅', '😊', '😍', '😎', '🤔', '😢', '😭', '😡', '👍', '👎', '👏', '🙏',
                    '🔥', '✨', '🎉', '❤️', '💀', '😴', '😱', '🥺', '😏', '👀'
                ];
                emojiPicker.innerHTML = EMOJIS.map((e) =>
                    `<button type="button" class="hover:bg-gray-100 rounded" data-emoji="${e}">${e}</button>`
                ).join('');

                function wrapSelection(before, after = before) {
                    const start = textarea.selectionStart;
                    const end = textarea.selectionEnd;
                    const selected = textarea.value.slice(start, end);
                    textarea.setRangeText(before + selected + after, start, end, 'end');
                    textarea.focus();
                }

                function insertAtCursor(text) {
                    const start = textarea.selectionStart;
                    textarea.setRangeText(text, start, textarea.selectionEnd, 'end');
                    textarea.focus();
                }

                boldBtn?.addEventListener('click', () => wrapSelection('**'));
                italicBtn?.addEventListener('click', () => wrapSelection('*'));

                linkBtn?.addEventListener('click', () => {
                    const url = prompt('Enter URL:');
                    if (!url) return;
                    const start = textarea.selectionStart;
                    const end = textarea.selectionEnd;
                    const selected = textarea.value.slice(start, end) || 'link text';
                    textarea.setRangeText(`[${selected}](${url})`, start, end, 'end');
                    textarea.focus();
                });

                imageBtn?.addEventListener('click', () => imageInput.click());

                imageInput?.addEventListener('change', async () => {
                    const file = imageInput.files[0];
                    if (!file) return;

                    const formData = new FormData();
                    formData.append('image', file);

                    try {
                        const res = await fetch('{{ route('comments.upload-image') }}', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                    .content,
                                'Accept': 'application/json',
                            },
                            body: formData,
                        });
                        const data = await res.json();
                        if (data.url) insertAtCursor(`![](${data.url})`);
                    } catch (e) {
                        alert('Image upload failed.');
                    } finally {
                        imageInput.value = '';
                    }
                });

                emojiBtn?.addEventListener('click', (e) => {
                    e.stopPropagation();
                    emojiPicker.classList.toggle('hidden');
                });

                document.addEventListener('click', () => emojiPicker.classList.add('hidden'));

                emojiPicker?.addEventListener('click', (e) => {
                    const emoji = e.target.closest('[data-emoji]');
                    if (!emoji) return;
                    insertAtCursor(emoji.dataset.emoji);
                });

                let previewOn = false;
                previewBtn?.addEventListener('click', () => {
                    previewOn = !previewOn;
                    if (previewOn) {
                        previewBox.innerHTML = renderMiniMarkdown(textarea.value) ||
                            '<span class="text-gray-400">Nothing to preview.</span>';
                        previewBox.classList.remove('hidden');
                        textarea.classList.add('hidden');
                        previewBtn.textContent = '✏️ Edit';
                    } else {
                        previewBox.classList.add('hidden');
                        textarea.classList.remove('hidden');
                        previewBtn.textContent = '👁 Preview';
                    }
                });
            }

            document.querySelectorAll('.rich-editor').forEach(setupRichEditor);
        })();
    </script>
@endsection