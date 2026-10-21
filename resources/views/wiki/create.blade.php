@extends('layouts.app')

@section('title', 'New Wiki Page')

@section('content')
    <div class="flex flex-col md:flex-row md:items-start gap-6">
        @include('partials.wiki-sidebar')

        <main class="flex-1 min-w-0">
            <h1 class="text-2xl font-bold mb-3">New Wiki Page</h1>

            @include('wiki._form', [
                'action' => route('wiki.store'),
                'method' => 'POST',
                'wikiTag' => null,
                'prefillTag' => $prefillTag,
                'existingWikiTag' => $existingWikiTag,
            ])
        </main>
    </div>
@endsection