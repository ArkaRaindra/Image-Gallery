@extends('layouts.app')

@section('title', 'Notes')

@section('content')
    <h1 class="text-2xl font-bold mb-4">Notes</h1>

    <form method="GET" action="{{ route('notes.index') }}" class="mb-6 max-w-md space-y-2 text-sm">
        <div class="flex items-center gap-2">
            <label class="w-16 font-semibold text-right">Note</label>
            <input type="text" name="note" value="{{ $filters['note'] ?? '' }}"
                class="flex-1 px-2 py-1 rounded bg-white border border-gray-700">
        </div>
        <div class="flex items-center gap-2">
            <label class="w-16 font-semibold text-right">Tags</label>
            <div class="flex-1 relative" data-tag-autocomplete-wrapper>
                <input type="text" name="tags" value="{{ $filters['tags'] ?? '' }}" autocomplete="off"
                    data-tag-autocomplete class="w-full px-2 py-1 rounded bg-white border border-gray-700">
            </div>
        </div>
        <div class="flex items-center gap-2">
            <span class="w-16"></span>
            <button type="submit"
                class="px-4 py-1.5 rounded bg-green-700 hover:bg-green-800 text-white cursor-pointer">Search</button>
        </div>
    </form>

    <div class="overflow-x-auto bg-white border border-gray-800 rounded">
        <table class="w-full text-sm border-collapse">
            <thead>
                <tr class="text-left border-b border-gray-800 bg-gray-100">
                    <th class="py-2 px-3 font-semibold">Post</th>
                    <th class="py-2 px-3 font-semibold">Note</th>
                    <th class="py-2 px-3 font-semibold">Text</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($notes as $note)
                    <tr class="border-b border-gray-200 align-top">
                        <td class="py-2 px-3 whitespace-nowrap">
                            <a href="{{ route('posts.show', $note->post) }}"
                                class="text-sky-700 hover:underline">{{ $note->post_id }}</a>
                        </td>
                        <td class="py-2 px-3 whitespace-nowrap">
                            <a href="{{ route('posts.show', $note->post) }}"
                                class="text-sky-700 hover:underline">{{ $note->id }}.{{ $note->version }}</a>
                        </td>
                        <td class="py-2 px-3 text-gray-800">{{ \Illuminate\Support\Str::limit($note->body, 200) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="py-10 text-center text-gray-500">No notes found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $notes->links('partials.pagination-centered') }}
    </div>
@endsection