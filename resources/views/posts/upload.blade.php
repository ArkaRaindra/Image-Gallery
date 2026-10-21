@extends('layouts.app')

@section('title', 'Upload')

@section('content')
    <form id="upload-form" method="POST" action="{{ route('upload.store') }}" enctype="multipart/form-data">
        @csrf
        <input type="file" id="file-input" name="file" accept="image/*,video/mp4" class="hidden">

        {{-- Step 1: pick a file --}}
        <div id="step-select" class="max-w-xl mx-auto text-center">
            <h1 class="text-2xl font-bold mb-4">Upload</h1>

            @unless (auth()->user()->isAdmin())
                <p class="text-sm text-amber-800 bg-amber-50 border border-amber-200 rounded p-2 mb-4">
                    Your upload will be reviewed by an admin before it appears publicly.
                </p>
            @endunless

            <div id="select-error" class="hidden mb-4 text-sm text-red-700 bg-red-50 border border-red-200 rounded p-2"></div>

            <label id="drop-zone" for="file-input"
                class="block rounded-lg border-2 border-dashed border-green-700 bg-white/60 hover:bg-white/80 px-6 py-10 cursor-pointer transition-colors">
                <span class="block text-sm">Choose file or drag image here</span>
                <span class="block text-xs italic text-gray-600 mt-1">Max size: 100 MB.</span>
            </label>
        </div>

        {{-- Step 2: preview on the left, tags and source on the right --}}
        <div id="step-edit" class="hidden">
          <div class="flex flex-col md:flex-row md:items-start gap-6">
            <div class="flex-1 min-w-0">
                <div class="flex justify-center">
                    <img id="preview-image" alt="Preview" class="hidden max-w-full max-h-[75vh] object-contain">
                    <video id="preview-video" controls class="hidden max-w-full max-h-[75vh]"></video>
                </div>
                <div class="mt-2 flex flex-wrap items-center justify-center gap-2 text-xs">
                    <span id="file-info" class="text-green-900"></span>
                    <span id="source-badge" class="px-1.5 py-0.5 rounded bg-red-500 text-white font-semibold">No Source</span>
                    <button type="button" id="change-file" class="underline text-gray-700 cursor-pointer">Change file</button>
                </div>
            </div>

            <div class="md:w-[28rem] md:shrink-0 text-sm">
                <div class="flex items-center gap-4 border-b border-green-700 mb-3">
                    <button type="button" data-tab="tags"
                        class="pb-1 -mb-px border-b-2 border-green-800 font-semibold cursor-pointer">Tags</button>
                    <button type="button" data-tab="source"
                        class="pb-1 -mb-px border-b-2 border-transparent text-gray-700 cursor-pointer">Source</button>
                </div>

                <div id="form-errors" class="hidden mb-3 text-sm text-red-700 bg-red-50 border border-red-200 rounded p-2"></div>

                {{-- Tags tab --}}
                <div data-panel="tags" class="space-y-4">
                    <div class="flex items-center gap-3">
                        <span class="font-semibold">Rating</span>
                        <div class="inline-flex rounded border border-gray-700 overflow-hidden text-xs bg-white">
                            @foreach (['explicit' => 'Explicit', 'questionable' => 'Questionable', 'sensitive' => 'Sensitive', 'general' => 'General'] as $value => $label)
                                <label class="cursor-pointer border-l border-gray-700 first:border-l-0">
                                    <input type="radio" name="rating" value="{{ $value }}" class="sr-only peer">
                                    <span class="block px-2 py-1 peer-checked:bg-green-700 peer-checked:text-white peer-focus-visible:ring-2 peer-focus-visible:ring-sky-500">{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <div class="flex items-baseline justify-between mb-1">
                            <label for="tags-input" class="block font-semibold">Tags (space separated)</label>
                            <span id="tag-count" class="text-xs text-gray-700">0 tags</span>
                        </div>
                        <textarea id="tags-input" name="tags" rows="7" spellcheck="false" autocomplete="off"
                            placeholder="1girl blue_eyes original"
                            class="w-full px-2 py-1.5 rounded bg-white border border-gray-700 text-sm focus:outline-none focus:border-sky-500"></textarea>
                        <p class="text-xs italic text-gray-600 mt-1">Ctrl+Enter to submit</p>
                        <input type="hidden" id="tag-categories-input" name="tag_categories" value="">

                        <div id="tag-categories" class="hidden mt-2 border border-gray-300 rounded bg-white p-2">
                            <p class="text-xs text-gray-600 mb-2">
                                Choose a category for each new tag. Tags that already exist keep their category.
                            </p>
                            <ul id="tag-categories-list" class="space-y-1 max-h-64 overflow-y-auto pr-1"></ul>
                        </div>
                    </div>

                    <div>
                        <label for="parent-id" class="block font-semibold mb-1">Parent post ID (optional)</label>
                        <input type="number" id="parent-id" name="parent_id" min="1" placeholder="e.g. 123"
                            class="w-full px-2 py-1.5 rounded bg-white border border-gray-700 text-sm">
                        <p class="text-xs text-gray-600 mt-1">Use this when the upload is a variant of another post.</p>
                    </div>

                    <div class="border border-gray-300 rounded bg-white/70 p-2">
                        <div class="flex items-center justify-between gap-2 mb-1">
                            <span class="font-semibold">Autotagger</span>
                            <button type="button" id="autotag-run"
                                class="px-2 py-0.5 rounded bg-green-700 hover:bg-green-800 disabled:opacity-50 disabled:cursor-not-allowed text-white text-xs cursor-pointer">Run autotagger</button>
                        </div>
                        <p id="autotag-status" class="text-xs text-gray-700"></p>
                        <ul id="autotag-list" class="mt-1 space-y-0.5 max-h-72 overflow-y-auto pr-1"></ul>
                        <p id="autotag-hint" class="hidden text-xs text-gray-600 mt-1">Click a tag to add it to or remove it from the tags above.</p>
                    </div>
                </div>

                {{-- Source tab --}}
                <div data-panel="source" class="hidden space-y-4">
                    <div>
                        <label for="source-input" class="block font-semibold mb-1">Source</label>
                        <input type="text" id="source-input" name="source" maxlength="255"
                            class="w-full px-2 py-1.5 rounded bg-white border border-gray-700 text-sm focus:outline-none focus:border-sky-500">
                    </div>
                    <div>
                        <label for="original-title" class="block font-semibold mb-1">Original title</label>
                        <input type="text" id="original-title" name="original_title" maxlength="255"
                            class="w-full px-2 py-1.5 rounded bg-white border border-gray-700 text-sm focus:outline-none focus:border-sky-500">
                    </div>
                    <div>
                        <label for="original-description" class="block font-semibold mb-1">Original description</label>
                        <textarea id="original-description" name="description" rows="6" maxlength="5000"
                            class="w-full px-2 py-1.5 rounded bg-white border border-gray-700 text-sm focus:outline-none focus:border-sky-500"></textarea>
                    </div>
                    <div>
                        <label for="translated-title" class="block font-semibold mb-1">Translated title</label>
                        <input type="text" id="translated-title" name="translated_title" maxlength="255"
                            class="w-full px-2 py-1.5 rounded bg-white border border-gray-700 text-sm focus:outline-none focus:border-sky-500">
                    </div>
                    <div>
                        <label for="translated-description" class="block font-semibold mb-1">Translated description</label>
                        <textarea id="translated-description" name="translated_description" rows="6" maxlength="5000"
                            class="w-full px-2 py-1.5 rounded bg-white border border-gray-700 text-sm focus:outline-none focus:border-sky-500"></textarea>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" id="post-button"
                        class="px-4 py-2 rounded bg-green-700 hover:bg-green-800 disabled:opacity-60 disabled:cursor-wait text-white text-sm cursor-pointer">
                        Post
                    </button>
                </div>
            </div>
          </div>
        </div>
    </form>

    <script>
        (function () {
            const MAX_SIZE = 100 * 1024 * 1024;
            const PREFILL_THRESHOLD = 0.35; // autotagger tags at or above this score are put in the tags box

            const categories = @json($categories);
            const lookupUrl = @json(route('tags.lookup'));
            const autotagUrl = @json(route('upload.autotag'));
            const postsUrl = @json(route('posts.index'));
            const csrf = document.querySelector('meta[name="csrf-token"]').content;
            const colors = {
                artist: 'text-red-700',
                copyright: 'text-purple-700',
                character: 'text-green-700',
                general: 'text-sky-700',
                meta: 'text-amber-700',
            };

            const form = document.getElementById('upload-form');
            const fileInput = document.getElementById('file-input');
            const dropZone = document.getElementById('drop-zone');
            const stepSelect = document.getElementById('step-select');
            const stepEdit = document.getElementById('step-edit');
            const selectError = document.getElementById('select-error');
            const formErrors = document.getElementById('form-errors');
            const previewImage = document.getElementById('preview-image');
            const previewVideo = document.getElementById('preview-video');
            const fileInfo = document.getElementById('file-info');
            const sourceBadge = document.getElementById('source-badge');
            const sourceInput = document.getElementById('source-input');
            const postButton = document.getElementById('post-button');

            const input = document.getElementById('tags-input');
            const hidden = document.getElementById('tag-categories-input');
            const box = document.getElementById('tag-categories');
            const list = document.getElementById('tag-categories-list');
            const tagCount = document.getElementById('tag-count');

            const autotagRun = document.getElementById('autotag-run');
            const autotagStatus = document.getElementById('autotag-status');
            const autotagList = document.getElementById('autotag-list');
            const autotagHint = document.getElementById('autotag-hint');

            // Prototype-less maps, so a tag named e.g. "constructor" is just a normal key.
            let chosen = Object.create(null);   // tag name -> category picked for it
            let existing = Object.create(null); // tag name -> category, for tags that already exist
            let lookupTimer = null;
            let lookupId = 0;
            let autotagId = 0;
            let suggestions = [];               // [{name, score}] from the autotagger
            let objectUrl = null;
            let submitting = false;
            let redirecting = false;

            const titleCase = (text) => text.charAt(0).toUpperCase() + text.slice(1);
            const normalize = (tag) => tag.toLowerCase().replace(/^_+|_+$/g, '');
            const typedTags = () => [...new Set(input.value.split(/\s+/).map(normalize).filter(Boolean))];
            const isVideo = (file) => file.type.startsWith('video/');

            function humanSize(bytes) {
                if (bytes >= 1048576) return (bytes / 1048576).toFixed(2) + ' MB';
                if (bytes >= 1024) return (bytes / 1024).toFixed(1) + ' KB';
                return bytes + ' B';
            }

            function showMessages(target, messages) {
                target.innerHTML = '';

                if (messages.length === 0) {
                    target.classList.add('hidden');
                    return;
                }

                const ul = document.createElement('ul');
                ul.className = 'list-disc list-inside';
                messages.forEach((message) => {
                    const li = document.createElement('li');
                    li.textContent = message;
                    ul.appendChild(li);
                });
                target.appendChild(ul);
                target.classList.remove('hidden');
            }

            // ---------- Step 1 / step 2 ----------

            function selectTab(name) {
                document.querySelectorAll('[data-tab]').forEach((tab) => {
                    const active = tab.dataset.tab === name;
                    tab.classList.toggle('border-green-800', active);
                    tab.classList.toggle('font-semibold', active);
                    tab.classList.toggle('border-transparent', !active);
                    tab.classList.toggle('text-gray-700', !active);
                });

                document.querySelectorAll('[data-panel]').forEach((panel) => {
                    panel.classList.toggle('hidden', panel.dataset.panel !== name);
                });
            }

            function updateSourceBadge() {
                const value = sourceInput.value.trim();
                sourceBadge.classList.toggle('hidden', !(value === '' || value.startsWith('file://')));
            }

            function resetEditor() {
                autotagId++; // forget any autotagger answer that is still on its way
                lookupId++;
                form.reset();
                fileInput.value = '';

                if (objectUrl) {
                    URL.revokeObjectURL(objectUrl);
                    objectUrl = null;
                }

                previewImage.removeAttribute('src');
                previewVideo.removeAttribute('src');
                previewImage.classList.add('hidden');
                previewVideo.classList.add('hidden');

                chosen = Object.create(null);
                existing = Object.create(null);
                suggestions = [];
                autotagList.innerHTML = '';
                autotagHint.classList.add('hidden');
                autotagStatus.textContent = '';
                showMessages(formErrors, []);
                onTagsChanged();
            }

            function showEditor(file) {
                objectUrl = URL.createObjectURL(file);
                const ext = (file.name.split('.').pop() || '').toLowerCase();
                const describe = (width, height) => {
                    fileInfo.textContent = humanSize(file.size) + ' .' + ext + (width ? ', ' + width + 'x' + height : '');
                };

                describe(0, 0);

                if (isVideo(file)) {
                    previewVideo.onloadedmetadata = () => describe(previewVideo.videoWidth, previewVideo.videoHeight);
                    previewVideo.src = objectUrl;
                    previewVideo.classList.remove('hidden');
                } else {
                    previewImage.onload = () => describe(previewImage.naturalWidth, previewImage.naturalHeight);
                    previewImage.src = objectUrl;
                    previewImage.classList.remove('hidden');
                }

                sourceInput.value = 'file://' + file.name;
                updateSourceBadge();

                stepSelect.classList.add('hidden');
                stepEdit.classList.remove('hidden');
                selectTab('tags');

                if (isVideo(file)) {
                    autotagRun.disabled = true;
                    autotagStatus.textContent = 'The autotagger only works on images.';
                } else {
                    autotagRun.disabled = false;
                    runAutotagger();
                }
            }

            function acceptFile(file) {
                showMessages(selectError, []);

                if (!file) return;

                if (!/^(image\/(jpeg|png|gif|webp)|video\/mp4)$/.test(file.type)) {
                    showMessages(selectError, ['Only JPG, PNG, GIF, WebP images and MP4 videos can be uploaded.']);
                    fileInput.value = '';
                    return;
                }

                if (file.size > MAX_SIZE) {
                    showMessages(selectError, ['That file is larger than 100 MB.']);
                    fileInput.value = '';
                    return;
                }

                resetEditor();

                // A dropped file is not in the form yet: put it in the file input so it is posted with the form.
                const transfer = new DataTransfer();
                transfer.items.add(file);
                fileInput.files = transfer.files;

                showEditor(file);
            }

            fileInput.addEventListener('change', () => acceptFile(fileInput.files[0]));

            ['dragenter', 'dragover'].forEach((type) => {
                dropZone.addEventListener(type, (event) => {
                    event.preventDefault();
                    dropZone.classList.add('bg-white');
                });
            });

            ['dragleave', 'drop'].forEach((type) => {
                dropZone.addEventListener(type, (event) => {
                    event.preventDefault();
                    dropZone.classList.remove('bg-white');
                });
            });

            dropZone.addEventListener('drop', (event) => acceptFile(event.dataTransfer.files[0]));

            document.getElementById('change-file').addEventListener('click', () => {
                resetEditor();
                stepEdit.classList.add('hidden');
                stepSelect.classList.remove('hidden');
            });

            document.querySelectorAll('[data-tab]').forEach((tab) => {
                tab.addEventListener('click', () => selectTab(tab.dataset.tab));
            });

            sourceInput.addEventListener('input', updateSourceBadge);

            // ---------- Tags and the category of each new tag ----------

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

            function renderCategories() {
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
                    renderCategories();
                    renderSuggestions();
                    return;
                }

                const params = new URLSearchParams();
                tags.forEach((name) => params.append('names[]', name));

                const id = ++lookupId;

                fetch(lookupUrl + '?' + params.toString(), { headers: { Accept: 'application/json' } })
                    .then((response) => (response.ok ? response.json() : {}))
                    .then((data) => {
                        if (id !== lookupId) return;

                        existing = Object.assign(Object.create(null), data || {});
                        renderCategories();
                        renderSuggestions();
                    })
                    .catch(() => {});
            }

            function onTagsChanged() {
                tagCount.textContent = typedTags().length + (typedTags().length === 1 ? ' tag' : ' tags');
                renderCategories();
                renderSuggestions();
                clearTimeout(lookupTimer);
                lookupTimer = setTimeout(lookup, 300);
            }

            input.addEventListener('input', onTagsChanged);

            input.addEventListener('keydown', (event) => {
                if ((event.ctrlKey || event.metaKey) && event.key === 'Enter') {
                    event.preventDefault();
                    form.requestSubmit();
                }
            });

            // ---------- Autotagger ----------

            function toggleTag(name) {
                const tags = typedTags();
                const next = tags.includes(name) ? tags.filter((tag) => tag !== name) : [...tags, name];

                input.value = next.join(' ');
                onTagsChanged();
            }

            function renderSuggestions() {
                const present = new Set(typedTags());

                autotagList.innerHTML = '';

                suggestions.forEach((item) => {
                    const category = existing[item.name] || 'general';
                    const isPresent = present.has(item.name);

                    const row = document.createElement('li');
                    row.className = 'flex items-center justify-between gap-2 text-sm';

                    const left = document.createElement('span');
                    left.className = 'truncate';

                    const help = document.createElement('a');
                    help.href = postsUrl + '?tags=' + encodeURIComponent(item.name) + '&wiki=1';
                    help.target = '_blank';
                    help.rel = 'noopener';
                    help.className = 'text-gray-600 hover:text-gray-900 mr-1';
                    help.textContent = '?';
                    left.appendChild(help);

                    const button = document.createElement('button');
                    button.type = 'button';
                    button.className = (colors[category] || colors.general) + ' hover:underline text-left cursor-pointer '
                        + (isPresent ? 'font-bold' : 'opacity-70');
                    button.textContent = item.name;
                    button.title = isPresent ? 'Remove from tags' : 'Add to tags';
                    button.addEventListener('click', () => toggleTag(item.name));
                    left.appendChild(button);

                    const percent = document.createElement('span');
                    percent.className = 'text-gray-600 shrink-0';
                    percent.textContent = Math.round(item.score * 100) + '%';

                    row.appendChild(left);
                    row.appendChild(percent);
                    autotagList.appendChild(row);
                });
            }

            function runAutotagger() {
                const file = fileInput.files[0];

                if (!file || isVideo(file)) return;

                const id = ++autotagId;
                const body = new FormData();
                body.append('file', file);

                autotagRun.disabled = true;
                autotagStatus.textContent = 'Analyzing image…';

                fetch(autotagUrl, {
                    method: 'POST',
                    body,
                    headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrf },
                })
                    .then(async (response) => {
                        const data = await response.json().catch(() => ({}));

                        if (!response.ok) {
                            throw new Error(data.message || 'The autotagger failed (HTTP ' + response.status + ').');
                        }

                        return data;
                    })
                    .then((data) => {
                        if (id !== autotagId) return;

                        suggestions = data.tags || [];

                        // Put the likely tags in the tags box. They stay editable.
                        const tags = typedTags();
                        suggestions.forEach((item) => {
                            if (item.score >= PREFILL_THRESHOLD && !tags.includes(item.name)) {
                                tags.push(item.name);
                            }
                        });
                        input.value = tags.join(' ');

                        if (data.rating && !form.querySelector('input[name="rating"]:checked')) {
                            const radio = form.querySelector('input[name="rating"][value="' + data.rating + '"]');
                            if (radio) radio.checked = true;
                        }

                        autotagStatus.textContent = suggestions.length === 0
                            ? 'The autotagger found no tags.'
                            : suggestions.length + ' tags predicted.';
                        autotagHint.classList.toggle('hidden', suggestions.length === 0);
                        onTagsChanged();
                    })
                    .catch((error) => {
                        if (id !== autotagId) return;

                        autotagStatus.textContent = error.message;
                    })
                    .finally(() => {
                        if (id === autotagId) autotagRun.disabled = false;
                    });
            }

            autotagRun.addEventListener('click', runAutotagger);

            // ---------- Posting ----------

            form.addEventListener('submit', (event) => {
                event.preventDefault();

                if (submitting) return;

                const errors = [];

                if (!form.querySelector('input[name="rating"]:checked')) errors.push('Pick a rating.');
                if (typedTags().length === 0) errors.push('Add at least one tag.');

                showMessages(formErrors, errors);

                if (errors.length > 0) {
                    selectTab('tags');
                    return;
                }

                submitting = true;
                postButton.disabled = true;
                postButton.textContent = 'Uploading…';

                fetch(form.action, {
                    method: 'POST',
                    body: new FormData(form),
                    headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrf },
                })
                    .then(async (response) => {
                        const data = await response.json().catch(() => ({}));

                        if (response.ok && data.redirect) {
                            redirecting = true;
                            window.location.href = data.redirect;
                            return;
                        }

                        const messages = data.errors
                            ? Object.values(data.errors).flat()
                            : [data.message || 'Upload failed (HTTP ' + response.status + ').'];

                        showMessages(formErrors, messages);
                    })
                    .catch(() => showMessages(formErrors, ['Upload failed. Check your connection and try again.']))
                    .finally(() => {
                        if (redirecting) return;

                        submitting = false;
                        postButton.disabled = false;
                        postButton.textContent = 'Post';
                    });
            });

            onTagsChanged();
        })();
    </script>
@endsection