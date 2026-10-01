{{--
    Delete control for a post or comment.
    - admin / owner: confirm, then the item is deleted right away.
    - moderator: must give a reason; the item is hidden until an admin reviews it.

    Params: $action (destroy URL), $what ('post'|'comment'), optional $tagQuery,
    $formClass (wrapper classes) and $triggerClass (button / link classes).
--}}
@php
    $deleteUser = auth()->user();
    $what = $what ?? 'comment';
    $triggerClass = $triggerClass ?? 'text-red-600 hover:underline cursor-pointer';
    $confirmText = $what === 'post' ? 'Delete this post? This cannot be undone.' : 'Delete this comment?';
@endphp
@if ($deleteUser?->canDeleteDirectly())
    <form method="POST" action="{{ $action }}" class="{{ $formClass ?? '' }}"
        onsubmit="return confirm('{{ $confirmText }}')">
        @csrf
        @method('DELETE')
        @isset($tagQuery)
            <input type="hidden" name="tags" value="{{ $tagQuery }}">
        @endisset
        <button type="submit" class="{{ $triggerClass }}">Delete</button>
    </form>
@elseif ($deleteUser?->mustRequestDeletion())
    <details class="relative {{ $formClass ?? '' }}">
        <summary class="list-none select-none {{ $triggerClass }}">Request deletion</summary>
        <form method="POST" action="{{ $action }}"
            class="absolute left-0 top-6 z-20 w-64 space-y-2 rounded border border-gray-300 bg-white p-2 text-left text-xs text-gray-900 shadow">
            @csrf
            @method('DELETE')
            @isset($tagQuery)
                <input type="hidden" name="tags" value="{{ $tagQuery }}">
            @endisset
            <label class="block font-semibold">Reason (required)</label>
            <textarea name="reason" rows="3" required minlength="5" maxlength="1000"
                class="w-full rounded border border-gray-300 p-1" placeholder="Why should this {{ $what }} be removed?"></textarea>
            <p class="text-gray-500">The {{ $what }} is hidden until an admin approves or rejects this request.</p>
            <button type="submit"
                class="rounded bg-red-600 px-2 py-1 text-white hover:bg-red-700 cursor-pointer">Send request</button>
        </form>
    </details>
@endif