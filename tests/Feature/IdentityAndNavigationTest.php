<?php

use App\Models\Diwan;
use App\Models\Poet;
use App\Models\Quote;
use App\Models\Saying;
use App\Models\User;
use Database\Seeders\LibrarySeeder;

test('every page carries the رقيم name, the documentation footer and the installable-app tags', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('<title>الرئيسية ·  رقيم</title>', false)
        ->assertSee('التوثيق والحقوق')
        ->assertSee('مقاييس اللغة')
        ->assertSee('rel="manifest" href="/manifest.webmanifest"', false)
        ->assertSee('name="theme-color"', false)
        ->assertDontSee('مِـــداد');

    expect(file_exists(public_path('manifest.webmanifest')))->toBeTrue()
        ->and(json_decode(file_get_contents(public_path('manifest.webmanifest')), true)['short_name'])->toBe('رقيم')
        ->and(file_exists(public_path('icons/icon-maskable-512.png')))->toBeTrue()
        ->and(file_get_contents(public_path('sw.js')))->toContain("event.request.mode !== 'navigate'");
});

test('sign in and sign up set the verse on the tablet, whole in the page, with the room as decoration only', function (string $page) {
    $this->seed(LibrarySeeder::class);

    $html = $this->get(route($page))->assertOk()
        ->assertSee('<div class="room" aria-hidden="true">', false)
        ->assertSee('<div class="glare" aria-hidden="true"></div>', false)
        ->assertSee('<symbol id="mark"', false)
        ->assertSee('data-write', false)
        ->getContent();

    // Both hemistichs are in the markup from the start; only their reveal is animated.
    expect(substr_count($html, 'class="ink"'))->toBe(2);
})->with(['login', 'signup']);

test('the service worker never caches pages, only the offline page and icons', function () {
    $worker = file_get_contents(public_path('sw.js'));

    expect($worker)->toContain("const OFFLINE_FILES = ['/offline.html', '/icons/favicon.svg', '/icons/icon-192.png'];")
        ->not->toContain('cache.put');
});

test('the theme switch is the familiar sun and crescent, labelled for screen readers', function () {
    $this->get(route('home'))
        ->assertSee('data-theme-toggle', false)
        ->assertSee('aria-label="الوضع الليلي"', false)
        ->assertSee('theme-star', false);
});

test('inner pages have a back link to the page they belong to', function () {
    $this->seed(LibrarySeeder::class);
    $quote = Quote::factory()->create();
    $diwan = Diwan::factory()->create();

    $this->get(route('quotes.show', $quote))->assertSee('href="'.route('home').'"', false)->assertSee('aria-label="رجوع"', false);
    $this->get(route('diwans.show', $diwan))->assertSee('href="'.route('diwans.index').'"', false);
    $this->get(route('library.poet', Poet::first()))->assertSee(route('library.index').'#poets', false);
});

test("the studio's back link never leads to another site", function () {
    $user = User::factory()->create();

    $this->actingAs($user)->withHeader('referer', 'https://evil.example/phish')
        ->get(route('quotes.create'))
        ->assertOk()
        ->assertDontSee('evil.example')
        ->assertSee('href="'.route('home').'"', false);

    $this->actingAs($user)->withHeader('referer', route('library.index'))
        ->get(route('quotes.create'))
        ->assertSee('href="'.route('library.index').'"', false);
});

test('after publishing, the reader lands on the new quote with a confirmation of what they chose', function (string $isPublic, string $message) {
    $this->actingAs(User::factory()->create())
        ->followingRedirects()
        ->post(route('store_quote'), ['content' => 'سطر جديد', 'is_public' => $isPublic, 'kind' => 'original'])
        ->assertOk()
        ->assertSee('سطر جديد')
        ->assertSee($message)
        ->assertSee('data-toast', false);
})->with([
    'private' => ['0', 'أُضيف اقتباسك إلى ركنك، ولا يراه غيرك.'],
    'public' => ['1', 'أُضيف اقتباسك، ويراه القرّاء الآن.'],
]);

test('the confirmation names the anthology the quote was filed in, and only one of my own', function () {
    $reader = User::factory()->create();
    $mine = $reader->anthologies()->create(['title' => 'أبيات الحكمة']);
    $theirs = User::factory()->create()->anthologies()->create(['title' => 'ليست لي']);

    $this->actingAs($reader)->followingRedirects()
        ->post(route('store_quote'), ['content' => 'سطر جديد', 'is_public' => '1', 'kind' => 'original', 'anthology_id' => $mine->id])
        ->assertSee('أُضيف اقتباسك، ويراه القرّاء الآن. وحُفظ في مختاراتك «أبيات الحكمة».');

    $this->actingAs($reader)->followingRedirects()
        ->post(route('store_quote'), ['content' => 'سطر آخر', 'is_public' => '1', 'kind' => 'original', 'anthology_id' => $theirs->id])
        ->assertSee('أُضيف اقتباسك، ويراه القرّاء الآن.')
        ->assertDontSee('ليست لي');

    expect($theirs->quotes()->count())->toBe(0);
});

test('no hadith is listed among the sayings', function () {
    $this->seed(LibrarySeeder::class);

    expect(Saying::where('source', 'like', '%حديث%')->exists())->toBeFalse();
    $this->get(route('library.index'))->assertDontSee('حديث شريف');
});
