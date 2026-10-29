<?php

namespace App\Http\Controllers;

use App\Http\Requests\QuoteRequest;
use App\Models\Book;
use App\Models\Category;
use App\Models\Quote;
use App\Models\Saying;
use App\Models\Verse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
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
        ];

        $quotes = Quote::withCardData($request->user())
            ->search($filters['q'])
            ->when($filters['category'], fn ($query, string $slug) => $query->whereRelation('category', 'slug', $slug))
            ->sortBy($filters['sort'])
            ->paginate(12)
            ->withQueryString();

        $isFiltering = $filters['q'] !== '' || $filters['category'] !== null;

        return view('quotes.index', [
            'quotes' => $quotes,
            'categories' => $categories,
            'filters' => $filters,
            'quoteOfTheDay' => ! $isFiltering && $quotes->onFirstPage() ? Quote::ofTheDay() : null,
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
            ? Quote::withCardData($request->user())
                ->where('author', $quote->author)
                ->whereKeyNot($quote->id)
                ->latest()->take($limit)->get()
            : collect();

        $sameCategory = $quote->category_id && $sameAuthor->count() < $limit
            ? Quote::withCardData($request->user())
                ->where('category_id', $quote->category_id)
                ->whereKeyNot([$quote->id, ...$sameAuthor->modelKeys()])
                ->latest()->take($limit - $sameAuthor->count())->get()
            : collect();

        return $sameAuthor->map(fn (Quote $related) => ['quote' => $related, 'reason' => 'من القائل نفسه'])
            ->concat($sameCategory->map(fn (Quote $related) => [
                'quote' => $related,
                'reason' => 'من تصنيف '.$quote->category->name,
            ]));
    }

    /**
     * رواق الحرف — design a new quote.
     */
    public function create(Request $request): View
    {
        return view('quotes.studio', [
            'quote' => $this->draftFromLibrary($request),
            'categories' => Category::orderBy('id')->get(),
            'bookTitles' => Book::latest()->limit(300)->pluck('title'),
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

        return redirect()->route('home');
    }

    /**
     * رواق الحرف — redesign an existing quote (authorized by QuotePolicy@update on the route).
     */
    public function edit(Quote $quote): View
    {
        return view('quotes.studio', [
            'quote' => $quote,
            'categories' => Category::orderBy('id')->get(),
            'bookTitles' => Book::latest()->limit(300)->pluck('title'),
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
            ? redirect()->route('home')
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
        $data = collect($request->validated())->except('book_genre')->all();

        if ($request->has('highlights')) {
            $data['highlights'] = $request->input('highlights');
        }

        return $data;
    }
}
