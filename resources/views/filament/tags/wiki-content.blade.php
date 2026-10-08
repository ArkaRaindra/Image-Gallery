@php
    use Illuminate\Support\Str;

    $html = (string) $getState();

    // Filament's sanitizer, when available. The content comes from the admin rich editor.
    if (Str::hasMacro('sanitizeHtml')) {
        $html = Str::sanitizeHtml($html);
    }
@endphp

<x-dynamic-component :component="$getEntryWrapperView()" :entry="$entry">
    {{-- The panel's CSS reset flattens headings, lists, etc., so they need their own styles here. --}}
    <style>
        .tag-wiki-content { font-size: 0.875rem; line-height: 1.5; overflow-wrap: anywhere; }
        .tag-wiki-content > :first-child { margin-top: 0; }
        .tag-wiki-content p { margin-bottom: 0.5rem; }
        .tag-wiki-content img { max-width: 100%; border-radius: 0.25rem; margin: 0.5rem 0; }
        .tag-wiki-content a { color: #0ea5e9; text-decoration: underline; }
        .tag-wiki-content h1, .tag-wiki-content h2, .tag-wiki-content h3,
        .tag-wiki-content h4, .tag-wiki-content h5, .tag-wiki-content h6 {
            font-weight: 700; line-height: 1.3; margin: 1rem 0 0.5rem;
        }
        .tag-wiki-content h1 { font-size: 1.5rem; }
        .tag-wiki-content h2 { font-size: 1.25rem; padding-bottom: 0.25rem; border-bottom: 1px solid rgba(128, 128, 128, 0.35); }
        .tag-wiki-content h3 { font-size: 1.125rem; }
        .tag-wiki-content h4 { font-size: 1rem; }
        .tag-wiki-content h5, .tag-wiki-content h6 { font-size: 0.875rem; }
        .tag-wiki-content ul { list-style: disc; padding-left: 1.5rem; margin-bottom: 0.5rem; }
        .tag-wiki-content ol { list-style: decimal; padding-left: 1.5rem; margin-bottom: 0.5rem; }
        .tag-wiki-content blockquote { border-left: 3px solid rgba(128, 128, 128, 0.6); padding-left: 0.75rem; margin: 0.5rem 0; opacity: 0.85; }
        .tag-wiki-content hr { border-top: 1px solid rgba(128, 128, 128, 0.35); margin: 1rem 0; }
        .tag-wiki-content strong { font-weight: 700; }
        .tag-wiki-content em { font-style: italic; }
        .tag-wiki-content code, .tag-wiki-content pre {
            font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
            background: rgba(128, 128, 128, 0.15); border-radius: 0.25rem;
        }
        .tag-wiki-content code { padding: 0.1rem 0.3rem; }
        .tag-wiki-content pre { padding: 0.5rem 0.75rem; margin: 0.5rem 0; overflow-x: auto; }
        .tag-wiki-content table { border-collapse: collapse; margin: 0.5rem 0; }
        .tag-wiki-content th, .tag-wiki-content td { border: 1px solid rgba(128, 128, 128, 0.4); padding: 0.25rem 0.5rem; }
    </style>

    <div class="tag-wiki-content">
        @if (filled(trim(strip_tags($html, '<img>'))))
            {!! $html !!}
        @else
            <span style="opacity: 0.7;">No wiki content yet.</span>
        @endif
    </div>
</x-dynamic-component>