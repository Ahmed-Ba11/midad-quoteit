<?php

use App\Models\User;

test('a logged-in user can log out with POST (FR-04)', function () {
    $this->actingAs(User::factory()->create())
        ->post(route('logout'))
        ->assertRedirect(route('home'));

    $this->assertGuest();
});

test('logout is not allowed with GET', function () {
    $this->actingAs(User::factory()->create())
        ->get('/logout')
        ->assertMethodNotAllowed();

    $this->assertAuthenticated();
});
