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
        ->assertSee($quote->created_at->format('Y-m-d'));
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
        ->assertDontSee('اقتباس رقم 1”');

    $this->get(route('home', ['page' => 2]))
        ->assertOk()
        ->assertSee('اقتباس رقم 1”');
});
