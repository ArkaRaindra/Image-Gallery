@php
    use App\Filament\Resources\Posts\PostResource;
    use Illuminate\Support\Facades\Storage;

    // Same colors as the public site: green = has children, orange = has a parent.
    $border = $border ?? '#f97316';
    $thumbUrl = Storage::disk('public')->url($item->thumbnail_path);
@endphp

<a href="{{ PostResource::getUrl('edit', ['record' => $item]) }}" title="Open post #{{ $item->id }}"
    style="display: block; width: 7rem; text-decoration: none; color: inherit;">
    <div style="height: 7rem; display: flex; align-items: center; justify-content: center; overflow: hidden; border: 2px solid {{ $border }}; border-radius: 0.375rem; background: rgba(128, 128, 128, 0.12);">
        @if ($item->thumbnailIsVideo())
            <video src="{{ $thumbUrl }}" muted preload="metadata"
                style="max-width: 100%; max-height: 100%; object-fit: contain;"></video>
        @else
            <img src="{{ $thumbUrl }}" alt="post {{ $item->id }}" loading="lazy"
                style="max-width: 100%; max-height: 100%; object-fit: contain;">
        @endif
    </div>
    <div style="display: flex; justify-content: space-between; gap: 0.25rem; margin-top: 0.15rem; font-size: 0.75rem; opacity: 0.8;">
        <span>#{{ $item->id }}</span>
        @unless ($item->is_approved)
            <span style="color: #d97706;">pending</span>
        @endunless
    </div>
</a>
