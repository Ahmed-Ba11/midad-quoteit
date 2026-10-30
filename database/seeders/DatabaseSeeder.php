<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Category;
use App\Models\Poet;
use App\Models\Quote;
use App\Models\Saying;
use App\Models\User;
use App\Models\Verse;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

/**
 * Demo data for trying the app: 5 readers, ~20 designed quotes (some from the library),
 * likes, bookmarks and replies. Every demo account uses DEMO_PASSWORD.
 */
class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Unique and strong, so browsers do not flag it as a password seen in data breaches.
     */
    public const DEMO_PASSWORD = 'Midad-Demo-2026';

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(LibrarySeeder::class);

        $users = collect([
            ['name' => 'سارة', 'email' => 'sara@midad.test', 'bio' => 'أقرأ الشعر الجاهلي بصوتٍ عالٍ.'],
            ['name' => 'خالد', 'email' => 'khalid@midad.test', 'bio' => 'أجمع الحِكم القصيرة في دفتر صغير.'],
            ['name' => 'نورة', 'email' => 'noura@midad.test', 'bio' => 'المتنبي أولاً، ثم كل شيء.'],
            ['name' => 'Fahad', 'email' => 'fahad@midad.test', 'bio' => null],
            ['name' => 'ريم', 'email' => 'reem@midad.test', 'bio' => 'أحب الخطوط العربية والقهوة.'],
        ])->map(fn (array $user) => User::factory()->create([...$user, 'password' => self::DEMO_PASSWORD]));

        $category = fn (string $slug) => Category::where('slug', $slug)->value('id');
        $palette = fn (string $key) => [
            'bg_color' => config("quotes.palettes.$key.bg"),
            'text_color' => config("quotes.palettes.$key.text"),
            'highlight_color' => config("quotes.palettes.$key.highlight"),
        ];
        $verse = fn (string $start) => Verse::with('work.poet')->where('sadr', 'like', $start.'%')->firstOrFail();
        $fromVerse = fn (Verse $chosen) => [
            'content' => $chosen->asText(),
            'author' => $chosen->work->poet->name,
            'book' => $chosen->work->title,
            'verse_id' => $chosen->id,
            'category_id' => $category('shir'),
        ];
        $fromSaying = function (string $start) use ($category) {
            $saying = Saying::where('text', 'like', $start.'%')->firstOrFail();

            return ['content' => $saying->text, 'author' => $saying->source, 'saying_id' => $saying->id, 'category_id' => $category('hikma')];
        };

        $quotes = [
            [0, [...$fromVerse($verse('على قدرِ أهلِ العزمِ')), ...$palette('navy'), 'font' => 'thmanyah-display', 'highlights' => [0, 1, 2, 3], 'ratio' => 'wide', 'font_size' => 36,
                'story' => 'علّقتها فوق مكتبي أيام الامتحانات، وكلما تعبتُ قرأتها.']],
            [2, [...$fromVerse($verse('الخيلُ والليلُ')), ...$palette('ink'), 'font' => 'naskh', 'highlights' => [9, 10], 'highlight_style' => 'color', 'ratio' => 'square', 'font_size' => 40]],
            [1, [...$fromVerse($verse('ستُبدي لك الأيامُ')), ...$palette('sand'), 'font' => 'amiri', 'highlights' => [0, 1, 2], 'ratio' => 'wide']],
            [4, [...$fromVerse($verse('أمرُّ على الديارِ')), ...$palette('mauve'), 'font' => 'ruqaa', 'ratio' => 'tall', 'font_size' => 38,
                'story' => 'أول بيت حفظته من شعر الغزل، علّمني إياه أبي.']],
            [0, [...$fromVerse($verse('قفا نبكِ')), ...$palette('cocoa'), 'font' => 'thmanyah-text', 'highlights' => [0, 1], 'ratio' => 'wide']],
            [3, [...$fromVerse($verse('دعِ الأيامَ تفعلُ')), ...$palette('sage'), 'font' => 'messiri', 'ratio' => 'square', 'font_size' => 36]],
            [2, [...$fromVerse($verse('ما كلُّ ما يتمنّى')), ...$palette('navy'), 'font' => 'amiri', 'highlights' => [6, 7, 8, 9], 'ratio' => 'wide']],
            [1, [...$fromVerse($verse('لسانُ الفتى')), ...$palette('sand'), 'font' => 'naskh', 'ratio' => 'wide', 'texture' => false]],
            [4, [...$fromVerse($verse('السيفُ أصدقُ')), ...$palette('ink'), 'font' => 'thmanyah-display', 'highlights' => [0, 1, 2], 'ratio' => 'wide']],
            [0, [...$fromSaying('إنَّ من البيانِ'), ...$palette('cocoa'), 'font' => 'reem-kufi', 'ratio' => 'square', 'font_size' => 48, 'highlights' => [2]]],
            [3, [...$fromSaying('الوقتُ كالسيفِ'), ...$palette('sage'), 'font' => 'amiri', 'ratio' => 'wide', 'font_size' => 40]],
            [1, ['content' => 'القراءة سفرٌ لا يحتاج إلى جواز، ولا تنتهي تذكرته.', 'category_id' => $category('adab'), ...$palette('sand'), 'font' => 'thmanyah-text', 'highlights' => [0, 1]]],
            [2, ['content' => 'ليست الحكمة أن تعرف الطريق، بل أن تمشيه وأنت تعرف لماذا.', 'category_id' => $category('falsafa'), ...$palette('navy'), 'font' => 'naskh', 'highlights' => [6, 7]]],
            [4, ['content' => 'كل كتابٍ نقرؤه يترك فينا رفّاً جديداً.', 'category_id' => $category('adab'), ...$palette('mauve'), 'font' => 'messiri', 'ratio' => 'square', 'font_size' => 42]],
            [0, ['content' => 'الصمت أحياناً أبلغ جوابٍ عن سؤالٍ لا يستحق.', 'category_id' => $category('taammul'), ...$palette('ink'), 'font' => 'amiri', 'highlights' => [0], 'highlight_style' => 'color']],
            [3, ['content' => 'Every good book leaves a new shelf inside us.', 'category_id' => $category('taammul'), ...$palette('sand'), 'font' => 'cormorant', 'ratio' => 'wide', 'font_size' => 40]],
            [1, ['content' => 'من لم يتعلّم الصبر، علّمته الأيام إياه بطريقتها.', 'category_id' => $category('hikma'), ...$palette('cocoa'), 'font' => 'thmanyah-display', 'highlights' => [4, 5]]],
            [2, ['content' => 'الفجر لا يتأخر عن موعده، نحن من نتأخر عن رؤيته.', 'category_id' => $category('taammul'), ...$palette('sage'), 'font' => 'ruqaa', 'ratio' => 'tall', 'font_size' => 36]],
            [4, ['content' => 'اكتب ما تحب أن تقرأه، واقرأ ما تتمنى لو كتبته.', 'category_id' => $category('adab'), ...$palette('navy'), 'font' => 'amiri', 'highlights' => [0, 1, 2, 3, 4], 'texture' => false,
                'story' => 'نصيحة من معلّمة اللغة العربية في المتوسطة، وما زلت أعمل بها.']],
            [0, ['content' => 'في كل غربةٍ وطنٌ صغير اسمه الكتاب.', 'category_id' => $category('taammul'), ...$palette('mauve'), 'font' => 'thmanyah-text', 'ratio' => 'square', 'font_size' => 44, 'highlights' => [5, 6]]],
        ];

        $created = collect($quotes)->map(function (array $entry, int $index) use ($users) {
            [$ownerIndex, $attributes] = $entry;
            $quote = new Quote($attributes);
            $quote->user()->associate($users[$ownerIndex]);
            $quote->created_at = now()->subHours((count($attributes) + 20 - $index) * 3);
            $quote->save();

            return $quote;
        });

        $replies = [
            'بيتٌ يُقرأ مرة ويُحفظ للأبد.',
            'أحسنتِ الاختيار، والتصميم أجمل.',
            'هذا ما كنت أحتاج قراءته اليوم.',
            'الإبراز جاء في مكانه تماماً.',
            'ذكّرتني بأيام المدرسة.',
            'كلامٌ يستحق أن يُعلّق على الجدار.',
        ];

        $created->each(function (Quote $quote, int $index) use ($users, $replies) {
            $quote->likers()->attach($users->reject(fn (User $user) => $user->id === $quote->user_id)->take(($index * 3) % 5)->pluck('id'));

            foreach (range(0, ($index * 7) % 3) as $offset) {
                $reply = $quote->replies()->make(['body' => $replies[($index + $offset) % count($replies)]]);
                $reply->user()->associate($users[($index + $offset + 1) % $users->count()]);
                $reply->save();
            }
        });

        $users[0]->bookmarkedQuotes()->attach($created->whereIn('user_id', [$users[2]->id, $users[4]->id])->pluck('id'));
        $users[2]->bookmarkedQuotes()->attach($created->take(4)->pluck('id'));

        // رفوف القرّاء: two lines checked word for word against مقدمة ابن خلدون on Wikisource.
        $muqaddimah = Book::findOrAdd('مقدمة ابن خلدون', 'ابن خلدون', $users[1]);
        $muqaddimah->update(['genre' => 'history']);

        foreach ([[1, 'الظلم مؤذن بخراب العمران', 'ink'], [3, 'المغلوب مولع أبداً بالاقتداء بالغالب', 'sand']] as [$ownerIndex, $content, $paletteKey]) {
            $quote = new Quote([
                'content' => $content, 'author' => 'ابن خلدون', 'book' => $muqaddimah->title,
                'category_id' => $category('falsafa'), 'font' => 'naskh', ...$palette($paletteKey),
            ]);
            $quote->book_id = $muqaddimah->id;
            $quote->user()->associate($users[$ownerIndex])->save();
            $quote->likers()->attach($users->reject(fn (User $user) => $user->id === $quote->user_id)->take(2)->pluck('id'));
        }

        $users[1]->books()->attach($muqaddimah, ['status' => 'read']);
        $users[3]->books()->attach($muqaddimah, ['status' => 'reading']);
        $users[0]->books()->attach($muqaddimah, ['status' => 'want']);

        $this->seedCorners($users, $created);
    }

    /**
     * ركني: colours, a pinned quote, favourite poets, anthologies, own writing and a reader's diwan.
     *
     * @param  Collection<int, User>  $users
     * @param  Collection<int, Quote>  $quotes
     */
    private function seedCorners($users, $quotes): void
    {
        foreach (['navy', 'cocoa', 'mauve', null, 'sage'] as $index => $accent) {
            $users[$index]->update(['accent' => $accent]);
        }

        // بقلمي: the demo quotes without an author are the readers' own lines.
        $quotes->whereNull('author')->whereNull('verse_id')->whereNull('saying_id')
            ->each(fn (Quote $quote) => $quote->update(['is_original' => true]));

        // Words only, between quotation marks; one private line that only its owner sees.
        $quotes->firstWhere('content', 'كل كتابٍ نقرؤه يترك فينا رفّاً جديداً.')?->update(['presentation' => 'text']);
        $quotes->firstWhere('content', 'الفجر لا يتأخر عن موعده، نحن من نتأخر عن رؤيته.')?->update(['presentation' => 'text']);
        $quotes->firstWhere('content', 'الصمت أحياناً أبلغ جوابٍ عن سؤالٍ لا يستحق.')?->update(['is_public' => false]);

        $users[0]->update(['pinned_quote_id' => $quotes->firstWhere('user_id', $users[0]->id)->id]);
        $users[2]->update(['pinned_quote_id' => $quotes->firstWhere('user_id', $users[2]->id)->id]);

        $poets = Poet::pluck('id', 'slug');
        $users[0]->favoritePoets()->attach($poets->only(['imru-al-qays', 'zuhair', 'antara'])->values());
        $users[2]->favoritePoets()->attach($poets->only(['al-mutanabbi', 'al-maarri', 'abu-tammam'])->values());

        $wisdom = $users[0]->anthologies()->create(['title' => 'أبيات الحكمة', 'description' => 'ما أعود إليه حين أحتاج نصيحة.', 'palette' => 'navy']);
        $wisdom->quotes()->attach($quotes->whereNotNull('verse_id')->take(4)->pluck('id'));
        $reading = $users[2]->anthologies()->create(['title' => 'عن القراءة', 'description' => 'كل ما قيل في الكتب والقراءة.', 'palette' => 'sand']);
        $reading->quotes()->attach($quotes->filter(fn (Quote $quote) => str_contains($quote->content, 'كتاب') || str_contains($quote->content, 'القراءة'))->pluck('id'));
        $users[2]->anthologies()->create(['title' => 'مسودّات', 'palette' => 'ink', 'is_public' => false]);

        // دواوين القرّاء: two verses checked against نونية ابن زيدون on Wikisource (ابن زيدون died 1071, public domain).
        $diwan = $users[4]->diwans()->create([
            'poet_name' => 'ابن زيدون', 'poet_death_year' => 1071, 'title' => 'من نونيّة ابن زيدون', 'color' => 'mauve', 'is_public' => true,
            'about' => 'قالها في ولّادة بنت المستكفي بعد الفراق، وهي من أشهر قصائد الأندلس.',
            'source' => 'https://ar.wikisource.org/wiki/'.rawurlencode('أضحى التنائي بديلا عن تدانينا'),
        ]);
        $diwan->verses()->createMany([
            ['sadr' => 'أضْحَى التّنائي بَديلاً عنْ تَدانِينَا', 'ajuz' => 'وَنَابَ عَنْ طيبِ لُقْيانَا تجافينَا', 'note' => 'مطلع القصيدة'],
            ['sadr' => 'أَنَّ الزَمانَ الَّذي مازالَ يُضحِكُنا', 'ajuz' => 'أُنساً بِقُربِهِمُ قَد عادَ يُبكينا'],
        ]);
    }
}
