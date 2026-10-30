<?php

use App\Models\Category;
use App\Models\Quote;
use App\Models\Reply;
use App\Models\User;

test('a user can like a quote and un-like it', function () {
    $user = User::factory()->create();
    $quote = Quote::factory()->create();

    $this->actingAs($user)->from(route('home'))->post(route('quotes.like', $quote))
        ->assertRedirect(route('home').'#quote-'.$quote->id);
    expect($quote->likers()->count())->toBe(1);

    $this->actingAs($user)->post(route('quotes.like', $quote));
    expect($quote->likers()->count())->toBe(0);
});

test('a user can save a quote and remove it', function () {
    $user = User::factory()->create();
    $quote = Quote::factory()->create();

    $this->actingAs($user)->post(route('quotes.bookmark', $quote));
    expect($user->bookmarkedQuotes()->pluck('quotes.id')->all())->toBe([$quote->id]);

    $this->actingAs($user)->post(route('quotes.bookmark', $quote));
    expect($user->bookmarkedQuotes()->count())->toBe(0);
});

test('guests cannot like, save or reply', function () {
    $quote = Quote::factory()->create();

    $this->post(route('quotes.like', $quote))->assertRedirect(route('login'));
    $this->post(route('quotes.bookmark', $quote))->assertRedirect(route('login'));
    $this->post(route('replies.store', $quote), ['body' => 'رد'])->assertRedirect(route('login'));

    expect($quote->likers()->count() + $quote->bookmarkers()->count() + $quote->replies()->count())->toBe(0);
});

test('cards show like and reply counts and the viewer\'s like', function () {
    $user = User::factory()->create();
    $quote = Quote::factory()->create();
    $quote->likers()->attach([$user->id, User::factory()->create()->id]);
    Reply::factory()->for($quote)->count(3)->create();

    $this->actingAs($user)->get(route('home'))
        ->assertSee('aria-label="إلغاء الإعجاب"', false)
        ->assertSee('aria-label="الردود (3)"', false);
});

test('the quote page shows the story and replies to guests', function () {
    $quote = Quote::factory()->create(['story' => 'قرأته في مكتبة جدّي.']);
    Reply::factory()->for($quote)->create(['body' => 'اقتباس جميل جداً']);

    $this->get(route('quotes.show', $quote))
        ->assertOk()
        ->assertSee('حكاية الاقتباس')
        ->assertSee('قرأته في مكتبة جدّي.')
        ->assertSee('اقتباس جميل جداً')
        ->assertSee('سجّل دخولك');
});

test('a logged-in user can reply up to 300 characters', function () {
    $user = User::factory()->create();
    $quote = Quote::factory()->create();

    $this->actingAs($user)->post(route('replies.store', $quote), ['body' => 'ردّ لطيف'])
        ->assertRedirect(route('quotes.show', $quote).'#replies');

    $this->actingAs($user)->post(route('replies.store', $quote), ['body' => str_repeat('ر', 301)])
        ->assertSessionHasErrors(['body' => 'الرد حتى ٣٠٠ حرف.']);

    $this->actingAs($user)->post(route('replies.store', $quote), ['body' => ''])
        ->assertSessionHasErrors('body');

    expect($quote->replies()->sole())
        ->body->toBe('ردّ لطيف')
        ->user_id->toBe($user->id);
});

test('related quotes come from the same author first, then the same category', function () {
    $wisdom = Category::where('slug', 'hikma')->first();
    $quote = Quote::factory()->create(['author' => 'المتنبي', 'category_id' => $wisdom->id]);
    Quote::factory()->create(['content' => 'بيت آخر للمتنبي', 'author' => 'المتنبي']);
    Quote::factory()->create(['content' => 'حكمة من غيره', 'category_id' => $wisdom->id]);
    Quote::factory()->create(['content' => 'لا علاقة له']);

    $this->get(route('quotes.show', $quote))
        ->assertSeeInOrder(['قد يعجبـــك أيضاً', 'بيت آخر للمتنبي', 'من القائل نفسه', 'حكمة من غيره', 'من تصنيف حكمة'])
        ->assertDontSee('لا علاقة له');
});

test('deleting a quote removes its likes, saves and replies', function () {
    $quote = Quote::factory()->create();
    $quote->likers()->attach(User::factory()->create());
    $quote->bookmarkers()->attach(User::factory()->create());
    Reply::factory()->for($quote)->create();

    $this->actingAs($quote->user)
        ->from(route('quotes.show', $quote))
        ->delete(route('delete_quote', $quote))
        ->assertRedirect(route('home'));

    $this->assertDatabaseCount('likes', 0);
    $this->assertDatabaseCount('bookmarks', 0);
    $this->assertDatabaseCount('replies', 0);
});

test('after liking, an off-site referer sends the user home instead', function () {
    $quote = Quote::factory()->create();

    $this->actingAs(User::factory()->create())
        ->withHeader('referer', 'https://evil.example/phish')
        ->post(route('quotes.like', $quote))
        ->assertRedirect(route('home').'#quote-'.$quote->id);

    $this->actingAs(User::factory()->create())
        ->withHeader('referer', 'http://localhost.evil.example/phish')
        ->post(route('quotes.bookmark', $quote))
        ->assertRedirect(route('home').'#quote-'.$quote->id);
});
