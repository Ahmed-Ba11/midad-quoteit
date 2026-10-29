<?php

use App\Http\Controllers\BookmarkController;
use App\Http\Controllers\LibraryController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\LogoutController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QuoteController;
use App\Http\Controllers\ReplyController;
use App\Http\Controllers\SignupController;
use Illuminate\Support\Facades\Route;

Route::get('/', [QuoteController::class, 'index'])->name('home');
Route::get('/quote/{quote}', [QuoteController::class, 'show'])->name('quotes.show');
Route::get('/users/{user}', [ProfileController::class, 'show'])->name('profile.show');

Route::get('/library', [LibraryController::class, 'index'])->name('library.index');
Route::get('/library/works/{work}', [LibraryController::class, 'work'])->name('library.work');
Route::get('/library/poets/{poet}', [LibraryController::class, 'poet'])->name('library.poet');

Route::middleware('guest')->group(function () {
    Route::view('/signup', 'auth.signup')->name('signup');
    Route::post('/signup', [SignupController::class, 'signup'])->middleware('throttle:10,1');

    Route::view('/login', 'auth.login')->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LogoutController::class, 'logout'])->name('logout');
    Route::patch('/profile/bio', [ProfileController::class, 'updateBio'])->name('profile.bio');

    Route::post('/', [QuoteController::class, 'store'])->name('store_quote');
    Route::get('/quotes/create', [QuoteController::class, 'create'])->name('quotes.create');
    Route::get('/quote/{quote}/edit', [QuoteController::class, 'edit'])->name('quotes.edit')->can('update', 'quote');
    Route::put('/quote/{quote}', [QuoteController::class, 'update'])->name('edit_quote')->can('update', 'quote');
    Route::delete('/quote/{quote}', [QuoteController::class, 'destroy'])->name('delete_quote')->can('delete', 'quote');

    Route::post('/quote/{quote}/like', [LikeController::class, 'toggle'])->name('quotes.like');
    Route::post('/quote/{quote}/bookmark', [BookmarkController::class, 'toggle'])->name('quotes.bookmark');
    Route::post('/quote/{quote}/replies', [ReplyController::class, 'store'])->name('replies.store');
});
