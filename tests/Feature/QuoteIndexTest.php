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
    foreach (range(1, 13) as $number) {
        Quote::factory()->for($user)->create([
            'content' => "اقتباس رقم {$number}",
            'created_at' => now()->addMinutes($number),
        ]);
    }

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('اقتباس رقم 13')
        ->assertDontSee('<p>اقتباس رقم 1</p>', false);

    $this->get(route('home', ['page' => 2]))
        ->assertOk()
        ->assertSee('<p>اقتباس رقم 1</p>', false);
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
