@extends('layouts.app')

@section('title', 'Edit Artist')

@section('content')
    <h1 class="text-2xl font-bold mb-1">Edit Artist</h1>
    <p class="text-sm mb-4">
        <a href="{{ route('wiki.show', $artist->tag->name) }}"
            class="{{ $artist->tag->categoryTextClass() }} hover:underline">{{ $artist->tag->displayName() }}</a>
        &middot;
        <a href="{{ route('artists.versions', ['artist' => $artist->tag->name]) }}"
            class="text-sky-700 hover:underline">History</a>
    </p>

    @include('artists._form', [
        'action' => route('artists.update', $artist),
        'method' => 'PUT',
        'artist' => $artist,
    ])
@endsection
