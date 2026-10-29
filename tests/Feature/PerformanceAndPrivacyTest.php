<?php

use App\Models\Quote;
use App\Models\Reply;
use App\Models\User;
use Database\Seeders\LibrarySeeder;
use Illuminate\Support\Facades\DB;

/**
 * Runs the request and returns how many SQL queries it made.
 */
function queriesFor(callable $request): int
{
    DB::flushQueryLog();
    DB::enableQueryLog();
    $request();
    $count = count(DB::getQueryLog());
    DB::disableQueryLog();

    return $count;
}

test('the quotes page runs the same small number of queries however many quotes there are (NFR: < 3 s)', function () {
    $viewer = User::factory()->create();
    $quotes = Quote::factory()->count(30)->create();
    $quotes->each(fn (Quote $quote) => $quote->likers()->attach($viewer));
    Reply::factory()->for($quotes->first())->count(5)->create();

    $few = queriesFor(fn () => $this->actingAs($viewer)->get(route('home'))->assertOk());

    Quote::factory()->count(30)->create();
    $many = queriesFor(fn () => $this->actingAs($viewer)->get(route('home'))->assertOk());

    expect($few)->toBeLessThanOrEqual(12)
        ->and($many)->toBe($few);
});

test('the quotes page renders well within the 3-second budget', function () {
    Quote::factory()->count(60)->create();

    $started = microtime(true);
    $this->get(route('home'))->assertOk();

    expect(microtime(true) - $started)->toBeLessThan(3.0);
});

test('the library page avoids per-item queries', function () {
    $this->seed(LibrarySeeder::class);

    expect(queriesFor(fn () => $this->get(route('library.index'))->assertOk()))->toBeLessThanOrEqual(15);
});

test('passwords are stored hashed, never as plain text (NFR)', function () {
    $this->post('/signup', ['name' => 'Sara', 'email' => 'sara@example.com', 'password' => 'secret123']);

    $stored = DB::table('users')->where('email', 'sara@example.com')->value('password');

    expect($stored)->not->toBe('secret123')
        ->and(password_get_info($stored)['algoName'])->not->toBe('unknown');
});

test('no page shows a user\'s email address', function () {
    $user = User::factory()->create(['email' => 'private@example.com']);
    $quote = Quote::factory()->for($user)->create();
    Reply::factory()->for($quote)->for($user)->create();

    foreach ([route('home'), route('quotes.show', $quote), route('profile.show', $user)] as $url) {
        $this->get($url)->assertDontSee('private@example.com');
        $this->actingAs(User::factory()->create())->get($url)->assertDontSee('private@example.com');
    }
});

test('password hashes and remember tokens are never serialised', function () {
    expect(User::factory()->create()->toArray())
        ->not->toHaveKey('password')
        ->not->toHaveKey('remember_token');
});

test('every write route rejects requests without a CSRF token', function () {
    $quote = Quote::factory()->create();
    $this->actingAs($quote->user);

    // The test suite skips CSRF by default; turn the real middleware back on for this check.
    $this->withMiddleware();
    app()->instance('env', 'production');

    foreach ([
        ['post', route('store_quote')],
        ['put', route('edit_quote', $quote)],
        ['delete', route('delete_quote', $quote)],
        ['post', route('quotes.like', $quote)],
        ['post', route('replies.store', $quote)],
        ['post', route('logout')],
    ] as [$method, $url]) {
        $this->{$method}($url, ['content' => 'abc', 'body' => 'abc'])->assertStatus(419);
    }

    $this->assertModelExists($quote);
});

test('error pages are in Arabic and use the site layout', function () {
    $this->get('/quote/999999')->assertNotFound()->assertSee('الصفحة غير موجودة')->assertSee('مِـــداد');

    $quote = Quote::factory()->create();
    $this->actingAs(User::factory()->create())
        ->get(route('quotes.edit', $quote))
        ->assertForbidden()
        ->assertSee('هذا الإجراء متاح لصاحب الاقتباس فقط.');
});
