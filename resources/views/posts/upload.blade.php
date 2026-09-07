@extends('layouts.app')

@section('title', 'Upload')

@section('content')
    <div class="max-w-xl">
        <h1 class="text-lg font-semibold mb-4">Upload a Post</h1>

        @if ($errors->any())
            <div class="mb-4 text-sm text-red-700 bg-red-50 border border-red-200 rounded p-2">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @unless (auth()->user()->isAdmin())
            <p class="text-sm text-amber-800 bg-amber-50 border border-amber-200 rounded p-2 mb-4">
                Your upload will be reviewed by an admin before it appears publicly.
            </p>
        @endunless

        <form method="POST" action="{{ route('upload.store') }}" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-semibold mb-1">File</label>
                <input type="file" name="file" accept="image/*" required class="text-sm">
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">Rating</label>
                <select name="rating" required class="w-full px-2 py-1.5 rounded bg-white border border-gray-700 text-sm">
                    <option value="general">General</option>
                    <option value="sensitive">Sensitive</option>
                    <option value="questionable">Questionable</option>
                    <option value="explicit">Explicit</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">Tags (space separated)</label>
                <input type="text" name="tags" value="{{ old('tags') }}" required
                    placeholder="1girl blue_eyes original"
                    class="w-full px-2 py-1.5 rounded bg-white border border-gray-700 text-sm">
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">Source (optional)</label>
                <input type="url" name="source" value="{{ old('source') }}"
                    class="w-full px-2 py-1.5 rounded bg-white border border-gray-700 text-sm">
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">Description (optional)</label>
                <textarea name="description" rows="3"
                    class="w-full px-2 py-1.5 rounded bg-white border border-gray-700 text-sm">{{ old('description') }}</textarea>
            </div>
            <button type="submit"
                class="px-4 py-2 rounded bg-green-700 hover:bg-green-800 text-white text-sm cursor-pointer">
                Upload
            </button>
        </form>
    </div>
@endsection