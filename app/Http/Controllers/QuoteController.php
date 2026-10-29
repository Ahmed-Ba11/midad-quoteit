<?php

namespace App\Http\Controllers;

use App\Http\Requests\QuoteRequest;
use App\Models\Category;
use App\Models\Quote;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class QuoteController extends Controller
{
    /**
     * Display all quotes, newest first, 12 per page.
     */
    public function index(): View
    {
        $quotes = Quote::with(['user', 'category'])->latest()->paginate(12);

        return view('quotes.index', compact('quotes'));
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

        return redirect()->back();
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
