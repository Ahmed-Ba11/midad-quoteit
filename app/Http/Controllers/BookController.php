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
     */
    public function index(Request $request): View
    {
        $term = mb_substr(trim($request->string('q')), 0, 100);
        $genre = array_key_exists($request->query('genre'), config('quotes.book_genres')) ? $request->query('genre') : null;
        $isFiltering = $term !== '' || $genre !== null;

        $results = $isFiltering
            ? Book::withCount(['quotes', 'readers'])
                ->when($term !== '', fn ($query) => $query->where('match_key', 'like', '%'.trim(Book::matchKey($term, null), '|').'%'))
                ->when($genre, fn ($query, string $key) => $query->where('genre', $key))
                ->orderByDesc('quotes_count')->paginate(24)->withQueryString()
            : null;

        return view('books.index', [
            'term' => $term,
            'genre' => $genre,
            'results' => $results,
            'mostQuoted' => $isFiltering ? collect() : Book::withCount(['quotes', 'readers'])->has('quotes')
                ->orderByDesc('quotes_count')->latest()->take(12)->get(),
            'newest' => $isFiltering ? collect() : Book::withCount(['quotes', 'readers'])->latest()->take(12)->get(),
        ]);
    }

    /**
     * One book: its generated cover, reader shelf counts, and the best quotes readers took from it.
     */
    public function show(Request $request, Book $book): View
    {
        $book->loadCount(['quotes', 'readers']);

        return view('books.show', [
            'book' => $book,
            'quotes' => Quote::withCardData($request->user())->whereBelongsTo($book, 'bookRecord')
                ->orderByDesc('likes_count')->latest()->paginate(12),
            'shelfCounts' => $book->readers()->toBase()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status'),
            'myStatus' => $request->user()?->books()->whereKey($book->id)->first()?->pivot->status,
        ]);
    }

    /**
     * Put the book on my shelf with a reading status, or take it off (empty status).
     */
    public function shelf(ReadingStatusRequest $request, Book $book): RedirectResponse
    {
        $status = $request->validated('status');

        if ($status === null) {
            $request->user()->books()->detach($book);
        } else {
            $request->user()->books()->syncWithoutDetaching([$book->id => ['status' => $status]]);
        }

        return $this->backTo('#shelf');
    }
}
