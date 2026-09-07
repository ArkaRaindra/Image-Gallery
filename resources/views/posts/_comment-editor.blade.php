<div class="rich-editor border border-gray-700 rounded overflow-hidden">
    <div class="flex items-center gap-1 bg-gray-100 border-b border-gray-300 px-2 py-1 text-xs">
        <button type="button" class="re-preview px-2 py-1 rounded hover:bg-gray-200 cursor-pointer">👁 Preview</button>
        <span class="w-px h-4 bg-gray-300 mx-1"></span>
        <button type="button" class="re-bold px-2 py-1 rounded hover:bg-gray-200 font-bold cursor-pointer">B</button>
        <button type="button" class="re-italic px-2 py-1 rounded hover:bg-gray-200 italic cursor-pointer">I</button>
        <button type="button" class="re-link px-2 py-1 rounded hover:bg-gray-200 cursor-pointer">🔗</button>
        <button type="button" class="re-image px-2 py-1 rounded hover:bg-gray-200 cursor-pointer">🖼</button>
        <div class="relative">
            <button type="button" class="re-emoji px-2 py-1 rounded hover:bg-gray-200 cursor-pointer">😊</button>
            <div
                class="re-emoji-picker hidden absolute z-40 top-full left-0 mt-1 w-56 bg-white border border-gray-300 rounded shadow-lg p-2 grid grid-cols-8 gap-1 text-lg">
            </div>
        </div>
        <input type="file" class="re-image-input hidden" accept="image/*">
    </div>

    <textarea name="body" rows="{{ $rows ?? 4 }}" required placeholder="{{ $placeholder ?? 'Post a comment' }}"
        class="re-textarea w-full px-2 py-2 bg-white text-sm focus:outline-none">{{ old('body') }}</textarea>

    <div class="re-preview-box hidden px-2 py-2 text-sm border-t border-gray-300 bg-gray-50"></div>
</div>