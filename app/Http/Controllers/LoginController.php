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

    /**
     * Wrong attempts allowed from one IP across all emails, so no one can try a password on many accounts.
     */
    private const MAX_ATTEMPTS_PER_IP = 20;

    public function login(LoginRequest $request): RedirectResponse
    {
        $throttleKey = 'login:'.$request->input('email').'|'.$request->ip();
        $ipKey = 'login-ip:'.$request->ip();

        foreach ([$throttleKey => self::MAX_ATTEMPTS, $ipKey => self::MAX_ATTEMPTS_PER_IP] as $key => $maxAttempts) {
            if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
                return redirect()->back()
                    ->withInput($request->only('email'))
                    ->withErrors(['email' => 'محاولات كثيرة. حاول مرة أخرى بعد '.RateLimiter::availableIn($key).' ثانية.']);
            }
        }

        if (Auth::attempt($request->validated())) {
            RateLimiter::clear($throttleKey);
            $request->session()->regenerate();

            return redirect()->intended(route('home'));
        }

        RateLimiter::hit($throttleKey, 60);
        RateLimiter::hit($ipKey, 60);

        return redirect()->back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => 'البريد الإلكتروني أو كلمة المرور غير صحيحة.']);
    }
}
