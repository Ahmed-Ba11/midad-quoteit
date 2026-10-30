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
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class QuoteController extends Controller
{
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

        return view('quotes.index', [
            'quotes' => $quotes,
            'categories' => $categories,
            'filters' => $filters,
            'isFrontPage' => $isFrontPage,
            'quoteOfTheDay' => $isFrontPage ? Quote::ofTheDay() : null,
            // أقلام رقيم: readers with the most public quotes — only what they chose to make public.
            'pens' => $isFrontPage
                ? User::whereHas('quotes', fn ($quotes) => $quotes->public())
                    ->withCount(['quotes as public_quotes_count' => fn ($quotes) => $quotes->public()])
                    ->orderByDesc('public_quotes_count')->take(8)->get(['id', 'name', 'accent', 'bio'])
                : collect(),
            // The hero's numbers move slowly; counting them once every few minutes is plenty.
            'counts' => $isFrontPage ? Cache::remember('home-counts', now()->addMinutes(5), fn () => [
                'quotes' => Quote::public()->count(),
                'readers' => User::whereHas('quotes', fn ($quotes) => $quotes->public())->count(),
                'verses' => Verse::count(),
            ]) : [],
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
            'inAnthologies' => $quote->anthologies()->where('is_public', true)->with('user')
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
     * مرسم الحروف — design a new quote.
     */
    public function create(Request $request): View
    {
        return view('quotes.studio', [
            'quote' => $this->draftFromLibrary($request),
            'backUrl' => $this->previousOnSite(),
            'categories' => Category::orderBy('id')->get(),
            'bookTitles' => Book::whereHas('quotes', fn ($quotes) => $quotes->public())->latest()->limit(300)->pluck('title'),
        ]);
    }

    /**
     * A new quote pre-filled from خزانة الأدب (?verses[]=…, up to 2, or ?saying=…).
     * The text is locked; QuoteRequest rebuilds it on the server when publishing.
     */
    private function draftFromLibrary(Request $request): Quote
    {
        $quote = new Quote;
        $verseIds = collect($request->query('verses', []))->filter(fn ($id) => ctype_digit((string) $id))
            ->map(fn ($id) => (int) $id)->unique()->take(config('library.max_selection'));

        if ($verseIds->isNotEmpty()) {
            $verses = Verse::with('work.poet')->findMany($verseIds)->sortBy(fn (Verse $verse) => $verseIds->search($verse->id))->values();

            if ($verses->isNotEmpty()) {
                $quote->fill([
                    'content' => $verses->map->asText()->implode("\n"),
                    'author' => $verses->pluck('work.poet.name')->unique()->implode(' و'),
                    'book' => $verses->pluck('work.title')->unique()->count() === 1 ? $verses->first()->work->title : null,
                    'category_id' => Category::where('slug', 'shir')->value('id'),
                ]);
                $quote->setRelation('libraryVerses', $verses);
            }
        } elseif ($saying = Saying::find($request->query('saying'))) {
            $quote->fill(['content' => $saying->text, 'author' => $saying->source]);
            $quote->setRelation('saying', $saying);
        } elseif (ctype_digit((string) $request->query('diwan_verse'))
            && ($diwanVerse = DiwanVerse::with('diwan')->find((int) $request->query('diwan_verse')))
            && Gate::allows('view', $diwanVerse->diwan)) {
            // A verse from دواوين القرّاء: prefilled with its poet and source, but editable (not verified text).
            $quote->fill([
                'content' => $diwanVerse->asText(),
                'author' => $diwanVerse->diwan->poet_name,
                'source' => $diwanVerse->diwan->source,
                'category_id' => Category::where('slug', 'shir')->value('id'),
            ]);
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
     * Store a newly created quote for the logged-in user.
     */
    public function store(QuoteRequest $request): RedirectResponse
    {
        $quote = new Quote([...$this->validatedWithHighlights($request), ...$request->libraryLink()]);
        $this->linkBook($quote, $request);
        $quote->user()->associate($request->user())->save();

        return redirect()->route('quotes.show', $quote)->with('toast', $quote->is_public
            ? 'رُقِم اقتباسك، وصار في لوح القرّاء.'
            : 'رُقِم اقتباسك في ركنك، لا يراه غيرك.');
    }

    /**
     * مرسم الحروف — redesign an existing quote (authorized by QuotePolicy@update on the route).
     */
    public function edit(Quote $quote): View
    {
        return view('quotes.studio', [
            'quote' => $quote,
            'categories' => Category::orderBy('id')->get(),
            'bookTitles' => Book::whereHas('quotes', fn ($quotes) => $quotes->public())->latest()->limit(300)->pluck('title'),
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

        return $request->input('_form') === 'studio'
            ? redirect()->route('quotes.show', $quote)->with('toast', 'حُفظ تصميمك الجديد.')
            : $this->backTo();
    }

    /**
     * Delete the quote immediately (authorized by QuotePolicy@delete on the route).
     */
    public function destroy(Quote $quote): RedirectResponse
    {
        $quote->delete();

        // Deleting from the quote's own page: that page no longer exists, so go home.
        return url()->previous() === route('quotes.show', $quote)
            ? redirect()->route('home')
            : $this->backTo();
    }

    /**
     * Validated data plus the cleaned highlight indexes from QuoteRequest::passedValidation().
     *
     * @return array<string, mixed>
     */
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

    private function validatedWithHighlights(QuoteRequest $request): array
    {
        $data = collect($request->validated())->except(['book_genre', 'kind', 'signature'])->all();

        if ($request->has('highlights')) {
            $data['highlights'] = $request->input('highlights');
        }

        return $data;
    }
}
