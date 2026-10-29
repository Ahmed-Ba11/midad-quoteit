<?php

namespace App\Http\Controllers;

use App\Http\Requests\QuoteRequest;
use App\Models\Category;
use App\Models\Quote;
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
    public function create(): View
    {
        return view('quotes.studio', [
            'quote' => new Quote,
            'categories' => Category::orderBy('id')->get(),
        ]);
    }

    /**
     * Store a newly created quote for the logged-in user.
     */
    public function store(QuoteRequest $request): RedirectResponse
    {
        $request->user()->quotes()->create($this->validatedWithHighlights($request));

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
        ]);
    }

    /**
     * Update the quote (authorized by QuotePolicy@update on the route).
     */
    public function update(QuoteRequest $request, Quote $quote): RedirectResponse
    {
        $quote->update($this->validatedWithHighlights($request));

        return $request->input('_form') === 'studio'
            ? redirect()->route('home')
            : redirect()->back();
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
            : redirect()->back();
    }

    /**
     * Validated data plus the cleaned highlight indexes from QuoteRequest::passedValidation().
     *
     * @return array<string, mixed>
     */
    private function validatedWithHighlights(QuoteRequest $request): array
    {
        $data = $request->validated();

        if ($request->has('highlights')) {
            $data['highlights'] = $request->input('highlights');
        }

        return $data;
    }
}
