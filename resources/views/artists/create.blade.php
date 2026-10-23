@extends('layouts.app')

@section('title', 'New Artist')

@section('content')
    <h1 class="text-2xl font-bold mb-3">New Artist</h1>

    @include('artists._form', [
        'action' => route('artists.store'),
        'method' => 'POST',
        'artist' => null,
    ])
@endsection
