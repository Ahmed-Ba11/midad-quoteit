<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;

class LoginController extends Controller
{
    /**
     * Wrong attempts allowed per email + IP before a one-minute pause (guards against password guessing).
     */
    private const MAX_ATTEMPTS = 5;

    public function login(LoginRequest $request): RedirectResponse
    {
        $throttleKey = 'login:'.$request->input('email').'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, self::MAX_ATTEMPTS)) {
            return redirect()->back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'محاولات كثيرة. حاول مرة أخرى بعد '.RateLimiter::availableIn($throttleKey).' ثانية.']);
        }

        if (Auth::attempt($request->validated())) {
            RateLimiter::clear($throttleKey);
            $request->session()->regenerate();

            return redirect()->intended(route('home'));
        }

        RateLimiter::hit($throttleKey, 60);

        return redirect()->back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => 'البريد الإلكتروني أو كلمة المرور غير صحيحة.']);
    }
}
