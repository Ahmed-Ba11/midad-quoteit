<?php

use App\Models\User;

test('the login page is shown to guests', function () {
    $this->get(route('login'))->assertOk();
});

test('a user can log in with correct credentials (FR-03)', function () {
    $user = User::factory()->create(['password' => 'secret123']);

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'secret123',
    ])->assertRedirect(route('home'));

    $this->assertAuthenticatedAs($user);
});

test('wrong credentials show an error and stay on the login page', function () {
    $user = User::factory()->create(['password' => 'secret123']);

    $this->from(route('login'))->post('/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ])
        ->assertRedirect(route('login'))
        ->assertSessionHasErrors(['email' => 'البريد الإلكتروني أو كلمة المرور غير صحيحة.'])
        ->assertSessionHasInput('email', $user->email);

    $this->assertGuest();
});

test('logging in works with a differently cased email', function () {
    $user = User::factory()->create(['email' => 'sara@example.com', 'password' => 'secret123']);

    $this->post('/login', ['email' => 'Sara@Example.com', 'password' => 'secret123']);

    $this->assertAuthenticatedAs($user);
});

test('repeated wrong passwords are locked out for a minute', function () {
    $user = User::factory()->create(['password' => 'secret123']);

    foreach (range(1, 5) as $attempt) {
        $this->post('/login', ['email' => $user->email, 'password' => 'wrong-password']);
    }

    $this->post('/login', ['email' => $user->email, 'password' => 'secret123'])
        ->assertSessionHasErrors('email');
    $this->assertGuest();
});
