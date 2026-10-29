<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function login(LoginRequest $request)
    {
        if (Auth::attempt($request->validated())) {
            return redirect()->route('home');
        }
        return redirect()->back()->withInput($request->only('email'))->withErrors(['email' => 'The entered information is not correct']);
    }
}
