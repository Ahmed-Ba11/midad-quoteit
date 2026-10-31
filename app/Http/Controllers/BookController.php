<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReadingStatusRequest;
use App\Models\Book;
use App\Models\Quote;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BookController extends Controller
{
    /**
     * رفوف القرّاء: the most-quoted books, the newest, and search / genre filter.
     * A book appears only once someone quoted it publicly; private quotes never reveal a title.
     */
    public function index(Request $request): View
    {
        $term = mb_substr(trim($request->string('q')), 0, 100);
        $genre = array_key_exists($request->query('genre'), config('quotes.book_genres')) ? $request->query('genre') : null;
        $isFiltering = $term !== '' || $genre !== null;

        $shelfBooks = fn () => Book::whereHas('quotes', fn ($quotes) => $quotes->public())
            ->withCount(['quotes' => fn ($quotes) => $quotes->public(), 'readers']);

        $results = $isFiltering
            ? $shelfBooks()
                ->when($term !== '', fn ($query) => $query->where('match_key', 'like', '%'.trim(Book::matchKey($term, null), '|').'%'))
                ->when($genre, fn ($query, string $key) => $query->where('genre', $key))
                ->orderByDesc('quotes_count')->paginate(24)->withQueryString()
            : null;

        return view('books.index', [
            'term' => $term,
            'genre' => $genre,
            'results' => $results,
            'mostQuoted' => $isFiltering ? collect() : $shelfBooks()->orderByDesc('quotes_count')->latest()->take(12)->get(),
            'newest' => $isFiltering ? collect() : $shelfBooks()->latest()->take(12)->get(),
        ]);
    }

    /**
     * One book: its generated cover, reader shelf counts, and the best public quotes readers took from it.
     * A book known only from private quotes is shown only to those who quoted it or shelved it.
     */
    public function show(Request $request, Book $book): View
    {
        $viewer = $request->user();
        abort_unless($book->isVisibleTo($viewer), 404);

        $book->loadCount(['quotes' => fn ($quotes) => $quotes->public(), 'readers']);

        return view('books.show', [
            'book' => $book,
            'quotes' => Quote::public()->withCardData($viewer)->whereBelongsTo($book, 'bookRecord')
                ->orderByDesc('likes_count')->latest()->paginate(12),
            'shelfCounts' => $book->readers()->toBase()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status'),
            'myStatus' => $viewer?->books()->whereKey($book->id)->first()?->pivot->status,
        ]);
    }

    /**
     * Put the book on my shelf with a reading status, or take it off (empty status).
     */
    public function shelf(ReadingStatusRequest $request, Book $book): RedirectResponse
    {
        // The same rule as the book page: a book I can see is one I can take off my shelf.
        abort_unless($book->isVisibleTo($request->user()), 404);

        $status = $request->validated('status');

        if ($status === null) {
            $request->user()->books()->detach($book);
        } else {
            $request->user()->books()->syncWithoutDetaching([$book->id => ['status' => $status]]);
        }

        return $this->backTo('#shelf');
    }
}
