<?php

use App\Models\Quote;
use App\Models\User;
use App\Models\Verse;
use App\Support\CardDesign;
use Database\Seeders\LibrarySeeder;

test('مرسم الحروف is simple: background colours, texture, dim, a divider, the box, font and emphasis', function () {
    $html = $this->actingAs(User::factory()->create())->get(route('quotes.create', ['as' => 'card']))
        ->assertOk()
        ->assertSee('مرسم الحروف')
        ->getContent();

    foreach (array_keys(config('card.palettes')) as $palette) {
        expect($html)->toContain('name="design[palette]" value="'.$palette.'"');
    }
    foreach (array_keys(config('card.textures')) as $texture) {
        expect($html)->toContain('name="design[texture]" value="'.$texture.'"');
    }
    foreach (array_keys(config('card.dividers')) as $divider) {
        expect($html)->toContain('name="design[divider]" value="'.$divider.'"');
    }
    foreach (array_keys(config('quotes.ratios')) as $ratio) {
        expect($html)->toContain('name="ratio" value="'.$ratio.'"');
    }
    expect($html)->toContain('name="design[dim]"')->toContain('name="design[box]"')->toContain('data-font-picker');
});

test('every studio tool carries a short hint, and so does the colour in the text editor', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get(route('quotes.create', ['as' => 'card']))->assertSeeInOrder([
        'المقاس: شكل البطاقة', 'ملمس خفيف على الخلفية', 'زخرفة صغيرة تحت الكلمات', '«تلقائي» يملأ البطاقة',
        'مكان الأسطر في البطاقة', 'يُقرأ بوضوح على الخلفية', 'الشعر صدرٌ وعجز', 'التظليل خلفية خلف الكلمة',
        'لون المربّع الذي يحمل الكلمات', 'المسافة بين الكلمات وحافة الصندوق', 'يرفع الصندوق قليلاً', 'إطار رفيع يحدّ الصندوق',
    ]);

    $this->actingAs($user)->get(route('quotes.create', ['as' => 'text']))->assertSee('لون الإبراز على الكلمات التي تختارها');
});

test('a font size chosen by hand is kept when the card is opened again', function () {
    $owner = User::factory()->create();
    $this->actingAs($owner)->post(route('store_quote'), [
        'content' => 'حجمٌ اخترته بيدي', 'kind' => 'anonymous', 'presentation' => 'card', 'font_size' => 50,
        'design' => ['auto_size' => '0'],
    ])->assertSessionHasNoErrors();

    $quote = Quote::sole();
    expect($quote->font_size)->toBe(50)->and($quote->cardDesign()['auto_size'])->toBeFalse();

    $this->actingAs($owner)->get(route('quotes.edit', $quote))
        ->assertSee('name="font_size" value="50"', false)
        ->assertSee('name="design[auto_size]" value="0"', false);
});

test('older cards without the size choice still fit their words automatically', function () {
    expect(CardDesign::normalize([])['auto_size'])->toBeTrue();
});

test('the removed extras are gone: no templates, frames, dedication, seal, photo, watermark or image export', function () {
    $html = $this->actingAs(User::factory()->create())->get(route('quotes.create', ['as' => 'card']))->getContent();

    foreach (['data-template', 'data-surprise', 'design[frame]', 'data-gift', 'design[seal]', 'data-photo-input', 'card-brand', 'data-save-image'] as $gone) {
        expect($html)->not->toContain($gone);
    }
});

test('a designed card keeps its design, whitelisted, and the quote page shows exactly that', function () {
    $this->actingAs(User::factory()->create())->post(route('store_quote'), [
        'content' => 'لا تُعلن الخطوات، أعلن الوصول.', 'kind' => 'anonymous', 'presentation' => 'card',
        'ratio' => 'story', 'font' => 'mirza', 'font_size' => 40, 'bg_color' => '#1B2440', 'text_color' => '#EFE6D2',
        'design' => [
            'palette' => 'indigo', 'texture' => 'linen', 'box' => '1', 'box_shadow' => 'lifted', 'divider' => 'star', 'dim' => '20',
            'frame' => 'mihrab', 'message' => '<script>alert(1)</script>', 'box_color' => 'url(javascript:x)',
        ],
    ])->assertSessionHasNoErrors();

    $quote = Quote::sole();
    expect($quote)->ratio->toBe('story')->font->toBe('mirza')
        ->and($quote->design)->toMatchArray(['palette' => 'indigo', 'texture' => 'linen', 'box' => true, 'box_shadow' => 'lifted', 'divider' => 'star', 'dim' => 20, 'box_color' => '#FBF7EF'])
        ->and($quote->design)->not->toHaveKeys(['frame', 'message']);

    $this->get(route('quotes.show', $quote))
        ->assertSee('data-texture="linen"', false)
        ->assertSee('data-box="on"', false)
        ->assertSee('data-divider="star"', false)
        ->assertSee('aspect-ratio: 9 / 16', false)
        ->assertSee("font-family: 'Mirza', serif")
        ->assertDontSee('<script>alert(1)</script>', false);
});

test('a card from before the studio had designs keeps its paper texture', function () {
    $quote = Quote::factory()->create(['presentation' => 'card', 'texture' => true, 'design' => null]);

    expect($quote->cardDesign()['texture'])->toBe('paper');
    $this->get(route('quotes.show', $quote))->assertSee('data-texture="paper"', false);
});

test('a card saved with options that no longer exist still opens, with them dropped', function () {
    $owner = User::factory()->create();
    $quote = Quote::factory()->for($owner)->create(['presentation' => 'card', 'design' => ['frame' => 'corners', 'seal' => true, 'palette' => 'olive']]);

    $this->get(route('quotes.show', $quote))->assertOk()->assertDontSee('data-frame', false)->assertDontSee('card-seal', false);
    $this->actingAs($owner)->get(route('quotes.edit', $quote))->assertOk()
        ->assertSee('<input type="radio" name="design[palette]" value="olive" class="sr-only" checked>', false);
    $this->actingAs(User::factory()->create())->get(route('quotes.edit', $quote))->assertForbidden();
});

test('the editor and the studio have no second share window, and switch between text and card', function () {
    $owner = User::factory()->create();
    $quote = Quote::factory()->for($owner)->create(['presentation' => 'card']);

    $this->actingAs($owner)->get(route('quotes.edit', $quote))
        ->assertDontSee('id="quote-popup"', false)
        ->assertSee('formaction="'.route('quotes.redraft', $quote).'"', false);
    $this->actingAs($owner)->get(route('quotes.create', ['as' => 'text']))
        ->assertDontSee('id="quote-popup"', false)
        ->assertSee('formaction="'.route('quotes.draft').'"', false);
    $this->actingAs($owner)->get(route('home'))->assertSee('id="quote-popup"', false);
});

test('back from a library pick leads to the library, even when the reader signed in on the way', function () {
    $this->seed(LibrarySeeder::class);
    $verse = Verse::first();
    $url = route('quotes.create', ['verses' => [$verse->id], 'as' => 'card']);

    $this->actingAs(User::factory()->create())->from(route('login'))->get($url)
        ->assertOk()
        ->assertSee('href="'.route('library.index').'" data-back', false)
        ->assertDontSee('href="'.route('login').'" data-back', false);
});

test('the library selection offers both ways: as text or as a card', function () {
    $this->seed(LibrarySeeder::class);

    $this->get(route('library.index'))
        ->assertSee('data-selection-go data-as="text"', false)
        ->assertSee('data-selection-go data-as="card"', false)
        ->assertSee('انشره نصاً')
        ->assertSee('صمّمه بطاقة');
});
