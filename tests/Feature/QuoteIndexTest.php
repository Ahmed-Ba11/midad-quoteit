<?php

use App\Models\Quote;
use App\Models\User;

test('guests see quotes with text, sharer name and date (FR-01)', function () {
    $user = User::factory()->create(['name' => 'أحمد']);
    $quote = Quote::factory()->for($user)->create(['content' => 'العلم نور']);

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('العلم نور')
        ->assertSee('أحمد')
        ->assertSee($quote->created_at->translatedFormat('j F Y'));
});

test('quotes are paginated 12 per page, newest first', function () {
    $user = User::factory()->create();
    Quote::factory()->for($user)->create(['content' => 'أقدم اقتباس', 'created_at' => now()->subDay()]);
    Quote::factory()->for($user)->count(12)->create(['content' => 'اقتباس أحدث']);

    // A search hides the quote of the day, which could otherwise be the oldest quote.
    $this->get(route('home', ['q' => 'اقتباس']))
        ->assertOk()
        ->assertSee('اقتباس أحدث')
        ->assertDontSee('أقدم اقتباس');

    $this->get(route('home', ['q' => 'اقتباس', 'page' => 2]))
        ->assertOk()
        ->assertSee('أقدم اقتباس');
});

test('guests see a log-in button and no edit or delete options (US-01)', function () {
    Quote::factory()->create();

    $this->get(route('home'))
        ->assertSee('سجّل دخولك لتشارك اقتباساً')
        ->assertSee('href="'.route('login').'"', false)
        ->assertDontSee('aria-label="حذف"', false)
        ->assertDontSee('aria-label="تعديل"', false);
});

test('logged-in users see their name at the top (US-03)', function () {
    $this->actingAs(User::factory()->create(['name' => 'سارة']))
        ->get(route('home'))
        ->assertSee('سارة');
});
