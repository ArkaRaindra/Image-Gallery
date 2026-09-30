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