@extends('layouts.app')

@section('title', 'Artists')

@section('content')
    <h1 class="text-2xl font-bold mb-3">Artists</h1>

    <form method="GET" action="{{ route('artists.index') }}" class="mb-4 grid gap-2 max-w-xl text-sm">
        <label class="grid grid-cols-[7rem_1fr] items-center gap-2">
            <span class="font-semibold text-right">Name</span>
            <div class="relative" data-wiki-tag-wrapper>
                <input type="text" name="name" value="{{ $filters['name'] }}" autocomplete="off"
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
            <span class="font-semibold text-right">Deleted?</span>
            <select name="deleted"
                class="w-24 px-2 py-1 rounded bg-white border border-gray-700 focus:outline-none focus:border-sky-500">
                <option value=""></option>
                <option value="yes" @selected($filters['deleted'] === 'yes')>Yes</option>
                <option value="no" @selected($filters['deleted'] === 'no')>No</option>
            </select>
        </label>
        <label class="grid grid-cols-[7rem_1fr] items-center gap-2">
            <span class="font-semibold text-right">Has tag?</span>
            <select name="has_tag"
                class="w-24 px-2 py-1 rounded bg-white border border-gray-700 focus:outline-none focus:border-sky-500">
                <option value=""></option>
                <option value="yes" @selected($filters['has_tag'] === 'yes')>Yes</option>
                <option value="no" @selected($filters['has_tag'] === 'no')>No</option>
            </select>
        </label>
        <label class="grid grid-cols-[7rem_1fr] items-center gap-2">
            <span class="font-semibold text-right">Order</span>
            <select name="order"
                class="w-48 px-2 py-1 rounded bg-white border border-gray-700 focus:outline-none focus:border-sky-500">
                <option value="created" @selected($filters['order'] === 'created')>Recently created</option>
                <option value="updated" @selected($filters['order'] === 'updated')>Recently updated</option>
                <option value="name" @selected($filters['order'] === 'name')>Name</option>
                <option value="post_count" @selected($filters['order'] === 'post_count')>Post count</option>
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
                    <th class="py-2 px-3 font-semibold">Name</th>
                    <th class="py-2 px-3 font-semibold">Other Names</th>
                    <th class="py-2 px-3 font-semibold">Status</th>
                    <th class="py-2 px-3 font-semibold whitespace-nowrap">Updated</th>
                    <th class="py-2 px-3 w-16"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($artists as $artist)
                    <tr class="border-b border-gray-200 align-top">
                        <td class="py-2 px-3 break-all">
                            <a href="{{ route('wiki.show', $artist->tag->name) }}"
                                class="{{ $artist->tag->categoryTextClass() }} hover:underline">{{ $artist->tag->name }}</a>
                            <span class="text-xs text-gray-500">{{ $artist->tag->post_count }}</span>
                        </td>
                        <td class="py-2 px-3">
                            @foreach ($artist->otherNameList() as $otherName)
                                <a href="{{ route('artists.index', ['name' => $otherName]) }}"
                                    class="inline-block px-1.5 py-0.5 mr-1 mb-0.5 rounded border border-gray-400 bg-gray-100 text-xs text-sky-700 hover:underline break-all">{{ $otherName }}</a>
                            @endforeach
                        </td>
                        <td class="py-2 px-3 whitespace-nowrap">
                            @if ($artist->is_deleted)
                                <a href="{{ route('artists.index', ['deleted' => 'yes']) }}"
                                    class="text-red-700 hover:underline">Deleted</a>
                            @endif
                        </td>
                        <td class="py-2 px-3 whitespace-nowrap" title="{{ $artist->updated_at?->format('Y-m-d H:i') }}">
                            {{ $artist->updated_at?->diffForHumans() }}
                        </td>
                        <td class="py-2 px-3 whitespace-nowrap">
                            <a href="{{ route('artists.edit', $artist) }}" class="text-sky-700 hover:underline">Edit</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="py-10 text-center text-gray-500">No artists match this search.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $artists->links('partials.pagination-centered') }}
    </div>

    @include('wiki._tag-autocomplete')
@endsection