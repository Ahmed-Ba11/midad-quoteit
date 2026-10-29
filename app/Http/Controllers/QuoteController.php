<?php

namespace App\Http\Controllers;

use App\Http\Requests\QuoteRequest;
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
        $quotes = Quote::with('user')->latest()->paginate(12);

        return view('welcome', compact('quotes'));
    }

    /**
     * Store a newly created quote for the logged-in user.
     */
    public function store(QuoteRequest $request): RedirectResponse
    {
        $request->user()->quotes()->create($request->validated());

        return redirect()->route('home');
    }

    /**
     * Update the quote (authorized by QuotePolicy@update on the route).
     */
    public function update(QuoteRequest $request, Quote $quote): RedirectResponse
    {
        $quote->update($request->validated());

        return redirect()->back();
    }

    /**
     * Delete the quote immediately (authorized by QuotePolicy@delete on the route).
     */
    public function destroy(Quote $quote): RedirectResponse
    {
        $quote->delete();

        return redirect()->back();
    }
}
