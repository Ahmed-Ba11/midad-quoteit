<?php

use App\Models\Quote;
use App\Models\User;

test('the text editor offers the three formats, every font, twelve colours and the four marks', function () {
    $html = $this->actingAs(User::factory()->create())->get(route('quotes.create', ['as' => 'text']))
        ->assertOk()
        ->assertSee('محرّر النص')
        ->assertSee('data-font-picker', false)
        ->getContent();

    foreach (array_keys(config('quotes.layouts')) as $layout) {
        expect($html)->toContain('name="layout" value="'.$layout.'"');
    }
    foreach (array_keys(config('quotes.fonts')) as $font) {
        expect($html)->toContain('name="font" value="'.$font.'"');
    }
    foreach (array_keys(config('quotes.highlight_colors')) as $hex) {
        expect($html)->toContain('name="highlight_color" value="'.$hex.'"');
    }
    foreach (array_keys(config('quotes.marks')) as $mark) {
        expect($html)->toContain('name="mark_style" value="'.$mark.'"');
    }
});

test('a fresh start opens the details; a draft folds them into one line with the words and who said them', function () {
    $reader = User::factory()->create();

    $this->actingAs($reader)->get(route('quotes.create', ['as' => 'text']))
        ->assertSee('<details data-details class="group rounded-2xl border border-line bg-paper"  open >', false);

    $this->actingAs($reader)->post(route('quotes.draft'), ['content' => 'العلم نور', 'kind' => 'anonymous', 'presentation' => 'text']);
    $this->actingAs($reader)->get(route('quotes.create', ['draft' => 1, 'as' => 'text']))
        ->assertDontSee('bg-paper"  open >', false)
        ->assertSee('العلم نور')
        ->assertSee('— مأثور', false);
});

test('a verse typed with a separator is published as one verse and set as صدر and عجز', function () {
    $this->actingAs(User::factory()->create())->post(route('store_quote'), [
        'content' => 'على قدر أهل العزم تأتي العزائم * وتأتي على قدر الكرام المكارم',
        'kind' => 'quoted', 'author' => 'المتنبي', 'source' => 'ديوانه',
        'presentation' => 'text', 'layout' => 'auto', 'font' => 'ruqaa', 'mark_style' => 'arch',
        'highlight_style' => 'mark', 'highlight_color' => '#3E8E7E', 'highlights' => [3],
    ])->assertSessionHasNoErrors();

    $quote = Quote::sole();
    expect($quote)->presentation->toBe('text')->font->toBe('ruqaa')->highlight_color->toBe('#3E8E7E')
        ->and($quote->readableText())->toBe('على قدر أهل العزم تأتي العزائم … وتأتي على قدر الكرام المكارم');

    $this->get(route('quotes.show', $quote))
        ->assertSee('data-mark="arch"', false)
        ->assertSeeInOrder(['hemi sadr', 'على قدر أهل', '<span class="hl">العزم</span>', 'hemi ajuz', 'وتأتي على قدر'], false)
        ->assertDontSee('العزائم * وتأتي');
});

test('«نثر» keeps a verse-looking text as written', function () {
    $this->actingAs(User::factory()->create())->post(route('store_quote'), [
        'content' => "سطر أول من خاطرة قصيرة\nوسطر ثانٍ يكملها بهدوء", 'kind' => 'original',
        'presentation' => 'text', 'layout' => 'prose',
    ])->assertSessionHasNoErrors();

    $this->get(route('quotes.show', Quote::sole()))
        ->assertSee('data-mode="prose"', false)
        ->assertSee('سطر أول من خاطرة قصيرة<br>وسطر ثانٍ يكملها بهدوء', false);
});

test('only the three formats are accepted', function () {
    $this->actingAs(User::factory()->create())
        ->post(route('store_quote'), ['content' => 'نص', 'kind' => 'original', 'layout' => '<script>'])
        ->assertSessionHasErrors('layout');
});

test('a text quote opens in the editor to edit, and can be reopened as a card', function () {
    $quote = Quote::factory()->for($owner = User::factory()->create())->create(['presentation' => 'text']);

    $this->actingAs($owner)->get(route('quotes.edit', $quote))->assertOk()->assertSee('محرّر النص')
        ->assertSee(route('quotes.edit', [$quote, 'as' => 'card']), false);
    $this->actingAs($owner)->get(route('quotes.edit', [$quote, 'as' => 'card']))->assertOk()->assertSee('مرسم الحروف');
    $this->actingAs(User::factory()->create())->get(route('quotes.edit', $quote))->assertForbidden();
});
