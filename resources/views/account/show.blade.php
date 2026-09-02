@extends('layouts.app')

@section('title', $user->name)

@section('content')
    <div class="bg-green-700/40 border-b border-green-300 -mx-6 -mt-6 mb-6 px-6 py-1.5 text-xs flex flex-wrap items-center gap-x-5 gap-y-1">
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
    </div>

    <h1 class="text-xl font-bold text-sky-700 mb-3">{{ $user->name }}</h1>

    <h2 class="font-semibold mb-2">Statistics</h2>
    <table class="text-sm">
        <tbody>
            <tr><td class="pr-6 py-0.5 text-gray-600">User ID</td><td>{{ $user->id }}</td></tr>
            <tr><td class="pr-6 py-0.5 text-gray-600">Join Date</td><td>{{ $user->created_at->format('Y-m-d') }}</td></tr>
            <tr><td class="pr-6 py-0.5 text-gray-600">Email Address</td><td>{{ $user->email }}</td></tr>
            <tr><td class="pr-6 py-0.5 text-gray-600">Level</td><td>Member</td></tr>
            <tr><td class="pr-6 py-0.5 text-gray-600">Posts</td><td>{{ $stats['posts'] }}</td></tr>
            <tr><td class="pr-6 py-0.5 text-gray-600">Deleted Posts</td><td>0</td></tr>
            <tr><td class="pr-6 py-0.5 text-gray-600">Favorites</td><td>0</td></tr>
            <tr><td class="pr-6 py-0.5 text-gray-600">Favorite Groups</td><td>0</td></tr>
            <tr><td class="pr-6 py-0.5 text-gray-600">Post Changes</td><td>0</td></tr>
            <tr><td class="pr-6 py-0.5 text-gray-600">Wiki Page Changes</td><td>0</td></tr>
            <tr><td class="pr-6 py-0.5 text-gray-600">Artist Changes</td><td>0</td></tr>
            <tr><td class="pr-6 py-0.5 text-gray-600">Pool Changes</td><td>0</td></tr>
            <tr><td class="pr-6 py-0.5 text-gray-600">Forum Posts</td><td>0</td></tr>
            <tr><td class="pr-6 py-0.5 text-gray-600">Comments</td><td>{{ $stats['comments'] }}</td></tr>
            <tr><td class="pr-6 py-0.5 text-gray-600">Appeals</td><td>0</td></tr>
            <tr><td class="pr-6 py-0.5 text-gray-600">Flags</td><td>0</td></tr>
        </tbody>
    </table>
@endsection