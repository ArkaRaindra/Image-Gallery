@php
    use App\Support\TagColors;
    use Illuminate\Support\Facades\Storage;

    $fileUrl = Storage::disk('public')->url($post->file_path);
    $thumbUrl = Storage::disk('public')->url($post->thumbnail_path);

    $formatSize = function (?int $bytes): string {
        if (! $bytes) {
            return '-';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $i = min((int) floor(log($bytes, 1024)), count($units) - 1);

        return round($bytes / (1024 ** $i), $i === 0 ? 0 : 2).' '.$units[$i];
    };

    $ratingColors = [
        'general' => '#15803d',
        'sensitive' => '#b45309',
        'questionable' => '#c2410c',
        'explicit' => '#b91c1c',
    ];

    $tagGroups = $post->tags->sortBy('name')->groupBy('category');
    $categoryOrder = ['artist', 'copyright', 'character', 'general', 'meta'];

    $labelStyle = 'font-weight: 600;';
    $mutedStyle = 'font-size: 0.8rem; opacity: 0.7;';
    $headingStyle = 'font-weight: 700; font-size: 1rem; margin-bottom: 0.5rem;';
    $sectionStyle = 'padding-top: 1rem; border-top: 1px solid rgba(128, 128, 128, 0.3);';
@endphp

<div style="display: grid; gap: 1rem;">
    {{-- Preview --}}
    <div style="display: flex; justify-content: center; padding: 0.5rem; border-radius: 0.5rem; background: rgba(128, 128, 128, 0.12);">
        @if ($post->isVideo())
            <video src="{{ $fileUrl }}" poster="{{ $post->hasCustomThumbnail() && ! $post->thumbnailIsVideo() ? $thumbUrl : '' }}"
                controls preload="metadata" style="max-width: 100%; max-height: 28rem; border-radius: 0.375rem;"></video>
        @else
            <img src="{{ $fileUrl }}" alt="post {{ $post->id }}"
                style="max-width: 100%; max-height: 28rem; object-fit: contain; border-radius: 0.375rem;">
        @endif
    </div>

    @if ($hasPendingDeletion)
        <div style="padding: 0.5rem 0.75rem; border-radius: 0.375rem; border: 1px solid #b91c1c; color: #b91c1c; font-size: 0.875rem;">
            A deletion request for this post is pending review.
        </div>
    @endif

    {{-- Details --}}
    <dl style="display: grid; grid-template-columns: max-content 1fr; gap: 0.35rem 1.5rem; font-size: 0.875rem;">
        <dt style="{{ $labelStyle }}">Uploader</dt>
        <dd>{{ $post->uploader?->name ?? '-' }}</dd>

        <dt style="{{ $labelStyle }}">Rating</dt>
        <dd style="color: {{ $ratingColors[$post->rating] ?? 'inherit' }}; font-weight: 600;">{{ ucfirst($post->rating) }}</dd>

        <dt style="{{ $labelStyle }}">Score</dt>
        <dd>{{ $post->score }}</dd>

        <dt style="{{ $labelStyle }}">Approved</dt>
        <dd>
            {{ $post->is_approved ? 'Yes' : 'No' }}
            @if ($post->is_approved && $post->approver)
                <span style="{{ $mutedStyle }}">&middot; by {{ $post->approver->name }}@if ($post->approved_at), {{ $post->approved_at->format('Y-m-d H:i') }}@endif</span>
            @endif
        </dd>

        <dt style="{{ $labelStyle }}">Source</dt>
        <dd style="overflow-wrap: anywhere;">
            @if ($post->source)
                <a href="{{ $post->source }}" target="_blank" rel="noopener noreferrer" style="text-decoration: underline;">{{ $post->source }}</a>
            @else
                -
            @endif
        </dd>

        <dt style="{{ $labelStyle }}">Description</dt>
        <dd style="white-space: pre-wrap;">{{ $post->description ?: '-' }}</dd>

        <dt style="{{ $labelStyle }}">File</dt>
        <dd style="overflow-wrap: anywhere;">
            {{ $post->file_name }}
            <span style="{{ $mutedStyle }}">
                &middot; {{ strtoupper($post->file_ext) }}
                &middot; {{ $formatSize($post->file_size) }}
                @if ($post->width && $post->height)
                    &middot; {{ $post->width }}&times;{{ $post->height }}
                @endif
            </span>
        </dd>

        <dt style="{{ $labelStyle }}">MD5</dt>
        <dd style="font-family: ui-monospace, monospace; font-size: 0.8rem; overflow-wrap: anywhere;">{{ $post->md5 ?: '-' }}</dd>

        <dt style="{{ $labelStyle }}">Created</dt>
        <dd>{{ $post->created_at?->format('Y-m-d H:i') }}</dd>

        <dt style="{{ $labelStyle }}">Updated</dt>
        <dd>{{ $post->updated_at?->format('Y-m-d H:i') }}</dd>
    </dl>

    {{-- Tags --}}
    <div style="{{ $sectionStyle }}">
        <div style="{{ $headingStyle }}">Tags ({{ $post->tags->count() }})</div>

        @if ($post->tags->isEmpty())
            <span style="{{ $mutedStyle }}">No tags.</span>
        @endif

        @foreach ($categoryOrder as $category)
            @if ($tagGroups->has($category))
                <div style="display: flex; flex-wrap: wrap; align-items: baseline; gap: 0.25rem 0.75rem; margin-bottom: 0.35rem; font-size: 0.875rem;">
                    <span style="{{ $mutedStyle }} min-width: 5.5rem;">{{ ucfirst($category) }}</span>
                    @foreach ($tagGroups[$category] as $tag)
                        <span style="color: {{ TagColors::hex($tag->category) }};">{{ $tag->name }}</span>
                    @endforeach
                </div>
            @endif
        @endforeach
    </div>

    {{-- Parent, siblings and children --}}
    <div style="{{ $sectionStyle }}">
        <div style="{{ $headingStyle }}">Parent</div>

        @if ($parent)
            <div style="display: flex; flex-wrap: wrap; gap: 0.75rem;">
                @include('filament.posts.family-thumb', ['item' => $parent, 'border' => '#16a34a'])
            </div>
        @else
            <span style="{{ $mutedStyle }}">This post has no parent.</span>
        @endif
    </div>

    @if ($parent)
        <div style="{{ $sectionStyle }}">
            <div style="{{ $headingStyle }}">Siblings ({{ $siblings->count() }})</div>

            @if ($siblings->isNotEmpty())
                <div style="display: flex; flex-wrap: wrap; gap: 0.75rem;">
                    @foreach ($siblings as $item)
                        @include('filament.posts.family-thumb', ['item' => $item, 'border' => '#f97316'])
                    @endforeach
                </div>
            @else
                <span style="{{ $mutedStyle }}">No other posts share this parent.</span>
            @endif
        </div>
    @endif

    <div style="{{ $sectionStyle }}">
        <div style="{{ $headingStyle }}">Children ({{ $children->count() }})</div>

        @if ($children->isNotEmpty())
            <div style="display: flex; flex-wrap: wrap; gap: 0.75rem;">
                @foreach ($children as $item)
                    @include('filament.posts.family-thumb', ['item' => $item, 'border' => '#f97316'])
                @endforeach
            </div>
        @else
            <span style="{{ $mutedStyle }}">This post has no children.</span>
        @endif
    </div>

    @if ($parent || $children->isNotEmpty())
        <p style="{{ $mutedStyle }}">
            Green border: the post has children. Orange border: the post has a parent. Click a thumbnail to open that post.
        </p>
    @endif
</div>
