@extends('layouts.app')

@section('title', 'Login')

@section('content')
    <div class="flex justify-center">
        <div class="w-full max-w-md bg-white border border-gray-950 rounded p-6">
            <h1 class="text-xl font-bold text-center mb-6">Login</h1>

            @if ($errors->any())
                <div class="mb-4 text-sm text-red-700 bg-red-50 border border-red-200 rounded p-2">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-semibold mb-1">Name or Email</label>
                    <input type="text" name="login" value="{{ old('login') }}" required autofocus
                        class="w-full px-2 py-1.5 rounded bg-white border border-gray-700 text-sm focus:outline-none focus:border-sky-500">
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">Password</label>
                    <input type="password" name="password" required
                        class="w-full px-2 py-1.5 rounded bg-white border border-gray-700 text-sm focus:outline-none focus:border-sky-500">
                </div>
                <button type="submit"
                    class="w-full py-2 rounded bg-sky-600 hover:bg-sky-700 text-white font-semibold cursor-pointer">
                    Login
                </button>
            </form>

            <p class="text-center text-sm text-gray-600 mt-4">
                Don't have an account?
                <a href="{{ route('register') }}" class="text-sky-700 hover:underline">Create a new account</a>.
            </p>
        </div>
    </div>
@endsection