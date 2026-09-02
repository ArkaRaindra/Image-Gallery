@extends('layouts.app')

@section('title', 'Sign up')

@section('content')
    <div class="flex justify-center">
        <div class="w-full max-w-md bg-white border border-gray-950 rounded p-6">
            <h1 class="text-xl font-bold text-center mb-6">Sign up</h1>

            @if ($errors->any())
                <div class="mb-4 text-sm text-red-700 bg-red-50 border border-red-200 rounded p-2">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('register') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-semibold mb-1">Username</label>
                    <input type="text" name="name" value="{{ old('name') }}" required autofocus
                        class="w-full px-2 py-1.5 rounded bg-white border border-gray-700 text-sm focus:outline-none focus:border-sky-500">
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" required
                        class="w-full px-2 py-1.5 rounded bg-white border border-gray-700 text-sm focus:outline-none focus:border-sky-500">
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">Password</label>
                    <input type="password" name="password" required
                        class="w-full px-2 py-1.5 rounded bg-white border border-gray-700 text-sm focus:outline-none focus:border-sky-500">
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">Confirm Password</label>
                    <input type="password" name="password_confirmation" required
                        class="w-full px-2 py-1.5 rounded bg-white border border-gray-700 text-sm focus:outline-none focus:border-sky-500">
                </div>
                <button type="submit"
                    class="w-full py-2 rounded bg-sky-600 hover:bg-sky-700 text-white font-semibold cursor-pointer">
                    Sign up
                </button>
            </form>

            <p class="text-center text-sm text-gray-600 mt-4">
                Already have an account?
                <a href="{{ route('login') }}" class="text-sky-700 hover:underline">Login</a>.
            </p>
        </div>
    </div>
@endsection