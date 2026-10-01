@php
    use App\Filament\Resources\Posts\PostResource;
    use App\Models\Comment;
    use App\Models\Post;
    use Illuminate\Support\Facades\Storage;
@endphp

<div style="display: grid; gap: 1rem;">
    <dl style="display: grid; grid-template-columns: max-content 1fr; gap: 0.35rem 1rem;">
        <dt style="font-weight: 600;">Reported by</dt>
        <dd>{{ $report->reporter?->name ?? '-' }} &middot; {{ $report->created_at->format('Y-m-d H:i') }}</dd>

        <dt style="font-weight: 600;">Reason</dt>
        <dd style="white-space: pre-wrap;">{{ $report->reason }}</dd>

        <dt style="font-weight: 600;">Status</dt>
        <dd>{{ ucfirst($report->status) }}</dd>

        @unless ($report->isPending())
            <dt style="font-weight: 600;">Reviewed by</dt>
            <dd>{{ $report->reviewer?->name ?? '-' }} &middot; {{ $report->reviewed_at?->format('Y-m-d H:i') }}</dd>

            @if ($report->review_note)
                <dt style="font-weight: 600;">Review note</dt>
                <dd style="white-space: pre-wrap;">{{ $report->review_note }}</dd>
            @endif
        @endunless
    </dl>

    <hr>

    @if ($subject instanceof Post)
        @unless ($subject->thumbnailIsVideo() || blank($subject->thumbnail_path))
            <img src="{{ Storage::disk('public')->url($subject->thumbnail_path) }}" alt="post {{ $subject->id }}"
                style="max-width: 100%; max-height: 20rem; border-radius: 0.5rem;">
        @endunless
        <p>
            Uploader: {{ $subject->uploader?->name ?? '-' }} &middot; Rating: {{ ucfirst($subject->rating) }}
            &middot; Tags: {{ $subject->tags()->pluck('name')->take(15)->implode(' ') ?: '-' }}
        </p>
        <p>
            <a href="{{ PostResource::getUrl('edit', ['record' => $subject]) }}" style="text-decoration: underline;">
                Open post in admin
            </a>
        </p>
    @elseif ($subject instanceof Comment)
        <p style="font-weight: 600;">Comment by {{ $subject->author_name }}</p>
        <div style="white-space: pre-wrap;">{{ $subject->body }}</div>
    @else
        <p>This {{ strtolower($report->typeLabel()) }} no longer exists.</p>
    @endif
</div>