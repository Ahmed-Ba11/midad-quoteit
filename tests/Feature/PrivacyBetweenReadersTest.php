<?php

use App\Models\Anthology;
use App\Models\Book;
use App\Models\Poet;
use App\Models\Quote;
use App\Models\User;
use Database\Seeders\LibrarySeeder;

/**
 * Several readers on the platform at once: what each of them — and a guest — can and cannot see or do.
 * A private quote must leave no trace anywhere except its owner's corner.
 */
beforeEach(function () {
    $this->owner = User::factory()->create(['name' => 'سارة', 'email' => 'sara.private@example.test']);
    $this->other = User::factory()->create(['name' => 'خالد']);

    $this->secret = Quote::factory()->for($this->owner)->create([
        'content' => 'سطر خاص لا يراه أحد', 'is_public' => false, 'author' => 'كاتب', 'book' => 'كتاب سري', 'source' => 'مصدر',
    ]);
    $this->secret->forceFill(['book_id' => Book::findOrAdd('كتاب سري', 'كاتب', $this->owner)->id])->save();

    $this->open = Quote::factory()->for($this->owner)->create(['content' => 'سطر عام للجميع']);
});

test('a private quote is missing from the home page, search, and quote of the day for everyone else', function () {
    foreach ([null, $this->other, $this->owner] as $viewer) {
        $viewer ? $this->actingAs($viewer) : auth()->logout();

        $this->get(route('home'))->assertSee('سطر عام للجميع')->assertDontSee('سطر خاص لا يراه أحد');
        $this->get(route('home', ['q' => 'سطر خاص']))->assertDontSee('سطر خاص لا يراه أحد');
    }

    for ($day = 0; $day < 10; $day++) {
        $this->travel(1)->days();
        expect(Quote::ofTheDay()?->is_public)->toBeTrue();
    }
});

test('nobody but the owner can open, like, save, reply to or collect a private quote', function () {
    $anthology = Anthology::factory()->for($this->other)->create();

    $this->get(route('quotes.show', $this->secret))->assertForbidden();

    $this->actingAs($this->other);
    $this->get(route('quotes.show', $this->secret))->assertForbidden();
    $this->post(route('quotes.like', $this->secret))->assertForbidden();
    $this->post(route('quotes.bookmark', $this->secret))->assertForbidden();
    $this->post(route('replies.store', $this->secret), ['body' => 'رد'])->assertForbidden();
    $this->post(route('anthologies.toggle', [$anthology, $this->secret]))->assertForbidden();
    $this->post(route('anthologies.store'), ['title' => 'مختارات', 'quote_id' => $this->secret->id])->assertForbidden();

    expect($this->secret->likers()->count())->toBe(0)
        ->and($this->secret->bookmarkers()->count())->toBe(0)
        ->and($this->secret->replies()->count())->toBe(0)
        ->and($anthology->quotes()->count())->toBe(0);

    $this->actingAs($this->owner)->get(route('quotes.show', $this->secret))->assertOk()->assertSee('اقتباس خاص');
});

test("a visitor sees only the public side of someone's corner", function () {
    $this->owner->forceFill(['pinned_quote_id' => $this->secret->id])->save();

    $this->actingAs($this->other)->get(route('profile.show', $this->owner))
        ->assertOk()
        ->assertSee('سطر عام للجميع')
        ->assertDontSee('سطر خاص لا يراه أحد')
        ->assertDontSee('مثبّت في ركنه')
        ->assertDontSee('sara.private@example.test')
        ->assertDontSee('tab=saved')
        ->assertDontSee('tab=books');

    $this->actingAs($this->owner)->get(route('profile.show', $this->owner))
        ->assertSee('سطر خاص لا يراه أحد')->assertSee('خاص بك')->assertSee('مثبّت في ركني');
});

test('private quotes are not counted in what visitors see', function () {
    $this->secret->likers()->attach($this->owner);

    $visitorView = $this->actingAs($this->other)->get(route('profile.show', $this->owner))->viewData('stats');
    $ownerView = $this->actingAs($this->owner)->get(route('profile.show', $this->owner))->viewData('stats');

    expect($visitorView['اقتباس'])->toBe(1)->and($visitorView['إعجاب'])->toBe(0)
        ->and($ownerView['اقتباس'])->toBe(2)->and($ownerView['إعجاب'])->toBe(1);
});

