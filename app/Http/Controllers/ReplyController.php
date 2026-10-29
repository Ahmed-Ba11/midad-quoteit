<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReplyRequest;
use App\Models\Quote;
use Illuminate\Http\RedirectResponse;

class ReplyController extends Controller
{
    /**
     * Add the logged-in user's reply to the quote.
     */
    public function store(ReplyRequest $request, Quote $quote): RedirectResponse
    {
        $quote->replies()->make($request->validated())
            ->user()->associate($request->user())
            ->save();

        return redirect()->to(route('quotes.show', $quote).'#replies');
    }
}
