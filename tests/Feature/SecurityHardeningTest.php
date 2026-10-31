<?php

use App\Models\Quote;
use App\Models\User;
use App\Models\Verse;
use Database\Seeders\DemoSeeder;
use Database\Seeders\LibrarySeeder;

test('every page forbids framing, MIME sniffing and leaking the page address to other sites', function () {
    $this->get(route('home'))
        ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
        ->assertHeader('X-Content-Type-Options', 'nosniff')
        ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
});

test('one address cannot try passwords on many accounts', function () {
    foreach (range(1, 20) as $attempt) {
        $this->post('/login', ['email' => "person{$attempt}@example.com", 'password' => 'wrong-password']);
    }

    $this->post('/login', ['email' => 'another@example.com', 'password' => 'wrong-password'])
        ->assertSessionHasErrors('email');

    expect(session('errors')->first('email'))->toStartWith('محاولات كثيرة.');
});

test('lists sent where words are expected are refused, never a server error', function () {
    $this->actingAs(User::factory()->create())
        ->post(route('quotes.draft'), ['content' => 'نص', 'kind' => 'original', 'signature' => ['x'], 'source' => ['y']])
        ->assertSessionHasErrors('signature');

    $this->actingAs(User::factory()->create())
        ->post(route('store_quote'), ['content' => 'نص مقتبس', 'kind' => 'quoted', 'author' => 'كاتب', 'source' => ['y']])
        ->assertStatus(302);
});

test("editing one's own text never turns it into a library verse", function () {
    $this->seed(LibrarySeeder::class);
    $verse = Verse::first();
    $quote = Quote::factory()->for($owner = User::factory()->create())->create(['content' => 'خاطرة من تأليفي', 'is_original' => true]);

    $this->actingAs($owner)->put(route('edit_quote', $quote), ['content' => 'خاطرة من تأليفي', 'kind' => 'original', 'verses' => [$verse->id]]);

    expect($quote->fresh())->content->toBe('خاطرة من تأليفي')->verse_id->toBeNull();
});

test('the demo readers are never created on the public site', function () {
    app()->detectEnvironment(fn () => 'production');
    app(DemoSeeder::class)->setContainer(app())->__invoke();
    app()->detectEnvironment(fn () => 'testing');

    expect(User::count())->toBe(0);
});
