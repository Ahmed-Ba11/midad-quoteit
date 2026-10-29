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
