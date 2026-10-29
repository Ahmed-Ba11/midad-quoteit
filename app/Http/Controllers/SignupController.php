<?php

namespace App\Http\Controllers;

use App\Http\Requests\SignupRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SignupController extends Controller
{
    public function signup(SignupRequest $request) {
        // dd($request->email);
        $user = User::create($request->validated());
        Auth::login($user);
        return redirect()->route('home');
    }
}
