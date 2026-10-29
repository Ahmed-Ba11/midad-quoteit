<?php

namespace App\Http\Controllers;

use App\Models\Quote;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LikeController extends Controller
{
    /**
     * Like the quote, or remove the like if it is already there.
     */
    public function toggle(Request $request, Quote $quote): RedirectResponse
    {
        $request->user()->likedQuotes()->toggle($quote);

        return $this->backTo('#quote-'.$quote->id);
    }
}
