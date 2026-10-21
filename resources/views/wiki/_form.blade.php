@php
    $bodyValue = old('body', $body ?? '');
@endphp

<form method="POST" action="{{ $action }}" id="wiki-form" class="space-y-4 max-w-4xl">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    @if ($wikiTag)
        <div>
            <span class="block text-sm font-semibold mb-1">Tag</span>
            <p class="text-lg font-bold {{ $wikiTag->categoryTextClass() }}">{{ $wikiTag->displayName() }}</p>
        </div>
    @else
        <div>
            <label for="wiki-tag" class="block text-sm font-semibold mb-1">Tag</label>
            <div class="relative w-full md:w-96" data-wiki-tag-wrapper>
                <input type="text" id="wiki-tag" name="tag" required maxlength="255" autocomplete="off"
                    data-wiki-tag-autocomplete
                    value="{{ old('tag', $prefillTag ?? '') }}" placeholder="Type to search tags"
                    class="w-full px-2 py-1.5 rounded bg-white border border-gray-700 text-sm focus:outline-none focus:border-sky-500">
            </div>
            <p class="text-xs text-gray-700 mt-1">Only an existing tag that does not have a wiki page yet can get a new page.</p>
            @error('tag')
                <p class="text-xs text-red-700 mt-1">
                    {{ $message }}
                    @if (!empty($existingWikiTag))
                        <a href="{{ route('wiki.show', $existingWikiTag->name) }}" class="text-sky-700 hover:underline">Open the page</a>
                    @endif
                </p>
            @enderror
        </div>
    @endif

    <div>
        <label for="wiki-other-names" class="block text-sm font-semibold mb-1">Other names</label>
        <input type="text" id="wiki-other-names" name="other_names" maxlength="255"
            value="{{ old('other_names', $wikiTag?->other_names) }}"
            class="w-full md:w-96 px-2 py-1.5 rounded bg-white border border-gray-700 text-sm focus:outline-none focus:border-sky-500">
        <p class="text-xs text-gray-700 mt-1">Aliases shown under the title, such as the name on other sites. Separate with spaces.</p>
        @error('other_names')
            <p class="text-xs text-red-700 mt-1">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="block text-sm font-semibold mb-1">Wiki content</label>
        <div class="border border-gray-700 rounded bg-white">
            <div id="wiki-toolbar"
                class="flex flex-wrap items-center gap-1 px-2 py-1 border-b border-gray-300 bg-gray-100 text-sm">
                <button type="button" data-cmd="bold" title="Bold"
                    class="px-2 py-0.5 rounded hover:bg-gray-200 font-bold cursor-pointer">B</button>
                <button type="button" data-cmd="italic" title="Italic"
                    class="px-2 py-0.5 rounded hover:bg-gray-200 italic cursor-pointer">I</button>
                <button type="button" data-cmd="underline" title="Underline"
                    class="px-2 py-0.5 rounded hover:bg-gray-200 underline cursor-pointer">U</button>
                <span class="w-px h-5 bg-gray-300 mx-1"></span>
                <button type="button" data-cmd="formatBlock" data-value="h2" title="Heading"
                    class="px-2 py-0.5 rounded hover:bg-gray-200 cursor-pointer">H2</button>
                <button type="button" data-cmd="formatBlock" data-value="h3" title="Sub heading"
                    class="px-2 py-0.5 rounded hover:bg-gray-200 cursor-pointer">H3</button>
                <button type="button" data-cmd="formatBlock" data-value="p" title="Paragraph"
                    class="px-2 py-0.5 rounded hover:bg-gray-200 cursor-pointer">P</button>
                <span class="w-px h-5 bg-gray-300 mx-1"></span>
                <button type="button" data-cmd="insertUnorderedList" title="Bullet list"
                    class="px-2 py-0.5 rounded hover:bg-gray-200 cursor-pointer">• List</button>
                <button type="button" data-cmd="insertOrderedList" title="Numbered list"
                    class="px-2 py-0.5 rounded hover:bg-gray-200 cursor-pointer">1. List</button>
                <span class="w-px h-5 bg-gray-300 mx-1"></span>
                <button type="button" data-action="link" title="Link"
                    class="px-2 py-0.5 rounded hover:bg-gray-200 cursor-pointer">Link</button>
                <button type="button" data-action="image" title="Upload an image"
                    class="px-2 py-0.5 rounded hover:bg-gray-200 cursor-pointer">Image</button>
                <button type="button" data-action="source" id="wiki-source-toggle" title="Edit the HTML source"
                    class="ml-auto px-2 py-0.5 rounded hover:bg-gray-200 font-mono text-xs cursor-pointer">HTML</button>
            </div>
            <div id="wiki-editor" contenteditable="true"
                class="wiki-content min-h-72 max-h-[40rem] overflow-auto p-3 text-sm text-gray-900 focus:outline-none"></div>
            <textarea id="wiki-source" name="body" rows="18"
                class="hidden w-full min-h-72 p-3 font-mono text-sm text-gray-900 focus:outline-none">{{ $bodyValue }}</textarea>
        </div>
        <input type="file" id="wiki-image-input" accept="image/*" class="hidden">
        <p class="text-xs text-gray-700 mt-1">Images are uploaded when you pick them. The HTML button shows the HTML source, where other HTML tags can be used. Unsafe HTML such as scripts is removed when the page is saved.</p>
        @error('body')
            <p class="text-xs text-red-700 mt-1">{{ $message }}</p>
        @enderror
    </div>

    <button type="submit"
        class="px-4 py-1.5 rounded bg-green-700 hover:bg-green-800 text-white text-sm cursor-pointer">Submit</button>