test('a book known only from a private quote does not appear on the shelves', function () {
    $book = Book::where('title', 'كتاب سري')->sole();

    $this->get(route('books.index'))->assertDontSee('كتاب سري');
    $this->get(route('books.index', ['q' => 'سري']))->assertDontSee('كتاب سري');
    $this->get(route('books.show', $book))->assertNotFound();
    $this->actingAs($this->other)->get(route('books.show', $book))->assertNotFound();
    $this->actingAs($this->other)->get(route('quotes.create'))->assertDontSee('كتاب سري');

    $this->actingAs($this->owner)->get(route('books.show', $book))->assertOk()->assertDontSee('سطر خاص لا يراه أحد');
});

test('making a quote private takes it out of other readers\' saved quotes and anthologies', function () {
    $anthology = Anthology::factory()->for($this->other)->create(['title' => 'مختارات خالد']);
    $this->actingAs($this->other)->post(route('quotes.bookmark', $this->open));
    $this->actingAs($this->other)->post(route('anthologies.toggle', [$anthology, $this->open]));

    $this->open->update(['is_public' => false]);

    $this->actingAs($this->other)->get(route('profile.show', [$this->other, 'tab' => 'saved']))->assertDontSee('سطر عام للجميع');
    $this->actingAs($this->other)->get(route('anthologies.show', $anthology))->assertDontSee('سطر عام للجميع');
    $this->get(route('anthologies.show', $anthology))->assertDontSee('سطر عام للجميع');

    // The owner of the anthology can still take it out.
    $this->actingAs($this->other)->post(route('anthologies.toggle', [$anthology, $this->open]))->assertRedirect();
    expect($anthology->quotes()->count())->toBe(0);
});

test('related quotes and the readers strip never show private quotes', function () {
    $lonely = User::factory()->create(['name' => 'قارئ خفي']);
    Quote::factory()->for($lonely)->create(['content' => 'كل ما عنده خاص', 'is_public' => false]);

    $this->get(route('quotes.show', $this->open))->assertDontSee('سطر خاص لا يراه أحد');
    $this->get(route('home'))->assertDontSee('قارئ خفي')->assertSee('سارة');
});

test('a reader can hide their favourite poets from visitors', function () {
    $this->seed(LibrarySeeder::class);
    $poet = Poet::first();
    $this->owner->favoritePoets()->attach($poet);

    $this->get(route('profile.show', [$this->owner, 'tab' => 'poets']))->assertSee($poet->name);

    $this->actingAs($this->owner)->patch(route('profile.bio'), ['show_poets' => '0'])->assertSessionHasNoErrors();
    auth()->logout();

    $this->get(route('profile.show', [$this->owner, 'tab' => 'poets']))->assertDontSee('شعراؤه')->assertSee('سطر عام للجميع');
    $this->actingAs($this->owner)->get(route('profile.show', [$this->owner, 'tab' => 'poets']))->assertSee($poet->name);
});

test('the quick-share form publishes privately when asked, and as words only', function () {
    $this->actingAs($this->other)->post(route('store_quote'), [
        '_form' => 'share', 'content' => 'الصبر مفتاح الفرج', 'kind' => 'anonymous', 'is_public' => '0', 'presentation' => 'text',
    ])->assertSessionHasNoErrors();

    $quote = Quote::latest('id')->first();
    expect($quote->is_public)->toBeFalse()->and($quote->presentation)->toBe('text');

    $this->actingAs($this->other)->get(route('profile.show', $this->other))
        ->assertSee('quote-text-mark', false)->assertSee('الصبر مفتاح الفرج');
});

test('quoting a named poet without a source is refused, whichever form is used', function () {
    $this->actingAs($this->other)->post(route('store_quote'), [
        '_form' => 'share', 'content' => 'بيت لشاعر معاصر', 'kind' => 'quoted', 'author' => 'بدر بن عبدالمحسن',
    ])->assertSessionHasErrors('source');

    $this->actingAs($this->other)->post(route('store_quote'), [
        '_form' => 'share', 'content' => 'بيت لشاعر معاصر', 'kind' => 'quoted', 'author' => 'بدر بن عبدالمحسن', 'source_url' => 'https://example.com',
    ])->assertSessionHasErrors('source');

    expect(Quote::where('content', 'بيت لشاعر معاصر')->exists())->toBeFalse();
});
