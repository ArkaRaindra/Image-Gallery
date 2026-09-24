@extends('layouts.app')

@section('title', 'Search Comments')

@section('content')
    <h1 class="text-2xl font-bold mb-4">Comments</h1>

    <form method="GET" action="{{ route('comments.search') }}" class="mb-6 max-w-md space-y-2 text-sm">
        <div class="flex items-center gap-2">
            <label class="w-24 font-semibold text-right">Commenter</label>
            <input type="text" name="commenter" value="{{ $filters['commenter'] ?? '' }}"
                class="flex-1 px-2 py-1 rounded bg-white border border-gray-700">
        </div>
        <div class="flex items-center gap-2">
            <label class="w-24 font-semibold text-right">Text</label>
            <input type="text" name="text" value="{{ $filters['text'] ?? '' }}"
                class="flex-1 px-2 py-1 rounded bg-white border border-gray-700">
        </div>
        <div class="flex items-center gap-2">
            <label class="w-24 font-semibold text-right">Tags</label>
            <div class="flex-1 relative" data-tag-autocomplete-wrapper>
                <input type="text" name="tags" value="{{ $filters['tags'] ?? '' }}" autocomplete="off"
                    data-tag-autocomplete class="w-full px-2 py-1 rounded bg-white border border-gray-700">
            </div>
        </div>
        <div class="flex items-center gap-2">
            <label class="w-24 font-semibold text-right">Score</label>
            <input type="text" name="score" value="{{ $filters['score'] ?? '' }}" placeholder="e.g. 5"
                class="flex-1 px-2 py-1 rounded bg-white border border-gray-700">
        </div>
        <div class="flex items-center gap-2">
            <label class="w-24 font-semibold text-right">Order</label>
            <select name="order" class="flex-1 px-2 py-1 rounded bg-white border border-gray-700">
                <option value="newest" @selected(($filters['order'] ?? '') === '' || ($filters['order'] ?? '') === 'newest')>Newest</option>
                <option value="oldest" @selected(($filters['order'] ?? '') === 'oldest')>Oldest</option>
                <option value="updated" @selected(($filters['order'] ?? '') === 'updated')>Updated</option>
                <option value="score_desc" @selected(($filters['order'] ?? '') === 'score_desc')>Score (highest)</option>
                <option value="score_asc" @selected(($filters['order'] ?? '') === 'score_asc')>Score (lowest)</option>
            </select>
        </div>
        <div class="flex items-center gap-2">
            <span class="w-24"></span>
            <button type="submit"
                class="px-4 py-1.5 rounded bg-green-700 hover:bg-green-800 text-white cursor-pointer">Search</button>
        </div>
    </form>

    @include('comments._search-list')
@endsection