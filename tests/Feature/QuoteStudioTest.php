<?php

use App\Models\Category;
use App\Models\Quote;
use App\Models\User;

function designedQuote(array $overrides = []): array
{
    return array_merge([
        'content' => 'العلم في الصغر كالنقش على الحجر',
        'category_id' => Category::where('slug', 'hikma')->value('id'),
        'author' => 'مثل عربي',
        'book' => 'مجمع الأمثال',
        'story' => 'سمعته من جدّي.',
        'font' => 'ruqaa',
        'bg_color' => '#1E2533',
        'text_color' => '#EDE6D6',
        'highlight_color' => '#D9B97A',
        'highlight_style' => 'color',
        'highlights' => [0, 1],
        'texture' => '0',
        'ratio' => 'square',
        'font_size' => 40,
    ], $overrides);
}

test('the five categories exist after migrating', function () {
    expect(Category::orderBy('id')->pluck('name')->all())->toBe(['حكمة', 'شعر', 'أدب', 'فلسفة', 'تأمل']);
});

test('logged-in users can open رواق الحرف', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('quotes.create'))
        ->assertOk()
        ->assertSee('رواق الحـــرف');
});

test('guests are sent to log in from رواق الحرف', function () {
    $this->get(route('quotes.create'))->assertRedirect(route('login'));
});

test('a designed quote is stored with all its design fields', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('store_quote'), designedQuote())
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('home'));

    $quote = Quote::sole();

    expect($quote->user_id)->toBe($user->id)
        ->and($quote->category->slug)->toBe('hikma')
        ->and($quote->font)->toBe('ruqaa')
        ->and($quote->highlights)->toBe([0, 1])
        ->and($quote->texture)->toBeFalse()
        ->and($quote->ratio)->toBe('square')
        ->and($quote->font_size)->toBe(40);
});

test('a quick share without design fields uses the defaults', function () {
    $this->actingAs(User::factory()->create())
        ->post(route('store_quote'), ['content' => 'اقتباس سريع']);

    $quote = Quote::sole()->fresh();

    expect($quote->font)->toBe('amiri')
        ->and($quote->ratio)->toBe('wide')
        ->and($quote->texture)->toBeTrue()
        ->and($quote->bg_color)->toBe('#E8E1D5');
});

test('invalid design values are rejected', function (string $field, mixed $value) {
    $this->actingAs(User::factory()->create())
        ->post(route('store_quote'), designedQuote([$field => $value]))
        ->assertSessionHasErrors($field);

    expect(Quote::count())->toBe(0);
})->with([
    'colour not hex' => ['bg_color', 'red'],
    'short hex' => ['text_color', '#FFF'],
    'unknown font' => ['font', 'comic-sans'],
    'unknown ratio' => ['ratio', 'panorama'],
    'font too small' => ['font_size', 10],
    'font too big' => ['font_size', 80],
    'unknown highlight style' => ['highlight_style', 'underline'],
    'unknown category' => ['category_id', 999],
    'story too long' => ['story', str_repeat('ح', 501)],
    'author too long' => ['author', str_repeat('a', 101)],
]);

test('highlight indexes past the last word are dropped', function () {
    $this->actingAs(User::factory()->create())
        ->post(route('store_quote'), designedQuote(['content' => 'ثلاث كلمات فقط', 'highlights' => [1, 2, 9]]));

    expect(Quote::sole()->highlights)->toBe([1, 2]);
});

test('adjacent highlighted words merge into one piece', function () {
    $quote = new Quote(['content' => 'العلم  نور   والجهل ظلام', 'highlights' => [0, 1, 3]]);

    expect($quote->contentPieces())->toBe([
        ['text' => 'العلم نور', 'highlighted' => true],
        ['text' => 'والجهل', 'highlighted' => false],
        ['text' => 'ظلام', 'highlighted' => true],
    ]);
});

test('the home page renders the design and one continuous highlight', function () {
    Quote::factory()->create(designedQuote());

    $this->get(route('home'))
        ->assertSee('<span class="hl">العلم في</span>', false)
        ->assertSee('--bg: #1E2533', false)
        ->assertSee('Aref Ruqaa')
        ->assertSee('حكمة')
        ->assertSee('له حكاية');
});

test('the owner can redesign their quote', function () {
    $quote = Quote::factory()->create();

    $this->actingAs($quote->user)->get(route('quotes.edit', $quote))->assertOk();

    $this->actingAs($quote->user)
        ->put(route('edit_quote', $quote), designedQuote(['_form' => 'studio']))
        ->assertRedirect(route('home'));

    expect($quote->fresh()->font)->toBe('ruqaa');
});

test('other users cannot open or save the redesign', function () {
    $quote = Quote::factory()->create();
    $intruder = User::factory()->create();

    $this->actingAs($intruder)->get(route('quotes.edit', $quote))->assertForbidden();
    $this->actingAs($intruder)->put(route('edit_quote', $quote), designedQuote())->assertForbidden();

    expect($quote->fresh()->font)->toBe('amiri');
});

test('the quick inline edit keeps the existing design', function () {
    $quote = Quote::factory()->create(designedQuote());

    $this->actingAs($quote->user)
        ->put(route('edit_quote', $quote), ['content' => 'نص جديد قصير']);

    expect($quote->fresh())
        ->content->toBe('نص جديد قصير')
        ->font->toBe('ruqaa')
        ->ratio->toBe('square');
});

test('quoting someone else requires a source or a book (Saudi copyright law, art. 15)', function () {
    $user = User::factory()->create();
    $verse = ['content' => 'بيتٌ لشاعر معاصر أعجبني', 'author' => 'بدر بن عبدالمحسن'];

    $this->actingAs($user)->post(route('store_quote'), $verse)
        ->assertSessionHasErrors(['source' => 'اذكر مصدر الاقتباس (كتاباً أو صحيفة أو رابطاً) ما دمت تنقل كلام غيرك.']);

    $this->actingAs($user)->post(route('store_quote'), [...$verse, 'source' => 'صحيفة الجزيرة'])
        ->assertSessionHasNoErrors();

    $this->actingAs($user)->post(route('store_quote'), [...$verse, 'book' => 'ديوان'])
        ->assertSessionHasNoErrors();

    expect(Quote::count())->toBe(2);
});

test('your own words need no source', function () {
    $this->actingAs(User::factory()->create())
        ->post(route('store_quote'), ['content' => 'كلماتي أنا'])
        ->assertSessionHasNoErrors();
});

test('a web-address source becomes a safe external link; anything else stays plain text', function () {
    $linked = Quote::factory()->create(['author' => 'شاعر', 'source' => 'https://www.al-jazirah.com/2020/poem']);
    $plain = Quote::factory()->create(['author' => 'شاعر', 'source' => 'javascript:alert(1)']);

    $this->get(route('quotes.show', $linked))
        ->assertSee('href="https://www.al-jazirah.com/2020/poem"', false)
        ->assertSee('rel="nofollow ugc noopener noreferrer"', false);

    $this->get(route('quotes.show', $plain))
        ->assertDontSee('href="javascript:', false)
        ->assertSee('javascript:alert(1)');
});
