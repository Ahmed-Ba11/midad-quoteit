<?php

namespace App\Http\Controllers;

use App\Http\Requests\QuoteRequest;
use App\Models\Book;
use App\Models\Category;
use App\Models\DiwanVerse;
use App\Models\Quote;
use App\Models\Saying;
use App\Models\User;
use App\Models\Verse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class QuoteController extends Controller
{
    /**
     * How many public quotes the home page needs before «اقتباس اليوم» replaces «بيت اليوم»,
     * and before it shows how many quotes and readers there are.
     */
    public const QUOTE_OF_THE_DAY_FROM = 10;

    /**
     * Display all quotes, 12 per page, with search, sort and category filter.
     */
    public function index(Request $request): View
    {
        $categories = Category::orderBy('id')->get();

        // Unknown values are ignored rather than rejected, so a bad link still shows the quotes.
        $filters = [
            'q' => mb_substr(trim($request->string('q')), 0, 100),
            'sort' => in_array($request->query('sort'), ['likes', 'replies'], true) ? $request->query('sort') : 'latest',
            'category' => $categories->firstWhere('slug', $request->query('category'))?->slug,
            'original' => $request->query('original') === '1',
        ];

        $quotes = Quote::public()->withCardData($request->user())
            ->search($filters['q'])
            ->when($filters['category'], fn ($query, string $slug) => $query->whereRelation('category', 'slug', $slug))
            ->when($filters['original'], fn ($query) => $query->where('is_original', true))
            ->sortBy($filters['sort'])
            ->paginate(12)
            ->withQueryString();

        $isFiltering = $filters['q'] !== '' || $filters['category'] !== null || $filters['original'];
        $isFrontPage = ! $isFiltering && $quotes->onFirstPage();

        // The hero's numbers move slowly; counting them once every few minutes is plenty.
        $counts = $isFrontPage ? Cache::remember('home-counts', now()->addMinutes(5), fn () => [
            'quotes' => Quote::public()->count(),
            'readers' => User::whereHas('quotes', fn ($quotes) => $quotes->public())->count(),
            'verses' => Verse::count(),
        ]) : [];
        // The day's leaf: a reader's quote once there are enough to choose from; until then a verse.
        $hasEnoughQuotes = $isFrontPage && $counts['quotes'] >= self::QUOTE_OF_THE_DAY_FROM;

        return view('quotes.index', [
            'quotes' => $quotes,
            'categories' => $categories,
            'filters' => $filters,
            'isFrontPage' => $isFrontPage,
            'quoteOfTheDay' => $hasEnoughQuotes ? Quote::ofTheDay() : null,
            'verseOfTheDay' => $isFrontPage && ! $hasEnoughQuotes ? Verse::ofTheDay() : null,
            // أقلام رقيم: readers with the most public quotes — only what they chose to make public.
            'pens' => $isFrontPage
                ? User::whereHas('quotes', fn ($quotes) => $quotes->public())
                    ->withCount(['quotes as public_quotes_count' => fn ($quotes) => $quotes->public()])
                    ->orderByDesc('public_quotes_count')->take(8)->get(['id', 'name', 'accent', 'badge', 'bio'])
                : collect(),
            'counts' => $counts,
        ]);
    }

    /**
     * The quote page: big card, story, replies and "قد يعجبك أيضاً".
     */
    public function show(Request $request, Quote $quote): View
    {
        $quote = Quote::withCardData($request->user())->findOrFail($quote->id);
        $quote->load(['replies' => fn ($replies) => $replies->with('user')->oldest()]);

        return view('quotes.show', [
            'quote' => $quote,
            'related' => $this->relatedQuotes($quote, $request),
            // مختاراتي: my anthologies, each marked if it already holds this quote.
            'myAnthologies' => $request->user()?->anthologies()
                ->withExists(['quotes as has_quote' => fn ($quotes) => $quotes->whereKey($quote->id)])
                ->latest('updated_at')->get() ?? collect(),
            // Public anthologies by other readers that include this quote.
            'inAnthologies' => $quote->anthologies()->public()->with('user')
                ->when($request->user(), fn ($others, $viewer) => $others->whereNot('anthologies.user_id', $viewer->id))
                ->latest('anthologies.updated_at')->take(6)->get(),
        ]);
    }

    /**
     * Up to 4 suggestions: same author first, then same category — each with the reason it was picked.
     *
     * @return Collection<int, array{quote: Quote, reason: string}>
     */
    private function relatedQuotes(Quote $quote, Request $request): Collection
    {
        $limit = 4;
        $sameAuthor = $quote->author
            ? Quote::public()->withCardData($request->user())
                ->where('author', $quote->author)
                ->whereKeyNot($quote->id)
                ->latest()->take($limit)->get()
            : collect();

        $sameCategory = $quote->category_id && $sameAuthor->count() < $limit
            ? Quote::public()->withCardData($request->user())
                ->where('category_id', $quote->category_id)
                ->whereKeyNot([$quote->id, ...$sameAuthor->pluck('id')])
                ->latest()->take($limit - $sameAuthor->count())->get()
            : collect();

        return $sameAuthor->map(fn (Quote $related) => ['quote' => $related, 'reason' => 'من القائل نفسه'])
            ->concat($sameCategory->map(fn (Quote $related) => [
                'quote' => $related,
                'reason' => 'من تصنيف '.$quote->category->name,
            ]));
    }

    /**
     * A new quote: «محرّر النص» (?as=text) to publish the words themselves with their format, or
     * مرسم الحروف to design a card. The details typed in «شارك اقتباساً» arrive as the draft (?draft=1),
     * so nothing is asked twice.
     */
    public function create(Request $request): View
    {
        $draft = $request->boolean('draft') && is_array($stored = $request->session()->get('quote_draft')) ? $stored : [];
        $quote = $this->withTemplate($this->draftFromLibrary($request, $draft), $request);
        // A library pick switched between «نص / بطاقة» keeps its locked text, and the reader's other choices.
        $quote->fill(Arr::except($draft, ['content', 'author', 'book', 'source', 'source_url']));
        $asText = $request->query('as', $draft['presentation'] ?? null) === 'text';
        $quote->presentation = $asText ? 'text' : 'card';

        // Back from a library pick goes to the library, wherever the reader arrived from (e.g. sign-in).
        $fromLibrary = $request->has('verses') || $request->has('saying');
        $backUrl = $this->previousOnSite();

        return view($asText ? 'quotes.editor' : 'quotes.studio', [
            'quote' => $quote,
            'draft' => $draft,
            'backUrl' => $fromLibrary && $backUrl === route('home') ? route('library.index') : $backUrl,
            'myAnthologies' => $request->user()->anthologies()->latest('updated_at')->get(['id', 'title']),
            ...$this->formOptions(),
        ]);
    }

    /**
     * A new quote pre-filled from خزانة الأدب (?verses[]=…, up to 2, or ?saying=…).
     * The text is locked; QuoteRequest rebuilds it on the server when publishing.
     */
    private function draftFromLibrary(Request $request, array $draft = []): Quote
    {
        $quote = new Quote;
        $poetry = fn () => Category::where('slug', 'shir')->value('id');
        $verses = Verse::picked($request->query('verses', []));

        if ($verses->isNotEmpty()) {
            $quote->fill([...Verse::quoteText($verses), 'category_id' => $poetry()]);
            $quote->setRelation('libraryVerses', $verses);
        } elseif ($saying = Saying::find($request->query('saying'))) {
            $quote->fill(['content' => $saying->text, 'author' => $saying->attribution()]);
            $quote->setRelation('saying', $saying);
        } elseif (ctype_digit((string) $request->query('diwan_verse'))
            && ($diwanVerse = DiwanVerse::with('diwan')->find((int) $request->query('diwan_verse')))
            && Gate::allows('view', $diwanVerse->diwan)) {
            // A verse from دواوين القرّاء: prefilled with its poet and source, but editable (not verified text).
            $quote->fill([
                'content' => $diwanVerse->asText(),
                'author' => $diwanVerse->diwan->poet_name,
                'source' => $diwanVerse->diwan->source,
                'category_id' => $poetry(),
            ]);
        } elseif (is_array($voice = config('library.peninsula_voices.'.$request->query('voice')))
            && isset($voice['verses'][(int) $request->query('line')])) {
            // «اقتبس هذا البيت» from أصوات من الجزيرة: the line with its poet, poem and where to read it.
            [$sadr, $ajuz] = $voice['verses'][(int) $request->query('line')];
            $quote->fill([
                'content' => $sadr."\n".$ajuz,
                'author' => $voice['poet'],
                'source' => 'قصيدة «'.$voice['poem'].'»',
                'source_url' => $voice['read_more'],
                'category_id' => $poetry(),
            ]);
        } elseif ($draft !== []) {
            // From «شارك اقتباساً»: the words and their details come along, already checked.
            $quote->fill($draft);
        } elseif ($request->filled('book')) {
            // "أضف اقتباساً من هذا الكتاب" on a book page.
            $quote->fill([
                'book' => mb_substr(trim($request->string('book')), 0, 150),
                'author' => mb_substr(trim($request->string('author')), 0, 100) ?: null,
            ]);
        }

        return $quote;
    }

    /**
     * «انشره نصاً» / «صمّمه بطاقة» in «شارك اقتباساً», and switching between the editor and the studio:
     * the details are checked here with the same rules as publishing (so a missing author or source is
     * caught in the window itself), kept in the session — never the URL — and the chosen page opens.
     */
    public function draft(Request $request): RedirectResponse
    {
        $way = $request->input('switch_to', $request->input('presentation')) === 'text' ? 'text' : 'card';
        $library = array_filter($request->only(['verses', 'saying']));

        // «نص / بطاقة» on a form with nothing written yet: there is nothing to carry, so just switch.
        if ($request->has('switch_to') && $request->isNotFilled('content') && $library === []) {
            return redirect()->route('quotes.create', ['as' => $way]);
        }

        // Resolving the form request validates it, exactly as when publishing.
        $previous = $request->boolean('from_draft') ? Arr::except((array) $request->session()->get('quote_draft'), ['quote_id']) : [];
        $request->session()->put('quote_draft', $this->draftFrom(app(QuoteRequest::class), $way, $previous));

        return redirect()->route('quotes.create', ['draft' => 1, 'as' => $way, ...$library]);
    }

    /**
     * «نص / بطاقة» on a saved quote: the unsaved choices come along to the other view, nothing is saved yet.
     */
    public function redraft(QuoteRequest $request, Quote $quote): RedirectResponse
    {
        $way = $request->input('switch_to') === 'text' ? 'text' : 'card';
        $stored = (array) $request->session()->get('quote_draft');
        $previous = $request->boolean('from_draft') && ($stored['quote_id'] ?? null) === $quote->id ? $stored : [];

        $request->session()->put('quote_draft', [...$this->draftFrom($request, $way, $previous), 'quote_id' => $quote->id]);

        return redirect()->route('quotes.edit', [$quote, 'as' => $way, 'draft' => 1]);
    }

    /**
     * What was chosen so far, checked, to refill the next view. Choices this view has no field for
     * (the card's design in «محرّر النص», the quote marks in مرسم الحروف) stay from the earlier draft.
     *
     * @param  array<string, mixed>  $previous
     * @return array<string, mixed>
     */
    private function draftFrom(QuoteRequest $request, string $way, array $previous = []): array
    {
        $fresh = collect($request->validated())
            ->put('kind', $request->input('kind'))
            ->put('signature', $request->validated('signature'))
            ->put('presentation', $way);

        return collect($previous)->except($fresh->keys()->all())->merge($fresh)
            ->reject(fn ($value) => $value === null)
            ->all();
    }

    /**
     * "استخدم التصميم": start a new quote with the look (font, colours, texture, box…) of one I can see —
     * never its words, author or story.
     */
    private function withTemplate(Quote $quote, Request $request): Quote
    {
        $template = ctype_digit((string) $request->query('template'))
            ? Quote::visibleTo($request->user())->find((int) $request->query('template'))
            : null;

        if ($template) {
            $quote->fill($template->only([
                'font', 'bg_color', 'text_color', 'highlight_color', 'highlight_style',
                'texture', 'ratio', 'font_size', 'presentation', 'mark_style', 'layout', 'design',
            ]));
        }

        return $quote;
    }

    /**
     * Store a newly created quote for the logged-in user.
     */
    public function store(QuoteRequest $request): RedirectResponse
    {
        $quote = new Quote([...$this->validatedWithHighlights($request), ...$request->libraryLink()]);
        $this->linkBook($quote, $request);
        $quote->user()->associate($request->user())->save();
        $request->session()->forget('quote_draft');

        // "أضف إلى": only into an anthology of my own; anything else is ignored.
        $anthology = $request->filled('anthology_id')
            ? $request->user()->anthologies()->find($request->integer('anthology_id'))
            : null;
        $anthology?->quotes()->attach($quote);

        return redirect()->route('quotes.show', $quote)->with('toast', $this->publishedMessage($quote, $anthology?->title));
    }

    /**
     * The toast after publishing says exactly what happened, from the reader's own choices: who can see
     * the quote, and the anthology it was filed in, if any.
     */
    private function publishedMessage(Quote $quote, ?string $anthologyTitle): string
    {
        $message = $quote->is_public
            ? 'أُضيف اقتباسك، ويراه القرّاء الآن.'
            : 'أُضيف اقتباسك إلى ركنك، ولا يراه غيرك.';

        return $anthologyTitle === null ? $message : $message.' وحُفظ في مختاراتك «'.$anthologyTitle.'».';
    }

    /**
     * مرسم الحروف — redesign an existing quote (authorized by QuotePolicy@update on the route).
     */
    public function edit(Quote $quote, Request $request): View
    {
        // A text quote opens in «محرّر النص», a card in مرسم الحروف; ?as= switches between them,
        // and ?draft=1 brings back the choices not saved yet (QuoteController@redraft).
        $stored = $request->session()->get('quote_draft');
        $draft = $request->boolean('draft') && is_array($stored) && ($stored['quote_id'] ?? null) === $quote->id ? $stored : [];
        $quote->fill(Arr::except($draft, ['quote_id']));

        $asText = $request->query('as', $quote->presentation) === 'text';
        $quote->presentation = $asText ? 'text' : 'card';

        return view($asText ? 'quotes.editor' : 'quotes.studio', [
            'quote' => $quote,
            'draft' => $draft,
            ...$this->formOptions(),
        ]);
    }

    /**
     * Update the quote (authorized by QuotePolicy@update on the route).
     */
    public function update(QuoteRequest $request, Quote $quote): RedirectResponse
    {
        $quote->fill($this->validatedWithHighlights($request));
        $this->linkBook($quote, $request);
        $quote->save();

        return match ($request->input('_form')) {
            'studio', 'editor' => redirect()->route('quotes.show', $quote)->with('toast', 'حُفظت تعديلاتك.'),
            default => redirect()->to(preg_replace('/([?&])edit=\d+&?/', '$1', $this->previousOnSite()))->with('toast', 'حُفظت تعديلاتك.'),
        };
    }

    /**
     * Delete the quote immediately (authorized by QuotePolicy@delete on the route).
     */
    public function destroy(Quote $quote): RedirectResponse
    {
        $quote->delete();

        // Deleting from the quote's own page (with or without a query): that page no longer exists, so go home.
        return parse_url(url()->previous(), PHP_URL_PATH) === parse_url(route('quotes.show', $quote), PHP_URL_PATH)
            ? redirect()->route('home')
            : $this->backTo();
    }

    /**
     * What «محرّر النص» and مرسم الحروف offer: the categories, and the titles of books quoted publicly
     * (suggested while typing the book's name).
     *
     * @return array{categories: \Illuminate\Database\Eloquent\Collection<int, Category>, bookTitles: Collection<int, string>}
     */
    private function formOptions(): array
    {
        return [
            'categories' => Category::orderBy('id')->get(),
            'bookTitles' => Book::whereHas('quotes', fn ($quotes) => $quotes->public())->latest()->limit(300)->pluck('title'),
        ];
    }

    /**
     * A reader's quote from a book joins that book's page (رفوف القرّاء): the named book is reused
     * if it already exists, otherwise added. Library diwans are not reader books.
     */
    private function linkBook(Quote $quote, QuoteRequest $request): void
    {
        if (! $request->has('book') || $quote->isFromLibrary()) {
            return;
        }

        if (blank($quote->book)) {
            $quote->book_id = null;

            return;
        }

        $book = Book::findOrAdd($quote->book, $quote->author, $request->user());

        if ($request->filled('book_genre') && $book->genre === null) {
            $book->update(['genre' => $request->input('book_genre')]);
        }

        $quote->book_id = $book->id;
    }

    /**
     * Validated data plus the cleaned highlight indexes from QuoteRequest::passedValidation().
     *
     * @return array<string, mixed>
     */
    private function validatedWithHighlights(QuoteRequest $request): array
    {
        $data = collect($request->validated())->except(['book_genre', 'kind', 'signature', 'anthology_id'])->all();

        if ($request->has('highlights')) {
            $data['highlights'] = $request->input('highlights');
        }

        return $data;
    }
}
