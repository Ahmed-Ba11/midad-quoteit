<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function login(LoginRequest $request): RedirectResponse
    {
        if (Auth::attempt($request->validated())) {
            $request->session()->regenerate();

            return redirect()->intended(route('home'));
        }

        return redirect()->back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => 'البريد الإلكتروني أو كلمة المرور غير صحيحة.']);
    }
}
