<?php

use App\Models\Anthology;
use App\Models\Quote;
use App\Models\User;

test('"صمّمه بطاقة" carries the typed details into the studio without putting them in the URL', function () {
    $reader = User::factory()->create();
    $details = ['_form' => 'share', 'content' => 'جملة كتبتها في النافذة', 'kind' => 'quoted', 'author' => 'شاعر', 'source' => 'ديوانه', 'presentation' => 'card'];

    $response = $this->actingAs($reader)->post(route('quotes.draft'), $details)
        ->assertRedirect(route('quotes.create', ['draft' => 1, 'as' => 'card']));
    expect($response->headers->get('Location'))->not->toContain('جملة');

    $this->actingAs($reader)->get(route('quotes.create', ['draft' => 1, 'as' => 'card']))
        ->assertOk()->assertSee('مرسم الحروف')->assertSee('جملة كتبتها في النافذة')->assertSee('شاعر')->assertSee('ديوانه');

    // Opening the studio some other way never picks up an old draft.
    $this->actingAs($reader)->get(route('quotes.create'))->assertDontSee('جملة كتبتها في النافذة');
});

test('"انشره نصاً" opens the text editor with the details, and publishing forgets the draft', function () {
    $reader = User::factory()->create();

    $this->actingAs($reader)->post(route('quotes.draft'), ['content' => 'سطر من تأليفي', 'kind' => 'original', 'signature' => 'بقلم ريم', 'presentation' => 'text'])
        ->assertRedirect(route('quotes.create', ['draft' => 1, 'as' => 'text']));

    $this->actingAs($reader)->get(route('quotes.create', ['draft' => 1, 'as' => 'text']))
        ->assertOk()->assertSee('محرّر النص')->assertSee('سطر من تأليفي')->assertSee('بقلم ريم');

    $this->actingAs($reader)->post(route('store_quote'), ['content' => 'سطر من تأليفي', 'kind' => 'original', 'presentation' => 'text', 'layout' => 'prose'])
        ->assertSessionHasNoErrors();
    expect(session('quote_draft'))->toBeNull()
        ->and(Quote::sole())->presentation->toBe('text')->layout->toBe('prose');
});

test('the share window checks the details before moving on, and reopens with them on a mistake', function () {
    $this->actingAs(User::factory()->create())->from(route('home'))
        ->post(route('quotes.draft'), ['_form' => 'share', 'content' => 'كلام منقول', 'kind' => 'quoted', 'author' => 'كاتب', 'presentation' => 'text'])
        ->assertRedirect(route('home'))
        ->assertSessionHasErrors('source')
        ->assertSessionHasInput('_form', 'share');

    expect(session('quote_draft'))->toBeNull();
});

test('a draft still needs its text', function () {
    $this->actingAs(User::factory()->create())
        ->post(route('quotes.draft'), ['content' => ''])
        ->assertSessionHasErrors('content');
});

test('"استخدم التصميم" copies the look of a visible quote, never its words', function () {
    $model = Quote::factory()->create(['content' => 'نص صاحب التصميم', 'font' => 'ruqaa', 'bg_color' => '#1E2533', 'story' => 'حكاية خاصة']);
    $hidden = Quote::factory()->create(['font' => 'messiri', 'is_public' => false]);
    $reader = User::factory()->create();

    $this->actingAs($reader)->get(route('quotes.create', ['template' => $model->id]))
        ->assertOk()->assertSee('--bg: #1E2533', false)->assertDontSee('نص صاحب التصميم')->assertDontSee('حكاية خاصة');

    $this->actingAs($reader)->get(route('quotes.create', ['template' => $hidden->id]))
        ->assertOk()->assertDontSee('value="messiri" checked', false);
});

test('"أضف إلى" files the new quote only into an anthology of my own', function () {
    $reader = User::factory()->create();
    $mine = Anthology::factory()->for($reader)->create();
    $someoneElses = Anthology::factory()->create();

    $this->actingAs($reader)->post(route('store_quote'), ['content' => 'إلى مختاراتي', 'anthology_id' => $mine->id]);
    $this->actingAs($reader)->post(route('store_quote'), ['content' => 'إلى مختارات غيري', 'anthology_id' => $someoneElses->id]);

    expect($mine->quotes()->pluck('content')->all())->toBe(['إلى مختاراتي'])
        ->and($someoneElses->quotes()->count())->toBe(0);
});

test('a text quote keeps its font and frame, and unknown frames are refused', function () {
    $reader = User::factory()->create();

    $this->actingAs($reader)->post(route('store_quote'), [
        'content' => 'نص في لوح', 'presentation' => 'text', 'mark_style' => 'arch', 'font' => 'naskh',
    ])->assertSessionHasNoErrors();

    expect(Quote::sole())->mark_style->toBe('arch')->font->toBe('naskh');
    $this->get(route('home'))->assertSee('data-mark="arch"', false)->assertSee('quote-text-arch', false);

    $this->actingAs($reader)->post(route('store_quote'), ['content' => 'نص', 'presentation' => 'text', 'mark_style' => '<script>'])
        ->assertSessionHasErrors('mark_style');
});

test('a like shows straight away in «أعجبني» — for its owner only', function () {
    $reader = User::factory()->create();
    $quote = Quote::factory()->create(['content' => 'سطر أعجبني']);

    $this->actingAs($reader)->post(route('quotes.like', $quote))->assertSessionHas('toast');
    $this->actingAs($reader)->get(route('profile.show', [$reader, 'tab' => 'liked']))->assertSee('سطر أعجبني');

    $this->actingAs(User::factory()->create())->get(route('profile.show', [$reader, 'tab' => 'liked']))
        ->assertDontSee('سطر أعجبني')->assertDontSee('tab=liked');
});
