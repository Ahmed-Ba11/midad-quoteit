<?php

use App\Models\Poet;
use App\Models\Quote;
use App\Models\Reply;
use App\Models\Saying;
use App\Models\User;
use App\Models\Verse;
use App\Models\Work;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\LibrarySeeder;

beforeEach(function () {
    $this->seed(LibrarySeeder::class);
});

test('the seeder loads every poet, the seven muallaqat and the sayings, and can run twice', function () {
    $this->seed(LibrarySeeder::class);

    expect(Poet::count())->toBe(14)
        ->and(Work::where('type', 'muallaqa')->count())->toBe(7)
        ->and(Saying::count())->toBe(10)
        ->and(Verse::count())->toBeGreaterThanOrEqual(90);
});

test('every verse has both hemistichs and a known occasion', function () {
    $occasions = array_keys(config('library.occasions'));

    Verse::all()->each(function (Verse $verse) use ($occasions) {
        expect(trim($verse->sadr))->not->toBe('')
            ->and(trim($verse->ajuz))->not->toBe('');

        if ($verse->occasion !== null) {
            expect($occasions)->toContain($verse->occasion);
        }
    });
});

test('the library page shows the shelf, the arcade, the occasions and the sayings', function () {
    $this->get(route('library.index'))
        ->assertOk()
        ->assertSee('خزانـــة الأدب')
        ->assertSee('المعلقات السبع')
        ->assertSee('دواوين العرب')
        ->assertSee('رواق الشعـــراء')
        ->assertSee('لكلّ مقامٍ مقـــال')
        ->assertSee('جدار المأثـــورات')
        ->assertSee('قفا نبكِ من ذكرى حبيبٍ ومنزلِ')
        ->assertSee('CC BY-SA');
});

test('library search finds verses by text and by poet', function () {
    $this->get(route('library.index', ['q' => 'جليس']))
        ->assertSee('وخيرُ جليسٍ في الزمانِ كتابُ');

    $this->get(route('library.index', ['q' => 'الخنساء']))
        ->assertSee('وإنَّ صخراً لتأتمُّ الهداةُ به');

    $this->get(route('library.index', ['q' => 'كلمة غير موجودة']))
        ->assertSee('لا أبيات تطابق بحثك');
});

test('work and poet pages list their verses', function () {
    $this->get(route('library.work', 'muallaqa-zuhair'))
        ->assertOk()
        ->assertSee('معلقة زهير بن أبي سلمى')
        ->assertSee('سئمتُ تكاليفَ الحياةِ ومَن يعشْ');

    $this->get(route('library.poet', 'al-mutanabbi'))
        ->assertOk()
        ->assertSee('ديوان المتنبي')
        ->assertSee('على قدرِ أهلِ العزمِ تأتي العزائمُ');

    $this->get('/library/poets/nobody')->assertNotFound();
});

test('رواق الحرف opens with the chosen verses locked and attributed', function () {
    $verse = Verse::where('sadr', 'like', 'على قدرِ أهلِ العزمِ%')->first();

    $this->actingAs(User::factory()->create())
        ->get(route('quotes.create', ['verses' => [$verse->id]]))
        ->assertOk()
        ->assertSee('من المكتبة · يُنشر كما هو')
        ->assertSee('اختر بيتاً آخر')
        ->assertSee('<input type="hidden" name="verses[]" value="'.$verse->id.'">', false);
});

test('publishing a library verse ignores any edited text and attributes it automatically', function () {
    $verse = Verse::where('sadr', 'like', 'على قدرِ أهلِ العزمِ%')->first();

    $this->actingAs(User::factory()->create())
        ->post(route('store_quote'), ['verses' => [$verse->id], 'content' => 'نص مُعدّل', 'author' => 'أنا'])
        ->assertSessionHasNoErrors();

    expect(Quote::sole())
        ->content->toBe($verse->sadr."\n".$verse->ajuz)
        ->author->toBe('المتنبي')
        ->book->toBe('ديوان المتنبي')
        ->verse_id->toBe($verse->id);
});

test('two verses are joined in order and longer text is rejected', function () {
    [$first, $second] = Verse::whereHas('work', fn ($work) => $work->where('slug', 'diwan-majnun-layla'))->orderBy('sort_order')->take(2)->get();

    $this->actingAs(User::factory()->create())
        ->post(route('store_quote'), ['verses' => [$first->id, $second->id]])
        ->assertSessionHasNoErrors();

    expect(Quote::sole()->content)->toBe($first->asText()."\n".$second->asText());

    $long = Verse::all()->sortByDesc(fn (Verse $verse) => mb_strlen($verse->asText()))->take(2)->values();
    if (mb_strlen($long[0]->asText()."\n".$long[1]->asText()) > 155) {
        $this->actingAs(User::factory()->create())
            ->post(route('store_quote'), ['verses' => $long->pluck('id')->all()])
            ->assertSessionHasErrors('content');
    }
});

test('a saying is published with its source', function () {
    $saying = Saying::where('text', 'like', 'خيرُ الكلامِ%')->first();

    $this->actingAs(User::factory()->create())
        ->post(route('store_quote'), ['saying_id' => $saying->id]);

    expect(Quote::sole())
        ->content->toBe($saying->text)
        ->author->toBe('مثل عربي')
        ->saying_id->toBe($saying->id);
});

test('the text of a library quote cannot be changed when editing it', function () {
    $verse = Verse::first();
    $user = User::factory()->create();
    $this->actingAs($user)->post(route('store_quote'), ['verses' => [$verse->id]]);
    $quote = Quote::sole();

    $this->actingAs($user)
        ->put(route('edit_quote', $quote), ['content' => 'محاولة تغيير النص', '_form' => 'studio', 'font' => 'naskh'])
        ->assertSessionHasNoErrors();

    expect($quote->fresh())
        ->content->toBe($verse->asText())
        ->font->toBe('naskh');
});

test('verse lines render on separate lines in the card', function () {
    $verse = Verse::first();
    $this->actingAs(User::factory()->create())->post(route('store_quote'), ['verses' => [$verse->id]]);

    $this->get(route('home', ['q' => 'نبك']))
        ->assertSee(e($verse->sadr), false)
        ->assertSee('<br>', false)
        ->assertSee(e($verse->ajuz), false);
});

test('the demo seeder builds a complete, browsable dataset', function () {
    $this->seed(DatabaseSeeder::class);

    expect(User::count())->toBe(5)
        ->and(Quote::count())->toBe(20)
        ->and(Quote::whereNotNull('verse_id')->count())->toBeGreaterThan(5)
        ->and(Reply::count())->toBeGreaterThan(10);

    $this->get(route('home'))->assertOk()->assertSee('اقتبـــاس اليوم');
    $this->get(route('library.index'))->assertOk();
});
