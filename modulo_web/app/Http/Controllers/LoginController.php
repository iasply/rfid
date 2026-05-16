<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function showLoginForm(Request $request)
    {
        if (Auth::check()) {
            return redirect()->route('admin.dashboard');
        }

        return response()
            ->view('auth.login')
            ->header('Cache-Control', 'no-store');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $throttleKey = Str::lower($request->input('email')) . '|' . $request->ip();
        $limit = config('auth.throttle_limit', 5);

        if (RateLimiter::tooManyAttempts($throttleKey, $limit)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            throw ValidationException::withMessages([
                'email' => [
                    trans('auth.throttle', [
                        'seconds' => $seconds,
                        'minutes' => ceil($seconds / 60),
                    ])
                ],
            ]);
        }

        if (Auth::attempt($credentials)) {
            RateLimiter::clear($throttleKey);
            $request->session()->regenerate();
            Log::info('session: regenerate no login', [
                'user_id'    => Auth::id(),
                'ip'         => $request->ip(),
                'user_agent' => $request->userAgent(),
                'session_id' => $request->session()->getId(),
            ]);
            return redirect()->intended(route('admin.dashboard'));
        }

        RateLimiter::hit($throttleKey);

        return back()->withErrors([
            'email' => __('auth.failed'),
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        $userId = Auth::id();
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        Log::info('session: logout — invalidate + regenerateToken', [
            'user_id'    => $userId,
            'ip'         => $request->ip(),
            'user_agent' => $request->userAgent(),
            'session_id' => $request->session()->getId(),
        ]);
        return redirect()->route('login');
    }
}
