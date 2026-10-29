<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

test('a guest can sign up with an Arabic name and is logged in (FR-02)', function () {
    $this->post('/signup', [
        'name' => 'أحمد خالد',
        'email' => 'ahmed@example.com',
        'password' => 'secret123',
    ])->assertRedirect(route('home'));

    $user = User::where('email', 'ahmed@example.com')->first();

    expect($user)->not->toBeNull()
        ->and(Hash::check('secret123', $user->password))->toBeTrue()
        ->and($user->password)->not->toBe('secret123');
    $this->assertAuthenticatedAs($user);
});

test('an English name is accepted', function () {
    $this->post('/signup', [
        'name' => 'Ahmed Khalid',
        'email' => 'ahmed@example.com',
        'password' => 'secret123',
    ])->assertRedirect(route('home'));

    $this->assertAuthenticated();
});

test('invalid names are rejected', function (string $name) {
    $this->from('/signup')->post('/signup', [
        'name' => $name,
        'email' => 'ahmed@example.com',
        'password' => 'secret123',
    ])->assertRedirect('/signup')->assertSessionHasErrors('name');

    $this->assertGuest();
})->with([
    'one letter' => 'أ',
    'too long' => str_repeat('a', 51),
    'digits' => 'Ahmed123',
    'symbols' => 'Ahmed!',
    'empty' => '',
]);

test('a 50-letter name is accepted', function () {
    $this->post('/signup', [
        'name' => str_repeat('a', 50),
        'email' => 'ahmed@example.com',
        'password' => 'secret123',
    ])->assertSessionHasNoErrors();
});

test('a duplicate email is rejected', function () {
    User::factory()->create(['email' => 'taken@example.com']);

    $this->post('/signup', [
        'name' => 'Ahmed',
        'email' => 'taken@example.com',
        'password' => 'secret123',
    ])->assertSessionHasErrors(['email' => 'هذا البريد مسجّل مسبقاً.']);
});

test('an invalid email or short password is rejected', function () {
    $this->post('/signup', [
        'name' => 'Ahmed',
        'email' => 'not-an-email',
        'password' => 'short',
    ])->assertSessionHasErrors(['email', 'password']);

    $this->assertGuest();
});

test('logged-in users cannot open the sign up page', function () {
    $this->actingAs(User::factory()->create())
        ->get('/signup')
        ->assertRedirect();
});
