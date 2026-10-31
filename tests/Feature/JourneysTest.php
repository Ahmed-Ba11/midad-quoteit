<?php

use App\Models\Quote;
use App\Models\User;
use App\Models\Verse;
use Database\Seeders\LibrarySeeder;

/*
 * System scenarios: whole journeys a reader takes through رقيم, step by step, checking what they see
 * at each step — the way the owner tests the site by hand.
 */

beforeEach(function () {
    $this->seed(LibrarySeeder::class);
});

test('a new reader signs up, shares a verse as text in under a minute, and finds it everywhere', function () {
    $this->post('/signup', ['name' => 'ليلى', 'email' => 'layla@example.com', 'password' => 'secret-123'])->assertRedirect();
    $layla = User::where('email', 'layla@example.com')->sole();

    // Step 1 — «شارك اقتباساً»: the words and their source, once.
    $this->actingAs($layla)->from(route('home'))->post(route('quotes.draft'), [
        '_form' => 'share', 'content' => 'إذا غامرتَ في شرفٍ مرومِ * فلا تقنع بما دون النجومِ',
        'kind' => 'quoted', 'author' => 'المتنبي', 'source' => 'ديوان المتنبي', 'is_public' => '1', 'presentation' => 'text',
    ])->assertRedirect(route('quotes.create', ['draft' => 1, 'as' => 'text']));

    // Step 2 — «محرّر النص»: the details are there, folded; nothing to type again.
    $this->actingAs($layla)->get(route('quotes.create', ['draft' => 1, 'as' => 'text']))
        ->assertOk()->assertSee('محرّر النص')->assertSee('— المتنبي', false)->assertSee('المصدر والنشر')
        ->assertDontSee('id="quote-popup"', false);

    // Step 3 — publish with the defaults.
    $this->actingAs($layla)->post(route('store_quote'), [
        'content' => 'إذا غامرتَ في شرفٍ مرومِ * فلا تقنع بما دون النجومِ', 'kind' => 'quoted', 'author' => 'المتنبي',
        'source' => 'ديوان المتنبي', 'is_public' => '1', 'presentation' => 'text', 'layout' => 'auto', 'font' => 'amiri', 'mark_style' => 'guillemets',
    ])->assertRedirect();
    $quote = Quote::sole();

    // Step 4 — the quote page, the home page, the corner: the same words, set as a verse.
    $this->get(route('quotes.show', $quote))->assertOk()->assertSee('hemi sadr', false)->assertSee('فلا تقنع بما دون النجومِ');
    $this->get(route('home'))->assertSee('إذا غامرتَ في شرفٍ مرومِ');
    $this->get(route('profile.show', $layla))->assertSee('إذا غامرتَ في شرفٍ مرومِ');
});

test('a reader designs a card, edits it, and only they can change or delete it', function () {
    $owner = User::factory()->create();
    $stranger = User::factory()->create();
    $card = [
        'content' => 'لا تُعلن الخطوات، أعلن الوصول.', 'kind' => 'anonymous', 'presentation' => 'card', 'ratio' => 'square',
        'font' => 'naskh', 'font_size' => 40, 'bg_color' => '#8E8B86', 'text_color' => '#1E1A17',
        'design' => ['palette' => 'kraft', 'texture' => 'linen', 'box' => '1', 'box_outline' => '1'],
    ];

    $this->actingAs($owner)->post(route('store_quote'), $card)->assertSessionHasNoErrors();
    $quote = Quote::sole();
    $this->get(route('quotes.show', $quote))->assertSee('data-texture="linen"', false)->assertSee('data-outline="on"', false);

    // Edit in the studio: a new palette, saved, shown.
    $this->actingAs($owner)->get(route('quotes.edit', $quote))->assertOk()->assertSee('مرسم الحروف');
    $this->actingAs($owner)->put(route('edit_quote', $quote), [...$card, '_form' => 'studio', 'design' => ['palette' => 'indigo', 'texture' => 'none']])
        ->assertRedirect(route('quotes.show', $quote));
    expect($quote->fresh()->design)->toMatchArray(['palette' => 'indigo', 'texture' => 'none', 'box' => false]);

    // Nobody else may edit or delete it.
    $this->actingAs($stranger)->get(route('quotes.edit', $quote))->assertForbidden();
    $this->actingAs($stranger)->put(route('edit_quote', $quote), $card)->assertForbidden();
    $this->actingAs($stranger)->delete(route('delete_quote', $quote))->assertForbidden();
    expect(Quote::count())->toBe(1);

    $this->actingAs($owner)->delete(route('delete_quote', $quote))->assertRedirect();
    expect(Quote::count())->toBe(0);
});

