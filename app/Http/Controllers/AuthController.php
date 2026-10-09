<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $user = $this->findUserByLogin(trim($credentials['login']));

        if (! $user || ! Auth::attempt(['id' => $user->id, 'password' => $credentials['password']], $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'login' => 'Those credentials do not match our records.',
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('posts.index'));
    }

    /**
     * Finds the account for what was typed in the login box: an email address,
     * or otherwise a username (the `name` column, as chosen at sign up).
     *
     * An exact match wins. Otherwise the match ignores letter case, but only
     * when it is unambiguous, so "arka" can still reach "Arka" without ever
     * picking the wrong account when two names differ only by case.
     */
    private function findUserByLogin(string $login): ?User
    {
        $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'name';

        if ($user = User::query()->where($field, $login)->first()) {
            return $user;
        }

        $matches = User::query()
            ->whereRaw('LOWER('.$field.') = ?', [mb_strtolower($login)])
            ->limit(2)
            ->get();

        return $matches->count() === 1 ? $matches->first() : null;
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:users,name'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        Auth::login($user);

        return redirect()->route('posts.index');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('posts.index');
    }
}