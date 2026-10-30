<?php

use App\Http\Controllers\AnthologyController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\BookmarkController;
use App\Http\Controllers\DiwanController;
use App\Http\Controllers\FavoritePoetController;
use App\Http\Controllers\LibraryController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\LogoutController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QuoteController;
use App\Http\Controllers\ReplyController;
use App\Http\Controllers\SignupController;
use App\Http\Controllers\WallController;
use Illuminate\Support\Facades\Route;

Route::get('/', [QuoteController::class, 'index'])->name('home');
Route::get('/quote/{quote}', [QuoteController::class, 'show'])->name('quotes.show')->can('view', 'quote');
Route::get('/users/{user}', [ProfileController::class, 'show'])->name('profile.show');

Route::get('/library', [LibraryController::class, 'index'])->name('library.index');
Route::get('/library/works/{work}', [LibraryController::class, 'work'])->name('library.work');
Route::get('/library/poets/{poet}', [LibraryController::class, 'poet'])->name('library.poet');

Route::get('/books', [BookController::class, 'index'])->name('books.index');
Route::get('/books/{book}', [BookController::class, 'show'])->name('books.show');

Route::get('/anthologies/{anthology}', [AnthologyController::class, 'show'])->name('anthologies.show')->can('view', 'anthology');
Route::get('/diwans', [DiwanController::class, 'index'])->name('diwans.index');

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
    Route::post('/quotes/draft', [QuoteController::class, 'draft'])->name('quotes.draft');
    Route::get('/quote/{quote}/edit', [QuoteController::class, 'edit'])->name('quotes.edit')->can('update', 'quote');
    Route::put('/quote/{quote}', [QuoteController::class, 'update'])->name('edit_quote')->can('update', 'quote');
    Route::delete('/quote/{quote}', [QuoteController::class, 'destroy'])->name('delete_quote')->can('delete', 'quote');

    Route::post('/quote/{quote}/like', [LikeController::class, 'toggle'])->name('quotes.like')->can('view', 'quote');
    Route::post('/quote/{quote}/bookmark', [BookmarkController::class, 'toggle'])->name('quotes.bookmark')->can('view', 'quote');
    Route::post('/quote/{quote}/replies', [ReplyController::class, 'store'])->name('replies.store')->can('view', 'quote');
    Route::post('/books/{book}/shelf', [BookController::class, 'shelf'])->name('books.shelf');

    // ركني: pin a quote, keep favourite poets, build anthologies (مختاراتي).
    Route::post('/quote/{quote}/pin', [ProfileController::class, 'pin'])->name('profile.pin')->can('update', 'quote');
    Route::post('/library/poets/{poet}/favorite', [FavoritePoetController::class, 'toggle'])->name('poets.favorite');
    Route::post('/library/sayings/{saying}/wall', [WallController::class, 'toggle'])->name('sayings.wall');
    Route::post('/anthologies', [AnthologyController::class, 'store'])->name('anthologies.store');
    Route::put('/anthologies/{anthology}', [AnthologyController::class, 'update'])->name('anthologies.update')->can('update', 'anthology');
    Route::delete('/anthologies/{anthology}', [AnthologyController::class, 'destroy'])->name('anthologies.destroy')->can('delete', 'anthology');
    Route::post('/anthologies/{anthology}/quotes/{quote}', [AnthologyController::class, 'toggle'])->name('anthologies.toggle')->can('update', 'anthology');

    // دواوين القرّاء: a reader's own copied-out diwans.
    Route::get('/diwans/create', [DiwanController::class, 'create'])->name('diwans.create');
    Route::post('/diwans', [DiwanController::class, 'store'])->name('diwans.store');
    Route::get('/diwans/{diwan}/edit', [DiwanController::class, 'edit'])->name('diwans.edit')->can('update', 'diwan');
    Route::put('/diwans/{diwan}', [DiwanController::class, 'update'])->name('diwans.update')->can('update', 'diwan');
    Route::delete('/diwans/{diwan}', [DiwanController::class, 'destroy'])->name('diwans.destroy')->can('delete', 'diwan');
    Route::post('/diwans/{diwan}/verses', [DiwanController::class, 'storeVerse'])->name('diwans.verses.store')->can('update', 'diwan');
    Route::delete('/diwans/{diwan}/verses/{verse}', [DiwanController::class, 'destroyVerse'])->name('diwans.verses.destroy')->can('update', 'diwan')->scopeBindings();
});

// After /diwans/create so "create" is not read as a diwan id.
Route::get('/diwans/{diwan}', [DiwanController::class, 'show'])->name('diwans.show')->can('view', 'diwan');
