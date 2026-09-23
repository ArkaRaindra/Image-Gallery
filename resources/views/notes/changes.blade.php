@extends('layouts.app')

@section('title', 'Note Changes')

@section('content')
    <h1 class="text-2xl font-bold mb-4">Note Changes</h1>

    <div class="overflow-x-auto bg-white border border-gray-800 rounded">
        <table class="w-full text-sm border-collapse">
            <thead>
                <tr class="text-left border-b border-gray-800 bg-gray-100">
                    <th class="py-2 px-3 font-semibold">Post</th>
                    <th class="py-2 px-3 font-semibold">Note</th>
                    <th class="py-2 px-3 font-semibold">Body</th>
                    <th class="py-2 px-3 font-semibold">Position (X,Y)</th>
                    <th class="py-2 px-3 font-semibold">Size (WxH)</th>
                    <th class="py-2 px-3 font-semibold">Changes</th>
                    <th class="py-2 px-3 font-semibold">Updated</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($versions as $version)
                    @php
                        $note = $version->note;
                        $post = $version->post;
                        $postWidth = $post?->width ?: 1;
                        $postHeight = $post?->height ?: 1;
                        $px = round($version->x / 100 * $postWidth);
                        $py = round($version->y / 100 * $postHeight);
                        $pw = round($version->width / 100 * $postWidth);
                        $ph = round($version->height / 100 * $postHeight);
                    @endphp
                    <tr class="border-b border-gray-200 align-top">
                        <td class="py-2 px-3 whitespace-nowrap">
                            @if ($post)
                                <a href="{{ route('posts.show', $post) }}"
                                    class="text-sky-700 hover:underline">{{ $version->post_id }} »</a>
                            @else
                                {{ $version->post_id }}
                            @endif
                        </td>
                        <td class="py-2 px-3 whitespace-nowrap">
                            @if ($note)
                                <a href="{{ route('notes.history', $note) }}"
                                    class="text-sky-700 hover:underline">{{ $version->note_id }}.{{ $version->version }}</a>
                            @else
                                {{ $version->note_id }}.{{ $version->version }}
                            @endif
                        </td>
                        <td class="py-2 px-3 text-gray-800 max-w-xs">
                            {{ \Illuminate\Support\Str::limit(strip_tags($version->body), 80) ?: '(empty)' }}
                        </td>
                        <td class="py-2 px-3 whitespace-nowrap">{{ $px }},{{ $py }}</td>
                        <td class="py-2 px-3 whitespace-nowrap">{{ $pw }}x{{ $ph }}</td>
                        <td class="py-2 px-3 whitespace-nowrap">
                            @if ($version->is_new)
                                <span class="text-green-700 font-medium">New</span>
                            @endif
                        </td>
                        <td class="py-2 px-3 whitespace-nowrap">
                            @if ($version->updater)
                                <a href="{{ route('users.show', $version->updater) }}"
                                    class="text-sky-700 hover:underline">{{ $version->updater->name }} »</a>
                            @else
                                <span class="text-gray-500">Anonymous</span>
                            @endif
                            <div class="text-xs text-gray-500">{{ $version->created_at->format('Y-m-d H:i') }}</div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="py-10 text-center text-gray-500">No note changes yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $versions->links('partials.pagination-centered') }}
    </div>
@endsection