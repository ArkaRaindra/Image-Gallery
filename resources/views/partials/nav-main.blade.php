@auth
    <a href="{{ route('account.show') }}"
        class="text-green-900 {{ $isAccountSection ? 'font-bold text-base' : '' }}">My Account</a>
@else
    <a href="{{ route('login') }}" class="text-red-400">Login</a>
@endauth

<a href="{{ route('posts.index') }}"
    class="text-green-900 {{ request()->routeIs('posts.index') || request()->routeIs('posts.show') ? 'font-bold text-base' : '' }}">Posts</a>
<a href="{{ route('comments.index') }}"
    class="text-green-900 {{ $isCommentsSection ? 'font-bold text-base' : '' }}">Comments</a>
<a href="{{ route('notes.index') }}"
    class="text-green-900 {{ $isNotesSection ? 'font-bold text-base' : '' }}">Notes</a>
<span class="text-gray-600 cursor-not-allowed">Artists</span>
<span class="text-gray-600 cursor-not-allowed">Tags</span>
<span class="text-gray-600 cursor-not-allowed">Pools</span>
<span class="text-gray-600 cursor-not-allowed">Wiki</span>
<span class="text-gray-600 cursor-not-allowed">Forum</span>
<span class="text-gray-600 cursor-not-allowed">More »</span>