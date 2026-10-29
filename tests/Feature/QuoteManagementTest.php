<?php

use App\Models\Quote;
use App\Models\User;

test('a logged-in user can share a quote (FR-05)', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('store_quote'), ['content' => 'الصبر مفتاح الفرج'])
        ->assertRedirect(route('home'));

    $this->assertDatabaseHas('quotes', ['user_id' => $user->id, 'content' => 'الصبر مفتاح الفرج']);
});

test('quotes of 3 and 155 characters are accepted', function (int $length) {
    $this->actingAs(User::factory()->create())
        ->post(route('store_quote'), ['content' => str_repeat('ب', $length)])
        ->assertSessionHasNoErrors();

    expect(Quote::count())->toBe(1);
})->with([3, 155]);

test('quotes of 2 or 156 characters are rejected', function (int $length) {
    $this->actingAs(User::factory()->create())
        ->post(route('store_quote'), ['content' => str_repeat('ب', $length)])
        ->assertSessionHasErrors(['content' => 'الاقتباس من ٣ إلى ١٥٥ حرفاً.']);

    expect(Quote::count())->toBe(0);
})->with([2, 156]);

test('guests cannot share a quote', function () {
    $this->post(route('store_quote'), ['content' => 'اقتباس من زائر'])
        ->assertRedirect(route('login'));

    expect(Quote::count())->toBe(0);
});

test('the owner can edit their quote (FR-06)', function () {
    $quote = Quote::factory()->create();

    $this->actingAs($quote->user)
        ->put(route('edit_quote', $quote), ['content' => 'نص معدّل'])
        ->assertRedirect();

    expect($quote->fresh()->content)->toBe('نص معدّل');
});

test('another user cannot edit the quote', function () {
    $quote = Quote::factory()->create(['content' => 'النص الأصلي']);

    $this->actingAs(User::factory()->create())
        ->put(route('edit_quote', $quote), ['content' => 'محاولة تعديل'])
        ->assertForbidden();

    expect($quote->fresh()->content)->toBe('النص الأصلي');
});

test('editing validates the 3–155 length', function () {
    $quote = Quote::factory()->create();

    $this->actingAs($quote->user)
        ->put(route('edit_quote', $quote), ['content' => str_repeat('ب', 156)])
        ->assertSessionHasErrors('content');
});

test('the owner deletes their quote immediately (FR-07)', function () {
    $quote = Quote::factory()->create();

    $this->actingAs($quote->user)
        ->delete(route('delete_quote', $quote))
        ->assertRedirect();

    $this->assertModelMissing($quote);
});

test('another user cannot delete the quote', function () {
    $quote = Quote::factory()->create();

    $this->actingAs(User::factory()->create())
        ->delete(route('delete_quote', $quote))
        ->assertForbidden();

    $this->assertModelExists($quote);
});

test('guests cannot edit or delete quotes', function () {
    $quote = Quote::factory()->create();

    $this->put(route('edit_quote', $quote), ['content' => 'نص جديد'])->assertRedirect(route('login'));
    $this->delete(route('delete_quote', $quote))->assertRedirect(route('login'));

    $this->assertModelExists($quote);
});

test('deleting a user deletes their quotes', function () {
    $quote = Quote::factory()->create();

    $quote->user->delete();

    $this->assertModelMissing($quote);
});

test('only the owner sees edit and delete controls', function () {
    $quote = Quote::factory()->create();

    $this->actingAs($quote->user)->get(route('home'))->assertSee('aria-label="حذف"', false);
    $this->actingAs(User::factory()->create())->get(route('home'))->assertDontSee('aria-label="حذف"', false);
});
