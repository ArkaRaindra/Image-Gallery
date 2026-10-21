@extends('layouts.app')

@section('title', 'Wiki Diff')

@section('content')
    <div class="flex flex-col md:flex-row md:items-start gap-6">
        @include('partials.wiki-sidebar')

        <main class="flex-1 min-w-0">
            <h1 class="text-2xl font-bold mb-2">Wiki Page Diff</h1>

            <p class="text-sm text-gray-800 mb-1">
                @if ($version->tag->hasWiki())
                    <a href="{{ route('wiki.show', $version->tag->name) }}"
                        class="{{ $version->tag->categoryTextClass() }} hover:underline">{{ $version->tag->displayName() }}</a>
                @else
                    {{ $version->tag->displayName() }}
                @endif
                &middot;
                version {{ $previous->version }}
                ({{ $previous->updater?->name ?? 'System' }}, {{ $previous->created_at->format('Y-m-d H:i') }})
                compared with version {{ $version->version }}
                ({{ $version->updater?->name ?? 'System' }}, {{ $version->created_at->format('Y-m-d H:i') }})
            </p>

            <p class="text-xs text-gray-700 mb-4">
                <span class="bg-red-200 text-red-900 line-through px-1">Removed</span>
                <span class="bg-green-200 text-green-900 px-1">Added</span>
                &middot;
                <a href="{{ route('wiki.versions', ['tag_id' => $version->tag_id]) }}"
                    class="text-sky-700 hover:underline">Wiki History</a>
            </p>

            @if ($otherNamesSegments)
                <h2 class="text-sm font-semibold mb-1">Other names</h2>
                <div class="mb-4 bg-white border border-gray-800 rounded p-3 text-sm whitespace-pre-wrap break-words">@include('wiki._diff-text', ['segments' => $otherNamesSegments])</div>
            @endif

            <h2 class="text-sm font-semibold mb-1">Content (HTML)</h2>
            <div class="bg-white border border-gray-800 rounded p-3 text-sm font-mono whitespace-pre-wrap break-words">@include('wiki._diff-text', ['segments' => $bodySegments])</div>
        </main>
    </div>
@endsection