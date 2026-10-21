@extends('layouts.app')

@section('title', $wikiTag->displayName())

@section('content')
    <div class="flex flex-col md:flex-row md:items-start gap-6">
        @include('partials.wiki-sidebar')

        <main class="flex-1 min-w-0">
            <h1 class="text-3xl font-bold break-words {{ $wikiTag->categoryTextClass() }}">{{ $wikiTag->displayName() }}</h1>

            @if ($wikiTag->otherNameList() !== [])
                <div class="flex flex-wrap gap-1 mt-2">
                    @foreach ($wikiTag->otherNameList() as $otherName)
                        <span class="px-2 py-0.5 rounded border border-gray-700 bg-white/60 text-sm text-sky-700">{{ $otherName }}</span>
                    @endforeach
                </div>
            @endif

            <div class="wiki-content mt-4 text-sm text-gray-900 break-words">
                {!! $bodyHtml !!}
            </div>

            <p class="mt-8 text-xs text-gray-600">
                Last edited
                @if ($wikiTag->wikiUpdater)
                    by
                    <a href="{{ route('users.show', $wikiTag->wikiUpdater) }}"
                        class="text-sky-700 hover:underline">{{ $wikiTag->wikiUpdater->name }}</a>
                @endif
                @if ($wikiTag->wiki_updated_at)
                    on {{ $wikiTag->wiki_updated_at->format('Y-m-d H:i') }}
                @endif
                &middot; version {{ $wikiTag->wiki_version }}
            </p>
        </main>
    </div>

    @include('wiki._content-styles')
@endsection