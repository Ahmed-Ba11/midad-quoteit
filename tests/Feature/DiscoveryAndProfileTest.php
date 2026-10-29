<?php

use App\Models\Category;
use App\Models\Quote;
use App\Models\Reply;
use App\Models\User;

test('search matches the text, the author and the book', function () {
    Quote::factory()->create(['content' => 'الصبر مفتاح الفرج']);
    Quote::factory()->create(['content' => 'نص أول', 'author' => 'المتنبي']);
    Quote::factory()->create(['content' => 'نص ثانٍ', 'book' => 'ديوان المعري']);
    Quote::factory()->create(['content' => 'لا علاقة']);

    $this->get(route('home', ['q' => 'الصبر']))->assertSee('الصبر مفتاح الفرج')->assertDontSee('لا علاقة');
    $this->get(route('home', ['q' => 'المتنبي']))->assertSee('نص أول')->assertDontSee('لا علاقة');
    $this->get(route('home', ['q' => 'المعري']))->assertSee('نص ثانٍ')->assertDontSee('لا علاقة');
    $this->get(route('home', ['q' => 'غير موجود']))->assertSee('لا نتائج');
});

test('quotes can be sorted by most liked and most discussed', function () {
    $quiet = Quote::factory()->create(['content' => 'اقتباس هادئ']);
    $liked = Quote::factory()->create(['content' => 'اقتباس محبوب', 'created_at' => now()->subDay()]);
    $discussed = Quote::factory()->create(['content' => 'اقتباس للنقاش', 'created_at' => now()->subDays(2)]);
    $liked->likers()->attach(User::factory()->count(2)->create());
    Reply::factory()->for($discussed)->count(3)->create();

    $this->get(route('home', ['sort' => 'likes', 'q' => 'اقتباس']))
        ->assertSeeInOrder(['اقتباس محبوب', 'اقتباس هادئ']);

    $this->get(route('home', ['sort' => 'replies', 'q' => 'اقتباس']))
        ->assertSeeInOrder(['اقتباس للنقاش', 'اقتباس هادئ']);
});

test('category chips filter the quotes', function () {
    $poetry = Category::where('slug', 'shir')->first();
    Quote::factory()->create(['content' => 'بيت شعر', 'category_id' => $poetry->id]);
    Quote::factory()->create(['content' => 'حكمة نثرية']);

    $this->get(route('home', ['category' => 'shir']))
        ->assertSee('بيت شعر')
        ->assertDontSee('حكمة نثرية');
});

test('unknown filter values are ignored instead of failing', function () {
    Quote::factory()->create(['content' => 'ما زال يظهر']);

    $this->get(route('home', ['sort' => 'random', 'category' => 'nope']))
        ->assertOk()
        ->assertSee('ما زال يظهر');
});

test('the quote of the day is the same all day and shows on the home page', function () {
    Quote::factory()->count(5)->create();

    $first = Quote::ofTheDay();
    expect(Quote::ofTheDay()->id)->toBe($first->id);

    $this->get(route('home'))
        ->assertSee('اقتبـــاس اليوم')
        ->assertSee(route('quotes.show', $first));
});

test('there is no quote of the day without quotes', function () {
    expect(Quote::ofTheDay())->toBeNull();

    $this->get(route('home'))->assertOk()->assertDontSee('اقتبـــاس اليوم');
});

test('a profile shows the user\'s quotes and stats to everyone', function () {
    $user = User::factory()->create(['name' => 'ليلى', 'bio' => 'أحب الشعر']);
    $quote = Quote::factory()->for($user)->create(['content' => 'اقتباس ليلى']);
    $quote->likers()->attach(User::factory()->count(2)->create());
    Reply::factory()->for($quote)->create();

    $this->get(route('profile.show', $user))
        ->assertOk()
        ->assertSee('ليلى')
        ->assertSee('أحب الشعر')
        ->assertSee('اقتباس ليلى')
        ->assertSeeInOrder(['اقتباس', '1', 'إعجاب', '2', 'رد', '1'])
        ->assertDontSee('tab=saved');
});

test('saved quotes are visible only to their owner', function () {
    $user = User::factory()->create();
    $saved = Quote::factory()->create(['content' => 'اقتباس محفوظ']);
    $user->bookmarkedQuotes()->attach($saved);

    $this->actingAs($user)->get(route('profile.show', [$user, 'tab' => 'saved']))
        ->assertSee('اقتباس محفوظ');

    $this->actingAs(User::factory()->create())->get(route('profile.show', [$user, 'tab' => 'saved']))
        ->assertDontSee('اقتباس محفوظ');
});

test('a user can update their bio up to 160 characters', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->patch(route('profile.bio'), ['bio' => 'قارئ نهم'])
        ->assertRedirect(route('profile.show', $user));
    expect($user->fresh()->bio)->toBe('قارئ نهم');

    $this->actingAs($user)->patch(route('profile.bio'), ['bio' => str_repeat('ن', 161)])
        ->assertSessionHasErrors(['bio' => 'النبذة حتى ١٦٠ حرفاً.']);

});

test('guests cannot change a bio', function () {
    $this->patch(route('profile.bio'), ['bio' => 'زائر'])->assertRedirect(route('login'));
});

test('search ignores tashkeel and hamza forms', function () {
    Quote::factory()->create(['content' => 'قفا نبكِ من ذكرى حبيبٍ ومنزلِ']);
    Quote::factory()->create(['content' => 'سافرْ إلى المجد']);

    $this->get(route('home', ['q' => 'قفا نبك']))->assertSee('قفا نبكِ من ذكرى');
    $this->get(route('home', ['q' => 'الى المجد']))->assertSee('سافرْ إلى المجد');
});

test('a search with no results points to the other sections, trusted sites and quoting it yourself', function () {
    $this->get(route('home', ['q' => 'بيت غير موجود']))
        ->assertSee('لا نتائج')
        ->assertSee(route('library.index', ['q' => 'بيت غير موجود']), false)
        ->assertSee('https://ar.wikisource.org/w/index.php?', false)
        ->assertSee('https://www.aldiwan.net/search.php?', false)
        ->assertSee('وجدتَه في مكان آخر؟')
        ->assertSee('rel="noopener noreferrer external"', false);
});
