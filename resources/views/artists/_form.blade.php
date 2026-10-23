<form method="POST" action="{{ $action }}" class="space-y-4 max-w-4xl">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div>
        <label for="artist-name" class="block text-sm font-semibold mb-1">Name</label>
        <input type="text" id="artist-name" name="name" required maxlength="255" autocomplete="off"
            value="{{ old('name', $artist?->tag->name) }}"
            class="w-full md:w-96 px-2 py-1.5 rounded bg-white border border-gray-700 text-sm focus:outline-none focus:border-sky-500">
        @error('name')
            <p class="text-xs text-red-700 mt-1">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="artist-other-names" class="block text-sm font-semibold mb-1">Other names</label>
        <div class="flex flex-wrap items-center gap-x-3 gap-y-1">
            <input type="text" id="artist-other-names" name="other_names" maxlength="255" autocomplete="off"
                value="{{ old('other_names', $artist?->other_names) }}"
                class="w-full md:w-96 px-2 py-1.5 rounded bg-white border border-gray-700 text-sm focus:outline-none focus:border-sky-500">
            <span class="text-xs italic text-gray-700">Separate names with spaces, not commas. Use underscores for spaces inside names.</span>
        </div>
        @error('other_names')
            <p class="text-xs text-red-700 mt-1">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="artist-group-name" class="block text-sm font-semibold mb-1">Group name</label>
        <input type="text" id="artist-group-name" name="group_name" maxlength="255" autocomplete="off"
            value="{{ old('group_name', $artist?->group_name) }}"
            class="w-full md:w-96 px-2 py-1.5 rounded bg-white border border-gray-700 text-sm focus:outline-none focus:border-sky-500">
        @error('group_name')
            <p class="text-xs text-red-700 mt-1">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="artist-urls" class="block text-sm font-semibold mb-1">URLs</label>
        <textarea id="artist-urls" name="urls" rows="10" spellcheck="false"
            class="w-full px-2 py-1.5 rounded bg-white border border-gray-700 text-sm font-mono focus:outline-none focus:border-sky-500">{{ old('urls', $artist?->urlsText()) }}</textarea>
        <p class="text-xs italic text-gray-700 mt-1">One URL per line. You can prefix a URL with - to mark it as dead.</p>
        @error('urls')
            <p class="text-xs text-red-700 mt-1">{{ $message }}</p>
        @enderror
    </div>

    @if ($artist)
        <div>
            <input type="hidden" name="is_deleted" value="0">
            <label class="inline-flex items-center gap-2 text-sm">
                <input type="checkbox" name="is_deleted" value="1" @checked(old('is_deleted', $artist->is_deleted))>
                <span class="font-semibold">Deleted</span>
            </label>
        </div>
    @endif

    <div>
        <button type="submit"
            class="px-4 py-1.5 rounded bg-green-700 hover:bg-green-800 text-white text-sm cursor-pointer">Submit</button>
    </div>
</form>
