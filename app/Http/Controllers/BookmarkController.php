<?php

namespace App\Http\Controllers;

use App\Models\Quote;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BookmarkController extends Controller
{
    /**
     * Save the quote to المحفوظات, or remove it if it is already saved.
     */
    public function toggle(Request $request, Quote $quote): RedirectResponse
    {
        $request->user()->bookmarkedQuotes()->toggle($quote);

        return $this->backTo('#quote-'.$quote->id);
    }
}