test('a verse taken from the library keeps its exact words and poet, whatever the browser sends', function (string $as) {
    $verse = Verse::with('work.poet')->first();

    $this->actingAs(User::factory()->create())->get(route('quotes.create', ['verses' => [$verse->id], 'as' => $as]))
        ->assertOk()->assertSee('من المكتبة · يُنشر كما هو')->assertSee(e($verse->sadr), false);

    $this->post(route('store_quote'), [
        'verses' => [$verse->id], 'content' => 'نص مزوّر', 'author' => 'شخص آخر', 'presentation' => $as,
    ])->assertSessionHasNoErrors();

    expect(Quote::sole())
        ->content->toBe($verse->asText())
        ->author->toBe($verse->work->poet->name)
        ->verse_id->toBe($verse->id);
})->with(['text', 'card']);

test('a private quote stays with its owner: not on home, search, the quote page or their corner for others', function () {
    $owner = User::factory()->create();
    $quote = Quote::factory()->for($owner)->create(['content' => 'سرٌّ بيني وبين الورق', 'is_public' => false]);

    $this->get(route('home'))->assertDontSee('سرٌّ بيني وبين الورق');
    $this->get(route('home', ['q' => 'سر بيني']))->assertDontSee('سرٌّ بيني وبين الورق');
    $this->get(route('quotes.show', $quote))->assertForbidden();
    $this->get(route('profile.show', $owner))->assertDontSee('سرٌّ بيني وبين الورق');
    $this->actingAs(User::factory()->create())->get(route('quotes.show', $quote))->assertForbidden();

    $this->actingAs($owner)->get(route('quotes.show', $quote))->assertOk()->assertSee('سرٌّ بيني وبين الورق');
    $this->actingAs($owner)->get(route('profile.show', $owner))->assertSee('سرٌّ بيني وبين الورق');
});

test('a guest reads everything but is asked to sign in to write', function () {
    $quote = Quote::factory()->create();
    $verse = Verse::with('work.poet')->first();

    foreach ([route('home'), route('library.index'), route('books.index'), route('diwans.index'), route('quotes.show', $quote),
        route('library.poet', $verse->work->poet), route('library.work', $verse->work), route('profile.show', $quote->user)] as $page) {
        $this->get($page)->assertOk();
    }

    $this->get(route('quotes.create'))->assertRedirect(route('login'));
    $this->post(route('quotes.draft'), ['content' => 'نص'])->assertRedirect(route('login'));
    $this->post(route('store_quote'), ['content' => 'نص'])->assertRedirect(route('login'));
});

test('every inner page has a way back to the page it belongs to', function () {
    $quote = Quote::factory()->create();
    $verse = Verse::with('work.poet')->first();

    $this->get(route('quotes.show', $quote))->assertSee('data-back', false);
    $this->get(route('library.poet', $verse->work->poet))->assertSee('href="'.route('library.index').'#poets" data-back', false);
    $this->get(route('library.work', $verse->work))->assertSee('href="'.route('library.index').'#shelf" data-back', false);
    $this->actingAs($quote->user)->get(route('quotes.edit', $quote))->assertSee('href="'.route('quotes.show', $quote).'" data-back', false);
});

test('a failed publish brings the reader back to their words, not to an empty form', function () {
    $reader = User::factory()->create();

    $this->actingAs($reader)->from(route('quotes.create', ['as' => 'text']))->post(route('store_quote'), [
        'content' => 'كلام منقول بلا مصدر', 'kind' => 'quoted', 'author' => 'كاتب', 'presentation' => 'text',
    ])->assertRedirect(route('quotes.create', ['as' => 'text']));

    $this->actingAs($reader)->get(route('quotes.create', ['as' => 'text']))
        ->assertSee('كلام منقول بلا مصدر')
        ->assertSee('راجع الحقول')
        ->assertSee('اذكر من أين نقلته');
});
