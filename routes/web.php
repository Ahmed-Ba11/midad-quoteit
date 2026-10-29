<?php

use App\Http\Controllers\LoginController;
use App\Http\Controllers\LogoutController;
use App\Http\Controllers\QuoteController;
use App\Http\Controllers\SignupController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;



Route::get('/', [QuoteController::class, 'index'])->name('home');

Route::get('/signup', [function () {
    return view('Authentication.SignUp', ['page'=>'Sign Up']);
}]);
Route::post('/signup', [SignupController::class, 'signup']);

Route::get('/login', function () {
    return view('Authentication.LogIn', ['page'=>'Log In']);
});
Route::post('/login', [LoginController::class, 'login']);

Route::get('/logout', [LogoutController::class, 'logout']);

Route::post('/', [QuoteController::class, 'store']);

Route::put('/quote/{quote}', [QuoteController::class, 'update'])->name('edit_quote');
Route::delete('/quote/{quote}', [QuoteController::class, 'destroy'])->name('delete_quote');

