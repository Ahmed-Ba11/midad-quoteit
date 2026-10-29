<?php

use App\Http\Controllers\LoginController;
use App\Http\Controllers\LogoutController;
use App\Http\Controllers\QuoteController;
use App\Http\Controllers\SignupController;
use Illuminate\Support\Facades\Route;

Route::get('/', [QuoteController::class, 'index'])->name('home');

Route::middleware('guest')->group(function () {
    Route::view('/signup', 'auth.signup')->name('signup');
    Route::post('/signup', [SignupController::class, 'signup']);

    Route::view('/login', 'auth.login')->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LogoutController::class, 'logout'])->name('logout');

    Route::post('/', [QuoteController::class, 'store'])->name('store_quote');
    Route::get('/quotes/create', [QuoteController::class, 'create'])->name('quotes.create');
    Route::get('/quote/{quote}/edit', [QuoteController::class, 'edit'])->name('quotes.edit')->can('update', 'quote');
    Route::put('/quote/{quote}', [QuoteController::class, 'update'])->name('edit_quote')->can('update', 'quote');
    Route::delete('/quote/{quote}', [QuoteController::class, 'destroy'])->name('delete_quote')->can('delete', 'quote');
});
