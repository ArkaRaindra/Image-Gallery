@extends('layouts.app')

@section('title', 'Wiki page not found')

@section('content')
    <div class="flex flex-col md:flex-row md:items-start gap-6">
        @include('partials.wiki-sidebar')

        <main class="flex-1 min-w-0">
            <h1 class="text-2xl font-bold mb-3">{{ str_replace('_', ' ', $title) }}</h1>

            <p class="text-sm text-gray-900">This wiki page does not exist.</p>

            <p class="mt-3 text-sm">
                @if ($tag)
                    @auth
                        <a href="{{ route('wiki.create', ['tag' => $tag->name]) }}"
                            class="text-sky-700 hover:underline">Write the wiki of this tag</a>
                    @else
                        <a href="{{ route('login') }}" class="text-sky-700 hover:underline">Log in</a>
                        to write the wiki of this tag.
                    @endauth
                @else
                    A wiki page can only be created for an existing tag.
                @endif
            </p>
        </main>
    </div>
@endsection