@extends('layouts.app')

@section('title', 'Wiki Changes')

@section('content')
    <div class="flex flex-col md:flex-row md:items-start gap-6">
        @include('partials.wiki-sidebar')

        <main class="flex-1 min-w-0">
            <h1 class="text-2xl font-bold mb-3">
                {{ $filterTag ? 'Wiki Page History' : 'Wiki Changes' }}
            </h1>

            @if ($filterTag)
                <p class="text-sm text-gray-700 mb-4">
                    Changes of
                    @if ($filterTag->hasWiki())
                        <a href="{{ route('wiki.show', $filterTag->name) }}"
                            class="text-sky-700 hover:underline">{{ $filterTag->displayName() }}</a>
                    @else
                        {{ $filterTag->displayName() }}
                    @endif
                    &middot; <a href="{{ route('wiki.versions') }}" class="text-sky-700 hover:underline">Show all</a>
                </p>
            @elseif ($updaterName !== '')
                <p class="text-sm text-gray-700 mb-4">
                    Changes by <span class="font-semibold">{{ $updaterName }}</span>
                    &middot; <a href="{{ route('wiki.versions') }}" class="text-sky-700 hover:underline">Show all</a>
                </p>
            @endif

            <div class="overflow-x-auto bg-white border border-gray-800 rounded">
                <table class="w-full text-sm border-collapse">
                    <thead>
                        <tr class="text-left border-b border-gray-800 bg-gray-100">
                            <th class="py-2 px-3 w-12"></th>
                            <th class="py-2 px-3 font-semibold">Title</th>
                            <th class="py-2 px-3 font-semibold">Changes</th>
                            <th class="py-2 px-3 font-semibold">Updated</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($versions as $version)
                            <tr class="border-b border-gray-200 align-top">
                                <td class="py-2 px-3 whitespace-nowrap">
                                    @if ($version->is_new)
                                        <span class="text-gray-400 cursor-not-allowed"
                                            title="New page, nothing to compare with">diff</span>
                                    @else
                                        <a href="{{ route('wiki.diff', $version) }}"
                                            class="text-sky-700 hover:underline">diff</a>
                                    @endif
                                </td>
                                <td class="py-2 px-3 break-all">
                                    @if ($version->tag->hasWiki())
                                        <a href="{{ route('wiki.show', $version->tag->name) }}"
                                            class="{{ $version->tag->categoryTextClass() }} hover:underline">{{ $version->tag->name }} »</a>
                                    @else
                                        {{ $version->tag->name }}
                                    @endif
                                </td>
                                <td class="py-2 px-3 whitespace-nowrap">
                                    @if ($version->is_new)
                                        <span class="text-green-700 font-medium">New</span>
                                    @else
                                        {{ $version->change_summary }}
                                    @endif
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
                                <td colspan="4" class="py-10 text-center text-gray-500">No wiki changes yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-6">
                {{ $versions->links('partials.pagination-centered') }}
            </div>
        </main>
    </div>
@endsection