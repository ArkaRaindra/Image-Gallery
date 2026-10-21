@if ($isAuthSection)
    <a href="{{ route('register') }}" class="hover:text-gray-200 cursor-pointer">Sign up</a>
    <a href="{{ route('login') }}" class="hover:text-gray-200 cursor-pointer">Login</a>
    <span class="cursor-not-allowed">Forgot password</span>
@elseif ($isAccountSection)
    <a href="{{ route('posts.index') }}" class="hover:text-gray-200 cursor-pointer">Listing</a>
    <span class="cursor-not-allowed">Profile</span>
    <span class="cursor-not-allowed">Settings</span>
    <span class="cursor-not-allowed">Messages</span>
    <span class="cursor-not-allowed">My Uploads</span>
    <span class="cursor-not-allowed">Upgrade</span>
    <form method="POST" action="{{ route('logout') }}" class="inline m-0">
        @csrf
        <button type="submit" class="hover:text-gray-200 cursor-pointer">Log out</button>
    </form>
@elseif ($isCommentsSection)
    <a href="{{ route('comments.index') }}"
        class="hover:text-gray-200 cursor-pointer {{ request()->routeIs('comments.index') ? 'font-bold text-gray-200' : '' }}">Comments</a>
    @auth
        <a href="{{ route('comments.index', ['on_my_uploads' => 1]) }}"
            class="hover:text-gray-200 cursor-pointer">On My Uploads</a>
    @else
        <span class="cursor-not-allowed">On My Uploads</span>
    @endauth
    <a href="{{ route('comments.search') }}"
        class="hover:text-gray-200 cursor-pointer {{ request()->routeIs('comments.search') ? 'font-bold text-gray-200' : '' }}">Search</a>
    <span class="cursor-not-allowed">Help</span>
@elseif ($isNotesSection)
    <a href="{{ route('notes.index') }}"
        class="hover:text-gray-200 cursor-pointer {{ request()->routeIs('notes.index') ? 'font-bold text-gray-200' : '' }}">Notes</a>
    <a href="{{ route('posts.index') }}" class="hover:text-gray-200 cursor-pointer">Posts</a>
    <a href="{{ route('notes.changes') }}"
        class="hover:text-gray-200 cursor-pointer {{ request()->routeIs('notes.changes') ? 'font-bold text-gray-200' : '' }}">Changes</a>
    <span class="cursor-not-allowed">Help</span>
@elseif ($isWikiSection)
    <form method="GET" action="{{ route('wiki.index') }}" class="m-0">
        <input type="text" name="title" placeholder="Search wiki pages" autocomplete="off"
            class="w-40 px-2 py-0.5 rounded bg-white border border-gray-700 text-gray-900 text-xs focus:outline-none focus:border-sky-500">
    </form>
    <a href="{{ route('wiki.index') }}"
        class="hover:text-gray-200 cursor-pointer {{ request()->routeIs('wiki.index') ? 'font-bold text-gray-200' : '' }}">Search</a>
    <a href="{{ route('wiki.create') }}"
        class="hover:text-gray-200 cursor-pointer {{ request()->routeIs('wiki.create') ? 'font-bold text-gray-200' : '' }}">New</a>
    <a href="{{ route('wiki.versions') }}"
        class="hover:text-gray-200 cursor-pointer {{ request()->routeIs('wiki.versions') || request()->routeIs('wiki.diff') ? 'font-bold text-gray-200' : '' }}">Changes</a>
    <a href="{{ route('wiki.show', 'help:wiki') }}" class="hover:text-gray-200 cursor-pointer">Help</a>
    @if (!empty($wikiTag))
        <span>|</span>
        <a href="{{ route('posts.index', ['tags' => $wikiTag->name]) }}"
            class="hover:text-gray-200 cursor-pointer">Posts ({{ $wikiTag->post_count }})</a>
        <a href="{{ route('wiki.versions', ['tag_id' => $wikiTag->id]) }}"
            class="hover:text-gray-200 cursor-pointer">History</a>
        <a href="{{ route('wiki.edit', $wikiTag->name) }}"
            class="hover:text-gray-200 cursor-pointer {{ request()->routeIs('wiki.edit') ? 'font-bold text-gray-200' : '' }}">Edit</a>
    @endif
@else
    <a href="{{ route('posts.index') }}"
        class="hover:text-gray-200 cursor-pointer {{ request()->routeIs('posts.index') || request()->routeIs('posts.show') ? 'font-bold text-gray-200' : '' }}">Listing</a>
    @auth
        <a href="{{ auth()->user()->isAdmin() ? '/admin/posts/create' : route('upload.create') }}"
            class="hover:text-gray-200 cursor-pointer">Upload</a>
    @else
        <a href="{{ route('login') }}" class="hover:text-gray-200 cursor-pointer">Upload</a>
    @endauth
    <span class="cursor-not-allowed">Hot</span>
    @auth
        <a href="{{ route('favorites.index') }}" class="hover:text-gray-200 cursor-pointer">Favorites</a>
        <span class="cursor-not-allowed">Fav groups</span>
        <span class="cursor-not-allowed">Saved searches</span>
    @endauth
    <span class="cursor-not-allowed">Changes</span>
    <span class="cursor-not-allowed">Help</span>
@endif