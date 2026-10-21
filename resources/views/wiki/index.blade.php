@extends('layouts.app')

@section('title', 'Wiki')

@section('content')
    <div class="flex flex-col md:flex-row md:items-start gap-6">
        @include('partials.wiki-sidebar')

        <main class="flex-1 min-w-0">
            <h1 class="text-2xl font-bold mb-3">Wiki</h1>

            <form method="GET" action="{{ route('wiki.index') }}" class="mb-4 grid gap-2 max-w-xl text-sm">
                <label class="grid grid-cols-[7rem_1fr] items-center gap-2">
                    <span class="font-semibold text-right">Title</span>
                    <div class="relative" data-wiki-tag-wrapper>
                        <input type="text" name="title" value="{{ $filters['title'] }}" autocomplete="off"
                            data-wiki-tag-autocomplete data-with-wiki="1"
                            class="w-full px-2 py-1 rounded bg-white border border-gray-700 focus:outline-none focus:border-sky-500">
                    </div>
                </label>
                <label class="grid grid-cols-[7rem_1fr] items-center gap-2">
                    <span class="font-semibold text-right">Other names</span>
                    <input type="text" name="other_names" value="{{ $filters['other_names'] }}"
                        class="px-2 py-1 rounded bg-white border border-gray-700 focus:outline-none focus:border-sky-500">
                </label>
                <label class="grid grid-cols-[7rem_1fr] items-center gap-2">
                    <span class="font-semibold text-right">Text</span>
                    <input type="text" name="text" value="{{ $filters['text'] }}"
                        class="px-2 py-1 rounded bg-white border border-gray-700 focus:outline-none focus:border-sky-500">
                </label>
                <label class="grid grid-cols-[7rem_1fr] items-center gap-2">
                    <span class="font-semibold text-right">Order</span>
                    <select name="order"
                        class="px-2 py-1 rounded bg-white border border-gray-700 focus:outline-none focus:border-sky-500">
                        <option value="updated" @selected($filters['order'] === 'updated')>Last edited</option>
                        <option value="title" @selected($filters['order'] === 'title')>Title</option>
                    </select>
                </label>
                <div class="pl-[7.5rem]">
                    <button type="submit"
                        class="px-4 py-1 rounded bg-green-700 hover:bg-green-800 text-white cursor-pointer">Search</button>
                </div>
            </form>

            <div class="overflow-x-auto bg-white border border-gray-800 rounded">
                <table class="w-full text-sm border-collapse">
                    <thead>
                        <tr class="text-left border-b border-gray-800 bg-gray-100">
                            <th class="py-2 px-3 font-semibold">Title</th>
                            <th class="py-2 px-3 font-semibold whitespace-nowrap">Last edited</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($pages as $page)
                            <tr class="border-b border-gray-200">
                                <td class="py-2 px-3 break-all">
                                    <a href="{{ route('wiki.show', $page->name) }}"
                                        class="{{ $page->categoryTextClass() }} hover:underline">{{ $page->name }}</a>
                                </td>
                                <td class="py-2 px-3 whitespace-nowrap"
                                    title="{{ $page->wiki_updated_at?->format('Y-m-d H:i') }}">
                                    {{ $page->wiki_updated_at?->diffForHumans() }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="py-10 text-center text-gray-500">No wiki pages match this search.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-6">
                {{ $pages->links('partials.pagination-centered') }}
            </div>
        </main>
    </div>

    @include('wiki._tag-autocomplete')
@endsection