</form>

@include('wiki._content-styles')

@if (!$wikiTag)
    @include('wiki._tag-autocomplete')
@endif

<script>
    (function() {
        const form = document.getElementById('wiki-form');
        const editor = document.getElementById('wiki-editor');
        const source = document.getElementById('wiki-source');
        const toolbar = document.getElementById('wiki-toolbar');
        const imageInput = document.getElementById('wiki-image-input');
        const sourceToggle = document.getElementById('wiki-source-toggle');
        if (!form || !editor || !source || !toolbar) return;

        let sourceMode = false;
        let savedRange = null;

        editor.innerHTML = source.value;

        function saveRange() {
            const selection = window.getSelection();
            if (selection.rangeCount && editor.contains(selection.anchorNode)) {
                savedRange = selection.getRangeAt(0).cloneRange();
            }
        }

        function restoreRange() {
            editor.focus();
            if (savedRange) {
                const selection = window.getSelection();
                selection.removeAllRanges();
                selection.addRange(savedRange);
            }
        }

        function setSourceMode(on) {
            sourceMode = on;
            if (on) {
                source.value = editor.innerHTML;
            } else {
                editor.innerHTML = source.value;
            }
            editor.classList.toggle('hidden', on);
            source.classList.toggle('hidden', !on);
            sourceToggle.classList.toggle('bg-gray-300', on);
        }

        ['keyup', 'mouseup', 'blur'].forEach((name) => editor.addEventListener(name, saveRange));

        // Keep the selection in the editor when a toolbar button is pressed.
        toolbar.addEventListener('mousedown', (e) => {
            if (e.target.closest('button')) e.preventDefault();
        });

        toolbar.addEventListener('click', (e) => {
            const button = e.target.closest('button');
            if (!button) return;

            if (button.dataset.action === 'source') {
                setSourceMode(!sourceMode);
                return;
            }

            if (sourceMode) return;

            if (button.dataset.cmd) {
                restoreRange();
                const value = button.dataset.value ? '<' + button.dataset.value + '>' : null;
                document.execCommand(button.dataset.cmd, false, value);
                saveRange();
            } else if (button.dataset.action === 'link') {
                const url = window.prompt('Link URL (https://...)');
                if (url) {
                    restoreRange();
                    document.execCommand('createLink', false, url);
                    saveRange();
                }
            } else if (button.dataset.action === 'image') {
                saveRange();
                imageInput.click();
            }
        });

        imageInput.addEventListener('change', async () => {
            const file = imageInput.files[0];
            if (!file) return;

            const data = new FormData();
            data.append('image', file);

            try {
                const res = await fetch(@json(route('wiki.upload-image')), {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: data,
                });
                if (!res.ok) throw new Error('Upload failed');
                const json = await res.json();
                restoreRange();
                document.execCommand('insertImage', false, json.url);
                saveRange();
            } catch (err) {
                window.alert('The image could not be uploaded. Use an image smaller than 5 MB.');
            }

            imageInput.value = '';
        });

        form.addEventListener('submit', () => {
            if (!sourceMode) source.value = editor.innerHTML;
        });
    })();
</script>