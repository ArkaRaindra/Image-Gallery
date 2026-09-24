@extends('layouts.app')

@section('title', 'Comments')

@section('content')
    <h1 class="text-2xl font-bold mb-4">Comments</h1>

    @include('comments._list')
@endsection