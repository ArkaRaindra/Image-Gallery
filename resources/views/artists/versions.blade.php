@extends('layouts.app')

@section('title', 'Artist History')

@section('content')
    @php
        $typeClass = [
            'same' => 'text-gray-800',
            'add' => 'text-green-700',
            'del' => 'text-red-600 line-through',
        ];
    @endphp

    <h1 class="text-2xl font-bold mb-1">Artist History</h1>
    <p class="text-sm mb-4">
        <span class="font-semibold">Version comparison:</span>
        <a href="{{ request()->fullUrlWithQuery(['compare' => 'previous', 'page' => null]) }}"
            class="{{ $compare === 'previous' ? 'font-bold text-gray-900' : 'text-sky-700 hover:underline' }}">previous</a>
        |
        <a href="{{ request()->fullUrlWithQuery(['compare' => 'current', 'page' => null]) }}"
            class="{{ $compare === 'current' ? 'font-bold text-gray-900' : 'text-sky-700 hover:underline' }}">current</a>
    </p>

    <form method="GET" action="{{ route('artists.versions') }}" class="mb-4 grid gap-2 max-w-xl text-sm">
        <input type="hidden" name="compare" value="{{ $compare }}">
        <label class="grid grid-cols-[7rem_1fr] items-center gap-2">
            <span class="font-semibold text-right">Updater</span>
            <input type="text" name="updater" value="{{ $filters['updater'] }}" autocomplete="off"
                class="px-2 py-1 rounded bg-white border border-gray-700 focus:outline-none focus:border-sky-500">
        </label>
        <label class="grid grid-cols-[7rem_1fr] items-center gap-2">
            <span class="font-semibold text-right">Artist</span>
            <input type="text" name="artist" value="{{ $filters['artist'] }}" autocomplete="off"
                class="px-2 py-1 rounded bg-white border border-gray-700 focus:outline-none focus:border-sky-500">
        </label>
        <label class="grid grid-cols-[7rem_1fr] items-center gap-2">
            <span class="font-semibold text-right">Order</span>
            <select name="order"
                class="w-32 px-2 py-1 rounded bg-white border border-gray-700 focus:outline-none focus:border-sky-500">
                <option value="id" @selected($filters['order'] === 'id')>ID</option>
                <option value="name" @selected($filters['order'] === 'name')>Name</option>
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
                    <th class="py-2 px-3 font-semibold">URLs</th>
                    <th class="py-2 px-3 font-semibold">Changes</th>
                    <th class="py-2 px-3 font-semibold whitespace-nowrap">Updated</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($versions as $version)
                    @php
                        $diff = $diffs[$version->id];
                        $artistTag = $version->artist->tag;
                    @endphp
                    <tr class="border-b border-gray-200 align-top">
                        <td class="py-2 px-3 break-all">
                            <a href="{{ route('wiki.show', $artistTag->name) }}"
                                class="{{ $artistTag->categoryTextClass() }} hover:underline">{{ $version->name }} »</a>
                            @if ($diff['name']['old'] !== null)
                                <div class="text-xs mt-1">
                                    <span class="font-semibold">Rename:</span>
                                    <span class="text-red-600 line-through">{{ $diff['name']['old'] }}</span>
                                    →
                                    <span class="text-green-700">{{ $diff['name']['new'] }}</span>
                                </div>
                            @endif
                            @if ($diff['group']['new'] !== null || $diff['group']['changed'])
                                <div class="text-xs mt-1">
                                    <span class="font-semibold">Group:</span>
                                    @if ($diff['group']['changed'] && $diff['group']['old'] !== null)
                                        <span class="text-red-600 line-through">{{ $diff['group']['old'] }}</span>
                                        →
                                    @endif
                                    @if ($diff['group']['new'] !== null)
                                        <span class="{{ $diff['group']['changed'] ? 'text-green-700' : 'text-gray-800' }}">{{ $diff['group']['new'] }}</span>
                                    @endif
                                </div>
                            @endif
                        </td>
                        <td class="py-2 px-3 break-all">
                            @foreach ($diff['other_names'] as $entry)
                                <div class="{{ $typeClass[$entry['type']] }}">{{ $entry['text'] }}</div>
                            @endforeach
                        </td>
                        <td class="py-2 px-3 break-all">
                            @foreach ($diff['urls'] as $entry)
                                @if ($entry['type'] === 'status')
                                    <div>
                                        <span class="text-red-600 line-through">{{ $entry['old'] }}</span>
                                        →
                                        <span class="text-green-700">{{ $entry['text'] }}</span>
                                    </div>
                                @else
                                    <div class="{{ $typeClass[$entry['type']] }}">{{ $entry['text'] }}</div>
                                @endif
                            @endforeach
                        </td>
                        <td class="py-2 px-3 whitespace-nowrap">
                            @foreach ($version->changeLabels() as $label)
                                <div class="{{ $label === 'New' ? 'text-green-700 font-medium' : '' }}">{{ $label }}</div>
                            @endforeach
                        </td>
                        <td class="py-2 px-3 whitespace-nowrap">
                            @if ($version->updater)
                                <a href="{{ route('users.show', $version->updater) }}" data-user-card
                                    data-user-card-url="{{ route('users.card', $version->updater) }}"
                                    class="text-sky-700 hover:underline">{{ $version->updater->name }} »</a>
                            @else
                                <span class="text-gray-500">System</span>
                            @endif
                            <div class="text-xs text-gray-500">{{ $version->created_at->format('Y-m-d H:i') }}</div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="py-10 text-center text-gray-500">No artist changes yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $versions->links('partials.pagination-centered') }}
    </div>
@endsection
