<?php

namespace App\Http\Controllers;

use App\Http\Requests\BioRequest;
use App\Models\Quote;
use App\Models\Reply;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Tabs everyone can see, then the ones only the owner can (المحفوظات, كتبي).
     */
    private const PUBLIC_TABS = ['quotes', 'original', 'anthologies', 'poets', 'diwans'];

    private const OWNER_TABS = ['saved', 'books'];

    /**
     * ركني: a reader's corner — their colour, pinned quote, stats, and tabs for what they share,
     * write, collect and read. Private things (saved, books, private anthologies and diwans) are the owner's only.
     */
    public function show(Request $request, User $user): View
    {
        $viewer = $request->user();
        $isOwner = $viewer?->is($user) ?? false;
        $allowedTabs = $isOwner ? [...self::PUBLIC_TABS, ...self::OWNER_TABS] : self::PUBLIC_TABS;
        $tab = in_array($request->query('tab'), $allowedTabs, true) ? $request->query('tab') : 'quotes';

        $quotes = match ($tab) {
            'quotes' => $user->quotes()->latest(),
            'original' => $user->quotes()->where('is_original', true)->latest(),
            'saved' => $user->bookmarkedQuotes()->orderByPivot('created_at', 'desc'),
            default => null,
        };

        return view('profile.show', [
            'user' => $user,
            'tab' => $tab,
            'isOwner' => $isOwner,
            'quotes' => $quotes?->withCardData($viewer)->paginate(12)->withQueryString(),
            'pinned' => $user->pinned_quote_id ? Quote::withCardData($viewer)->find($user->pinned_quote_id) : null,
            'anthologies' => $tab === 'anthologies'
                ? $user->anthologies()->when(! $isOwner, fn ($public) => $public->where('is_public', true))
                    ->withCount('quotes')->with(['quotes' => fn ($quotes) => $quotes->latest('anthology_quote.created_at')->limit(3)])
                    ->latest('updated_at')->get()
                : collect(),
            'poets' => $tab === 'poets' ? $user->favoritePoets()->withCount('verses')->orderBy('sort_order')->get() : collect(),
            'diwans' => $tab === 'diwans'
                ? $user->diwans()->when(! $isOwner, fn ($public) => $public->where('is_public', true))
                    ->withCount('verses')->latest('updated_at')->get()
                : collect(),
            // كتبي (owner only): the reader's shelf grouped by reading status.
            'shelf' => $tab === 'books'
                ? $user->books()->withCount('quotes')->orderByPivot('updated_at', 'desc')->get()->groupBy('pivot.status')
                : collect(),
            'stats' => [
                'اقتباس' => $user->quotes()->count(),
                'بقلمه' => $user->quotes()->where('is_original', true)->count(),
                'إعجاب' => DB::table('likes')->whereIn('quote_id', $user->quotes()->select('id'))->count(),
                'رد' => Reply::whereIn('quote_id', $user->quotes()->select('id'))->count(),
            ],
        ]);
    }

    /**
     * Update the logged-in user's own bio and corner colour.
     */
    public function updateBio(BioRequest $request): RedirectResponse
    {
        $request->user()->update($request->validated());

        return redirect()->route('profile.show', $request->user());
    }

    /**
     * Pin one of my own quotes at the top of ركني, or unpin it (QuotePolicy@update on the route).
     */
    public function pin(Request $request, Quote $quote): RedirectResponse
    {
        $user = $request->user();
        $user->pinned_quote_id = $user->pinned_quote_id === $quote->id ? null : $quote->id;
        $user->save();

        return $this->backTo();
    }
}
