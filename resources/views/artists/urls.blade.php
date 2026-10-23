@extends('layouts.app')

@section('title', 'Artist URLs')

@section('content')
    <h1 class="text-2xl font-bold mb-3">Artist URLs</h1>

    <form method="GET" action="{{ route('artists.urls') }}" class="mb-4 grid gap-2 max-w-xl text-sm">
        <label class="grid grid-cols-[7rem_1fr] items-center gap-2">
            <span class="font-semibold text-right">Artist Name</span>
            <div class="relative" data-wiki-tag-wrapper>
                <input type="text" name="artist_name" value="{{ $filters['artist_name'] }}" autocomplete="off"
                    data-wiki-tag-autocomplete data-category="artist"
                    class="w-full px-2 py-1 rounded bg-white border border-gray-700 focus:outline-none focus:border-sky-500">
            </div>
        </label>
        <label class="grid grid-cols-[7rem_1fr] items-center gap-2">
            <span class="font-semibold text-right">URL</span>
            <input type="text" name="url" value="{{ $filters['url'] }}" autocomplete="off"
                class="px-2 py-1 rounded bg-white border border-gray-700 focus:outline-none focus:border-sky-500">
        </label>
        <label class="grid grid-cols-[7rem_1fr] items-center gap-2">
            <span class="font-semibold text-right">Active?</span>
            <select name="active"
                class="w-24 px-2 py-1 rounded bg-white border border-gray-700 focus:outline-none focus:border-sky-500">
                <option value=""></option>
                <option value="true" @selected($filters['active'] === 'true')>Yes</option>
                <option value="false" @selected($filters['active'] === 'false')>No</option>
            </select>
        </label>
        <label class="grid grid-cols-[7rem_1fr] items-center gap-2">
            <span class="font-semibold text-right">Order</span>
            <select name="order"
                class="w-32 px-2 py-1 rounded bg-white border border-gray-700 focus:outline-none focus:border-sky-500">
                <option value="id" @selected($filters['order'] === 'id')>ID</option>
                <option value="created" @selected($filters['order'] === 'created')>Created</option>
                <option value="updated" @selected($filters['order'] === 'updated')>Updated</option>
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
                    <th class="py-2 px-3 font-semibold">Id</th>
                    <th class="py-2 px-3 font-semibold">Artist Name</th>
                    <th class="py-2 px-3 font-semibold">URL</th>
                    <th class="py-2 px-3 font-semibold">Active?</th>
                    <th class="py-2 px-3 font-semibold whitespace-nowrap">Created</th>
                    <th class="py-2 px-3 font-semibold whitespace-nowrap">Updated</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($urls as $url)
                    <tr class="border-b border-gray-200 align-top">
                        <td class="py-2 px-3">{{ $url->id }}</td>
                        <td class="py-2 px-3 break-all">
                            <a href="{{ route('wiki.show', $url->artist->tag->name) }}"
                                class="{{ $url->artist->tag->categoryTextClass() }} hover:underline">{{ $url->artist->tag->name }}</a>
                        </td>
                        <td class="py-2 px-3 break-all">
                            <a href="{{ $url->url }}" target="_blank" rel="noopener noreferrer nofollow"
                                class="text-sky-700 hover:underline">{{ $url->url }}</a>
                        </td>
                        <td class="py-2 px-3">{{ $url->is_active ? 'true' : 'false' }}</td>
                        <td class="py-2 px-3 whitespace-nowrap">{{ $url->created_at?->format('Y-m-d H:i') }}</td>
                        <td class="py-2 px-3 whitespace-nowrap">{{ $url->updated_at?->format('Y-m-d H:i') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="py-10 text-center text-gray-500">No URLs match this search.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $urls->links('partials.pagination-centered') }}
    </div>

    @include('wiki._tag-autocomplete')
@endsection
