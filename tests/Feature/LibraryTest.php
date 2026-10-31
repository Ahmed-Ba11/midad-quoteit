<?php

use App\Models\Poet;
use App\Models\Quote;
use App\Models\Reply;
use App\Models\Saying;
use App\Models\User;
use App\Models\Verse;
use App\Models\Work;
use App\Support\Aldiwan;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DemoSeeder;
use Database\Seeders\LibrarySeeder;

beforeEach(function () {
    $this->seed(LibrarySeeder::class);
});

test('the seeder loads every poet, the seven muallaqat and the sayings, and can run twice', function () {
    $this->seed(LibrarySeeder::class);

    expect(Poet::count())->toBe(14)
        ->and(Work::where('type', 'muallaqa')->count())->toBe(7)
        ->and(Saying::count())->toBe(29)
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
        ->assertSee('لم نجد «كلمة غير موجودة» على رفوف الخزانة')
        ->assertSee('https://www.aldiwan.net/search.php?q=', false)
        ->assertSee('rel="noopener noreferrer external"', false)
        ->assertSee('شاعرك ليس هنا؟');
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

test('مرسم الحروف opens with the chosen verses locked and attributed', function () {
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
        ->post(route('store_quote'), ['saying' => $saying->id]);

    expect(Quote::sole())
        ->content->toBe($saying->text)
        ->author->toBe('قول مأثور')
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

test('verse lines render as صدر then عجز in the card', function () {
    $verse = Verse::first();
    $this->actingAs(User::factory()->create())->post(route('store_quote'), ['verses' => [$verse->id]]);

    $this->get(route('home', ['q' => 'نبك']))
        ->assertSeeInOrder(['data-mode="verse"', 'hemi sadr', e($verse->sadr), 'hemi ajuz', e($verse->ajuz)], false);
});

test('the demo seeder builds a complete, browsable dataset', function () {
    $this->seed(DemoSeeder::class);

    expect(User::count())->toBe(5)
        ->and(Quote::count())->toBe(22)
        ->and(Quote::whereNotNull('verse_id')->count())->toBeGreaterThan(5)
        ->and(Reply::count())->toBeGreaterThan(10);

    $this->get(route('home'))->assertOk()->assertSee('اقتبـــاس اليوم');
    $this->get(route('library.index'))->assertOk();
});

test('the public site starts with the library only: no demo readers, quotes or made-up numbers', function () {
    app()->detectEnvironment(fn () => 'production');
    app(DatabaseSeeder::class)->setContainer(app())->__invoke();
    app()->detectEnvironment(fn () => 'testing');

    expect(User::count())->toBe(0)
        ->and(Quote::count())->toBe(0)
        ->and(Verse::count())->toBeGreaterThan(100);

    $this->get(route('home'))->assertOk()
        ->assertSee('بيتُ اليوم')
        ->assertDontSee('اقتبـــاس اليوم')
        ->assertDontSee('>0</dd>', false)
        ->assertSee('بيتاً موثّقاً');
});

test('a quote cannot be linked to the library by sending verse_id or saying_id directly', function () {
    $verse = Verse::first();
    $saying = Saying::first();

    $this->actingAs(User::factory()->create())
        ->post(route('store_quote'), ['content' => 'نص مزيّف ينتحل بيتاً', 'author' => 'المتنبي', 'source' => 'مصدر ما', 'verse_id' => $verse->id, 'saying_id' => $saying->id]);

    expect(Quote::sole())
        ->verse_id->toBeNull()
        ->saying_id->toBeNull()
        ->isFromLibrary()->toBeFalse();
});

test('every poet links to Wikisource and الديوان, and every muallaqa to its own page', function () {
    Poet::all()->each(function (Poet $poet) {
        expect($poet->wikisource_url)->toStartWith('https://ar.wikisource.org/wiki/')
            ->and($poet->aldiwan_url)->toStartWith('https://www.aldiwan.net/cat-poet-');
    });

    Work::where('type', 'muallaqa')->get()->each(
        fn (Work $work) => expect($work->wikisource_url)->toStartWith('https://ar.wikisource.org/wiki/')
    );
});

test('verse source links only point to Wikisource poem pages', function () {
    $linked = Verse::whereNotNull('source_url')->pluck('source_url');

    expect($linked->count())->toBeGreaterThan(Verse::count() / 2);
    $linked->each(fn (string $url) => expect($url)->toStartWith('https://ar.wikisource.org/wiki/'));
});

test('diwan and poet pages offer "read the full diwan" links that open safely in a new tab', function () {
    $this->get(route('library.work', 'diwan-al-mutanabbi'))
        ->assertOk()
        ->assertSee('أكمل قراءة الديوان')
        ->assertSee('https://www.aldiwan.net/cat-poet-Mutanabi', false)
        ->assertSee('rel="noopener noreferrer external"', false)
        ->assertSee('target="_blank"', false);

    $this->get(route('library.work', 'muallaqa-zuhair'))->assertSee('اقرأ المعلقة كاملة');

    $this->get(route('library.poet', 'al-khansa'))
        ->assertSee('https://www.aldiwan.net/cat-poet-al-khansa', false);
});

test('each verse is read on in الديوان, by a search for its first hemistich', function () {
    $verse = Work::where('slug', 'diwan-al-mutanabbi')->sole()->verses()->first();

    $this->get(route('library.work', 'diwan-al-mutanabbi'))
        ->assertSee('أكمل القصيدة في الديوان')
        ->assertSee(e(Aldiwan::verse($verse->sadr)), false)
        ->assertDontSee('القصيدة كاملة');
});

test('every verse has an explanation, and the diwan page shows it with Lisan al-Arab lookups', function () {
    expect(Verse::whereNull('explanation')->count())->toBe(0);

    $this->get(route('library.work', 'muallaqa-imru-al-qays'))
        ->assertOk()
        ->assertSee('شرح البيت ومعاني كلماته')
        ->assertSee('يخاطب الشاعر صاحبَيه')
        ->assertSee('غريب الألفاظ')
        ->assertSee('منقطعه حيث يدقّ')
        ->assertSee('https://ar.wikisource.org/w/index.php?search=', false);
});

test('every epigraph chosen for a page is exactly one verified verse from the library', function () {
    $this->seed(LibrarySeeder::class);
    $verses = Verse::pluck('sadr')->map(fn (string $sadr) => Verse::normalizeArabic($sadr));

    foreach (config('library.epigraphs') as $page => $starts) {
        foreach ($starts as $start) {
            expect($verses->filter(fn (string $verse) => str_starts_with($verse, $start)))
                ->toHaveCount(1, "«{$start}» on {$page}");
        }
    }
});

test('pages show an epigraph from the verses chosen for them', function () {
    $this->seed(LibrarySeeder::class);
    $chosen = fn (string $page) => Verse::get()->filter(fn (Verse $verse) => collect(config("library.epigraphs.$page"))
        ->contains(fn (string $start) => str_starts_with(Verse::normalizeArabic($verse->sadr), $start)));

    $login = $this->get(route('login'))->assertOk()->getContent();
    expect($chosen('return')->contains(fn (Verse $verse) => str_contains($login, $verse->sadr)))->toBeTrue();

    $signup = $this->get(route('signup'))->assertOk()->getContent();
    expect($chosen('beginning')->contains(fn (Verse $verse) => str_contains($signup, $verse->sadr)))->toBeTrue();
});

test('modern Saudi poets appear with one or two lines each, a source, and a way to read on', function () {
    foreach (config('library.peninsula_voices') as $voice) {
        expect(count($voice['verses']))->toBeLessThanOrEqual(2)
            ->and(count($voice['sources']))->toBeGreaterThanOrEqual(2)
            ->and($voice['read_more'])->toStartWith('https://');
    }

    $this->get(route('library.index'))
        ->assertSee('أصواتٌ من الجزيرة')
        ->assertSee('فوق هام السحب وإن كنتِ ثرى')
        ->assertSee('أكمل قراءتك في الديوان')
        ->assertSee('الحقوق محفوظة لأصحابها');
});

test('quoting a Saudi poet from the library pre-fills the poet, the poem and the link', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('quotes.create', ['voice' => 'ghazi', 'line' => 0]))
        ->assertOk()
        ->assertSee('أَجَلْ نَحْنُ الْحِجَازُ وَنَحْنُ نَجْدُ')
        ->assertSee('غازي القصيبي')
        ->assertSee('https://www.aldiwan.net/poem61097.html', false);

    $this->actingAs(User::factory()->create())
        ->get(route('quotes.create', ['voice' => 'nobody', 'line' => 9]))
        ->assertOk()->assertDontSee('غازي القصيبي');
});

test('every saying shows its meaning, and sourced ones link to the page that documents them', function () {
    $this->seed(LibrarySeeder::class);
    $documented = Saying::whereNotNull('source_url')->first();

    expect(Saying::whereNull('meaning')->count())->toBe(0)
        ->and(Saying::where('source', 'like', '%حديث%')->count())->toBe(0);

    $this->get(route('library.index'))->assertSee('اعرض كل المأثورات')->assertSee($documented->source_url, false);
});
