<?php

use App\Models\Quote;
use App\Models\User;
use App\Models\Verse;
use Database\Seeders\LibrarySeeder;

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
        ->assertSee('formaction="'.route('quotes.redraft', $quote).'"', false);
    $this->actingAs($owner)->get(route('quotes.edit', [$quote, 'as' => 'card']))->assertOk()->assertSee('مرسم الحروف');
    $this->actingAs(User::factory()->create())->get(route('quotes.edit', $quote))->assertForbidden();
});

test('switching a saved quote between «نص» and «بطاقة» keeps the unsaved choices, and saves nothing', function () {
    $quote = Quote::factory()->for($owner = User::factory()->create())
        ->create(['presentation' => 'card', 'content' => 'نص محفوظ', 'is_original' => true, 'font' => 'amiri']);

    $this->actingAs($owner)
        ->put(route('quotes.redraft', $quote), ['_form' => 'studio', 'content' => 'نص لم يُحفظ بعد', 'kind' => 'original', 'font' => 'mirza', 'switch_to' => 'text'])
        ->assertRedirect(route('quotes.edit', [$quote, 'as' => 'text', 'draft' => 1]));

    $this->actingAs($owner)->get(route('quotes.edit', [$quote, 'as' => 'text', 'draft' => 1]))
        ->assertOk()->assertSee('محرّر النص')->assertSee('نص لم يُحفظ بعد')
        ->assertSee('name="font" value="mirza" class="sr-only" checked', false);

    expect($quote->fresh())->content->toBe('نص محفوظ')->font->toBe('amiri');

    $this->actingAs(User::factory()->create())
        ->put(route('quotes.redraft', $quote), ['content' => 'ليس لي', 'switch_to' => 'text'])->assertForbidden();
});

test('card → text → card keeps the card choices the text editor has no fields for', function () {
    $quote = Quote::factory()->for($owner = User::factory()->create())->create(['presentation' => 'card', 'is_original' => true]);

    $this->actingAs($owner)->put(route('quotes.redraft', $quote), [
        'content' => 'نص قصير', 'kind' => 'original', 'font_size' => 56, 'design' => ['auto_size' => '0', 'divider' => 'star'], 'switch_to' => 'text',
    ]);
    // «محرّر النص» sends no design: the earlier choices must survive the trip back.
    $this->actingAs($owner)->put(route('quotes.redraft', $quote), ['content' => 'نص قصير', 'kind' => 'original', 'from_draft' => 1, 'switch_to' => 'card']);

    $this->actingAs($owner)->get(route('quotes.edit', [$quote, 'as' => 'card', 'draft' => 1]))
        ->assertSee('name="font_size" value="56"', false)
        ->assertSee('name="design[auto_size]" value="0"', false)
        ->assertSee('name="design[divider]" value="star" class="sr-only" checked', false);
});

test('switching an empty form just opens the other view, with no «required» error', function () {
    $this->actingAs(User::factory()->create())
        ->post(route('quotes.draft'), ['_form' => 'studio', 'content' => '', 'switch_to' => 'text'])
        ->assertRedirect(route('quotes.create', ['as' => 'text']))
        ->assertSessionHasNoErrors();
});

test('switching a library pick keeps the verse locked and the choices made', function () {
    $this->seed(LibrarySeeder::class);
    $verse = Verse::first();
    $reader = User::factory()->create();

    $this->actingAs($reader)
        ->post(route('quotes.draft'), ['_form' => 'studio', 'verses' => [$verse->id], 'font' => 'mirza', 'is_public' => 0, 'switch_to' => 'text'])
        ->assertRedirect(route('quotes.create', ['draft' => 1, 'as' => 'text', 'verses' => [$verse->id]]));

    $this->actingAs($reader)->get(route('quotes.create', ['draft' => 1, 'as' => 'text', 'verses' => [$verse->id]]))
        ->assertOk()->assertSee('من المكتبة · يُنشر كما هو')->assertSee(e($verse->sadr))
        ->assertSee('name="font" value="mirza" class="sr-only" checked', false)
        ->assertSee('name="is_public" value="0" class="sr-only" checked', false);
});

test('saving from the editor lands on the quote with a confirmation, and «عدّل النص» opens the editor away from home', function () {
    $quote = Quote::factory()->for($owner = User::factory()->create())->create(['presentation' => 'text', 'content' => 'نص قديم', 'is_original' => true]);

    $this->actingAs($owner)->from(route('quotes.edit', $quote))
        ->put(route('edit_quote', $quote), ['_form' => 'editor', 'content' => 'نص جديد', 'kind' => 'original', 'presentation' => 'text'])
        ->assertRedirect(route('quotes.show', $quote))
        ->assertSessionHas('toast', 'حُفظت تعديلاتك.');

    $this->actingAs($owner)->get(route('quotes.show', $quote))->assertSee(e(route('quotes.edit', [$quote, 'as' => 'text'])), false);
});
