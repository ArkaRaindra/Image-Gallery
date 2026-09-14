@extends('layouts.app')

@section('title', 'Home')

@section('content')
    <div class="flex flex-col items-center justify-center gap-6 min-h-[70vh] text-center px-4">
        <a href="{{ route('home') }}">
            <img src="{{ asset('images/VSC.png') }}" alt="Logo" class="w-40 h-40 md:w-56 md:h-56 object-contain">
        </a>

        <img src="https://count.getloli.com/@Image?name=Image&theme=rule34&padding=7&offset=0&align=top&scale=2&pixelated=1&darkmode=auto"
            alt="Visitor counter" class="h-32 md:h-40 object-contain">

        <div class="w-full max-w-xl">
            <div class="relative" data-tag-autocomplete-wrapper>
                <form method="GET" action="{{ route('posts.index') }}" class="flex gap-2">
                    <input type="text" name="tags" placeholder="e.g. 1girl -weapon rating:general"
                        autocomplete="off" data-tag-autocomplete
                        class="flex-1 min-w-0 px-4 py-2.5 rounded bg-white border border-gray-700 text-sm focus:outline-none focus:border-sky-500">
                    <button type="submit"
                        class="px-5 rounded bg-green-700 hover:bg-green-800 text-white text-sm font-medium cursor-pointer">
                        Search
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection