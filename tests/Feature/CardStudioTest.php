<?php

use App\Models\Quote;
use App\Models\User;

test('مرسم الحروف offers the templates, palettes, frames, sizes, the box, the seal and saving as an image', function () {
    $html = $this->actingAs(User::factory()->create())->get(route('quotes.create', ['as' => 'card']))
        ->assertOk()
        ->assertSee('مرسم الحـــروف')
        ->assertSee('data-save-image', false)
        ->assertSee('data-photo-input', false)
        ->getContent();

    foreach (array_keys(config('card.templates')) as $template) {
        expect($html)->toContain('data-template="'.$template.'"');
    }
    foreach (array_keys(config('card.palettes')) as $palette) {
        expect($html)->toContain('name="design[palette]" value="'.$palette.'"');
    }
    foreach (array_keys(config('card.frames')) as $frame) {
        expect($html)->toContain('name="design[frame]" value="'.$frame.'"');
    }
    foreach (array_keys(config('quotes.ratios')) as $ratio) {
        expect($html)->toContain('name="ratio" value="'.$ratio.'"');
    }
});

test('the photo and the dedication have no form names, so they are never sent', function () {
    $html = $this->actingAs(User::factory()->create())->get(route('quotes.create', ['as' => 'card']))->getContent();

    expect($html)->toMatch('/<input type="file" accept="image\/\*" class="sr-only" data-photo-input>/')
        ->and($html)->toContain('<input id="gift-to" data-gift="to"')
        ->and($html)->toContain('<input id="gift-message" data-gift="message"');
    preg_match_all('/<input[^>]*data-gift="[^"]+"[^>]*>/', $html, $gifts);
    collect($gifts[0])->each(fn (string $input) => expect($input)->not->toContain('name='));
});

test('a designed card keeps its design, whitelisted, and shows it on the quote page', function () {
    $this->actingAs(User::factory()->create())->post(route('store_quote'), [
        'content' => 'لا تُعلن الخطوات، أعلن الوصول.', 'kind' => 'anonymous', 'presentation' => 'card',
        'ratio' => 'story', 'font' => 'mirza', 'font_size' => 40, 'bg_color' => '#1B2440', 'text_color' => '#EFE6D2',
        'design' => [
            'palette' => 'indigo', 'frame' => 'mihrab', 'texture' => 'none', 'box' => '1', 'box_style' => 'glass',
            'seal' => '1', 'seal_color' => 'gold', 'seal_emblem' => 'star', 'dim' => '20',
            'to' => 'سارة', 'message' => '<script>alert(1)</script>', 'frame_color' => 'url(javascript:x)',
        ],
    ])->assertSessionHasNoErrors();

    $quote = Quote::sole();
    expect($quote->ratio)->toBe('story')
        ->and($quote->design)->toMatchArray(['palette' => 'indigo', 'frame' => 'mihrab', 'box' => true, 'box_style' => 'glass', 'seal' => true, 'seal_color' => 'gold', 'dim' => 20, 'frame_color' => '#A8873F'])
        ->and($quote->design)->not->toHaveKeys(['to', 'message']);

    $this->get(route('quotes.show', $quote))
        ->assertSee('data-frame="mihrab"', false)
        ->assertSee('data-box-style="glass"', false)
        ->assertSee('data-seal="on"', false)
        ->assertSee('aspect-ratio: 9 / 16', false)
        ->assertDontSee('<script>alert(1)</script>', false)
        ->assertDontSee('سارة');
});

test('a card from before the studio had designs keeps its paper texture', function () {
    $quote = Quote::factory()->create(['presentation' => 'card', 'texture' => true, 'design' => null]);

    expect($quote->cardDesign()['texture'])->toBe('paper');
    $this->get(route('quotes.show', $quote))->assertSee('data-texture="paper"', false);
});

test('a saved card reopens in the studio with its design, for its owner only', function () {
    $owner = User::factory()->create();
    $quote = Quote::factory()->for($owner)->create(['presentation' => 'card', 'design' => ['frame' => 'corners', 'palette' => 'olive']]);

    $this->actingAs($owner)->get(route('quotes.edit', $quote))->assertOk()
        ->assertSee('<input type="radio" name="design[frame]" value="corners" class="sr-only" checked>', false);
    $this->actingAs(User::factory()->create())->get(route('quotes.edit', $quote))->assertForbidden();
});
