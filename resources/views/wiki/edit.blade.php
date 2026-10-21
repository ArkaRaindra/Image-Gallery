@extends('layouts.app')

@section('title', 'Edit '.$wikiTag->displayName())

@section('content')
    <div class="flex flex-col md:flex-row md:items-start gap-6">
        @include('partials.wiki-sidebar')

        <main class="flex-1 min-w-0">
            <h1 class="text-2xl font-bold mb-3">Edit Wiki Page</h1>

            @include('wiki._form', [
                'action' => route('wiki.update', $wikiTag->name),
                'method' => 'PUT',
                'wikiTag' => $wikiTag,
                'body' => $body,
            ])
        </main>
    </div>
@endsection