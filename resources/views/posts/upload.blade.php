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
                <input type="file" name="file" accept="image/*,video/mp4" required class="text-sm">
                <p class="text-xs text-gray-600 mt-1">Images or MP4 video, up to 100MB.</p>
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
                <input type="text" id="tags-input" name="tags" value="{{ old('tags') }}" required
                    placeholder="1girl blue_eyes original"
                    class="w-full px-2 py-1.5 rounded bg-white border border-gray-700 text-sm">
                <input type="hidden" id="tag-categories-input" name="tag_categories" value="{{ old('tag_categories') }}">

                <div id="tag-categories" class="hidden mt-2 border border-gray-300 rounded bg-white p-2">
                    <p class="text-xs text-gray-600 mb-2">
                        Choose a category for each new tag. Tags that already exist keep their category.
                    </p>
                    <ul id="tag-categories-list" class="space-y-1"></ul>
                </div>
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">Parent post ID (optional)</label>
                <input type="number" name="parent_id" value="{{ old('parent_id') }}" min="1"
                    placeholder="e.g. 123"
                    class="w-full px-2 py-1.5 rounded bg-white border border-gray-700 text-sm">
                <p class="text-xs text-gray-600 mt-1">Use this when the upload is a variant of another post.</p>
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

    <script>
        (function () {
            const input = document.getElementById('tags-input');
            const hidden = document.getElementById('tag-categories-input');
            const box = document.getElementById('tag-categories');
            const list = document.getElementById('tag-categories-list');

            const categories = @json($categories);
            const lookupUrl = @json(route('tags.lookup'));
            const colors = {
                artist: 'text-red-700',
                copyright: 'text-purple-700',
                character: 'text-green-700',
                general: 'text-sky-700',
                meta: 'text-amber-700',
            };

            // Prototype-less maps, so a tag named e.g. "constructor" is just a normal key.
            let chosen = Object.create(null);   // tag name -> category picked for it
            let existing = Object.create(null); // tag name -> category, for tags that already exist
            let timer = null;
            let requestId = 0;

            try {
                Object.assign(chosen, JSON.parse(hidden.value || '{}') || {});
            } catch (e) {
                chosen = Object.create(null);
            }

            const titleCase = (text) => text.charAt(0).toUpperCase() + text.slice(1);
            const normalize = (tag) => tag.toLowerCase().replace(/^_+|_+$/g, '');
            const typedTags = () => [...new Set(input.value.split(/\s+/).map(normalize).filter(Boolean))];

            // Only new tags are sent: an existing tag keeps the category it already has.
            function syncHidden() {
                const out = {};

                typedTags().forEach((name) => {
                    if (!(name in existing)) {
                        out[name] = chosen[name] || 'general';
                    }
                });

                hidden.value = JSON.stringify(out);
            }

            function render() {
                const tags = typedTags();

                list.innerHTML = '';
                box.classList.toggle('hidden', tags.length === 0);

                tags.forEach((name) => {
                    const isExisting = name in existing;
                    const category = isExisting ? existing[name] : (chosen[name] || 'general');

                    const row = document.createElement('li');
                    row.className = 'flex items-center gap-2 text-sm';

                    const label = document.createElement('span');
                    label.className = 'flex-1 truncate ' + (colors[category] || colors.general);
                    label.textContent = name;
                    row.appendChild(label);

                    if (isExisting) {
                        const note = document.createElement('span');
                        note.className = 'text-xs text-gray-600';
                        note.textContent = titleCase(category) + ' (existing)';
                        row.appendChild(note);
                    } else {
                        const select = document.createElement('select');
                        select.className = 'px-2 py-1 rounded bg-white border border-gray-700 text-sm';
                        select.setAttribute('aria-label', 'Category for ' + name);

                        categories.forEach((option) => {
                            const el = document.createElement('option');
                            el.value = option;
                            el.textContent = titleCase(option);
                            select.appendChild(el);
                        });

                        select.value = category;
                        select.addEventListener('change', () => {
                            chosen[name] = select.value;
                            label.className = 'flex-1 truncate ' + (colors[select.value] || colors.general);
                            syncHidden();
                        });

                        row.appendChild(select);
                    }

                    list.appendChild(row);
                });

                syncHidden();
            }

            // Find out which of the typed tags already exist.
            function lookup() {
                const tags = typedTags();

                if (tags.length === 0) {
                    existing = Object.create(null);
                    render();
                    return;
                }

                const params = new URLSearchParams();
                tags.forEach((name) => params.append('names[]', name));

                const id = ++requestId;

                fetch(lookupUrl + '?' + params.toString(), { headers: { Accept: 'application/json' } })
                    .then((response) => (response.ok ? response.json() : {}))
                    .then((data) => {
                        if (id !== requestId) {
                            return;
                        }

                        existing = Object.assign(Object.create(null), data || {});
                        render();
                    })
                    .catch(() => {});
            }

            input.addEventListener('input', () => {
                render();
                clearTimeout(timer);
                timer = setTimeout(lookup, 300);
            });

            render();
            lookup();
        })();
    </script>
@endsection