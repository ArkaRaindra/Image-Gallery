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
                    <label for="login" class="block text-sm font-semibold mb-1">Username or Email</label>
                    <input type="text" id="login" name="login" value="{{ old('login') }}" required autofocus
                        autocomplete="username" autocapitalize="none" spellcheck="false"
                        class="w-full px-2 py-1.5 rounded bg-white border border-gray-700 text-sm focus:outline-none focus:border-sky-500">
                </div>
                <div>
                    <label for="password" class="block text-sm font-semibold mb-1">Password</label>
                    <div class="relative">
                        <input type="password" id="password" name="password" required autocomplete="current-password"
                            class="w-full pl-2 pr-10 py-1.5 rounded bg-white border border-gray-700 text-sm focus:outline-none focus:border-sky-500">
                        <button type="button" id="toggle-password" aria-label="Show password" aria-pressed="false"
                            title="Show password"
                            class="absolute inset-y-0 right-0 px-2 flex items-center text-gray-600 hover:text-gray-900 cursor-pointer">
                            {{-- Eye: password hidden --}}
                            <svg id="icon-eye" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z" />
                                <circle cx="12" cy="12" r="3" />
                            </svg>
                            {{-- Eye with a slash: password visible --}}
                            <svg id="icon-eye-off" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 hidden" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M17.94 17.94A10.9 10.9 0 0 1 12 19c-6.5 0-10-7-10-7a18.5 18.5 0 0 1 5.06-5.94" />
                                <path d="M9.9 4.24A10.9 10.9 0 0 1 12 5c6.5 0 10 7 10 7a18.5 18.5 0 0 1-2.16 3.19" />
                                <path d="M14.12 14.12A3 3 0 1 1 9.88 9.88" />
                                <path d="M1 1l22 22" />
                            </svg>
                        </button>
                    </div>
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

    <script>
        (function () {
            const input = document.getElementById('password');
            const button = document.getElementById('toggle-password');
            const eye = document.getElementById('icon-eye');
            const eyeOff = document.getElementById('icon-eye-off');

            button.addEventListener('click', function () {
                const show = input.type === 'password';

                input.type = show ? 'text' : 'password';
                eye.classList.toggle('hidden', show);
                eyeOff.classList.toggle('hidden', !show);

                const label = show ? 'Hide password' : 'Show password';
                button.setAttribute('aria-label', label);
                button.setAttribute('aria-pressed', show ? 'true' : 'false');
                button.title = label;
            });
        })();
    </script>
@endsection