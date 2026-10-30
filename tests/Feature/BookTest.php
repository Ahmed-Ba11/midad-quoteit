<?php

use App\Models\Book;
use App\Models\Quote;
use App\Models\User;
use App\Models\Verse;
use Database\Seeders\LibrarySeeder;

test('quoting from a book adds the book once, whatever the spelling of hamza or tashkeel', function () {
    $reader = User::factory()->create();

    $this->actingAs($reader)->post(route('store_quote'), [
        'content' => 'جملة أولى من الكتاب', 'author' => 'طه حسين', 'book' => 'الأيام', 'page' => 12, 'book_genre' => 'novel',
    ])->assertSessionHasNoErrors();

    $this->actingAs(User::factory()->create())->post(route('store_quote'), [
        'content' => 'جملة ثانية من الكتاب', 'author' => 'طه حسين', 'book' => 'الايام',
    ])->assertSessionHasNoErrors();

    $book = Book::sole();
    expect($book->title)->toBe('الأيام')
        ->and($book->genre)->toBe('novel')
        ->and($book->quotes()->count())->toBe(2)
        ->and(Quote::first()->page)->toBe(12);
});

test('library verses do not create reader books', function () {
    $this->seed(LibrarySeeder::class);

    $this->actingAs(User::factory()->create())
        ->post(route('store_quote'), ['verses' => [Verse::first()->id]]);

    expect(Book::count())->toBe(0);
});

test('invalid page numbers and genres are rejected', function (array $input, string $field) {
    $this->actingAs(User::factory()->create())
        ->post(route('store_quote'), ['content' => 'اقتباس من كتاب', 'book' => 'كتاب', ...$input])
        ->assertSessionHasErrors($field);
})->with([
    'page zero' => [['page' => 0], 'page'],
    'page too big' => [['page' => 10000], 'page'],
    'unknown genre' => [['book_genre' => 'comics'], 'book_genre'],
]);

test('the books page shows the most quoted books and finds a book without tashkeel', function () {
    $book = Book::factory()->create(['title' => 'مقدّمة ابن خلدون', 'author' => 'ابن خلدون', 'match_key' => Book::matchKey('مقدّمة ابن خلدون', 'ابن خلدون')]);
    Quote::factory()->create(['book' => $book->title, 'book_id' => $book->id]);

    $this->get(route('books.index'))->assertOk()->assertSee('الأكثر اقتباساً')->assertSee('مقدّمة ابن خلدون');
    $this->get(route('books.index', ['q' => 'مقدمه']))->assertSee('مقدّمة ابن خلدون');
    $this->get(route('books.index', ['q' => 'كتاب لا وجود له']))->assertSee('لم نجد هذا الكتاب بعد');
});

test('a book page lists its quotes, best liked first', function () {
    $book = Book::factory()->create();
    $loved = Quote::factory()->create(['content' => 'اقتباس محبوب', 'book_id' => $book->id]);
    Quote::factory()->create(['content' => 'اقتباس عادي', 'book_id' => $book->id, 'created_at' => now()->addMinute()]);
    $loved->likers()->attach(User::factory()->count(3)->create());

    $this->get(route('books.show', $book))
        ->assertOk()
        ->assertSeeInOrder(['أجمل ما اقتبسه القرّاء', 'اقتباس محبوب', 'اقتباس عادي']);
});

test('a reader can shelve a book, change its status and remove it', function () {
    $reader = User::factory()->create();
    $book = Book::factory()->create();
    Quote::factory()->create()->forceFill(['book_id' => $book->id])->save();

    $this->actingAs($reader)->post(route('books.shelf', $book), ['status' => 'reading']);
    expect($reader->books()->sole()->pivot->status)->toBe('reading');

    $this->actingAs($reader)->post(route('books.shelf', $book), ['status' => 'read']);
    expect($reader->books()->sole()->pivot->status)->toBe('read');

    $this->actingAs($reader)->post(route('books.shelf', $book), ['status' => '']);
    expect($reader->books()->count())->toBe(0);

    $this->actingAs($reader)->post(route('books.shelf', $book), ['status' => 'stolen'])->assertSessionHasErrors('status');
});

test('guests cannot change a shelf', function () {
    $this->post(route('books.shelf', Book::factory()->create()), ['status' => 'read'])->assertRedirect(route('login'));
});

test('my shelf (كتبي) is visible only to its owner', function () {
    $reader = User::factory()->create();
    $book = Book::factory()->create(['title' => 'كتاب خاص']);
    $reader->books()->attach($book, ['status' => 'want']);

    $this->actingAs($reader)->get(route('profile.show', [$reader, 'tab' => 'books']))
        ->assertSee('أريد قراءته')
        ->assertSee('كتاب خاص');

    $this->actingAs(User::factory()->create())->get(route('profile.show', [$reader, 'tab' => 'books']))
        ->assertDontSee('كتاب خاص');
});

test('"add a quote from this book" opens the studio with the book filled in', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('quotes.create', ['book' => 'الأيام', 'author' => 'طه حسين']))
        ->assertOk()
        ->assertSee('value="الأيام"', false)
        ->assertSee('value="طه حسين"', false);
});
