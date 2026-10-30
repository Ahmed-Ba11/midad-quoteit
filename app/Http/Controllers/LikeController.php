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
        $isLiked = $request->user()->likedQuotes()->toggle($quote)['attached'] !== [];

        return $this->backTo('#quote-'.$quote->id)
            ->with('toast', $isLiked ? 'أعجبك؛ تجده في «أعجبني» بركنك.' : 'أُزيل من «أعجبني».');
    }
}
