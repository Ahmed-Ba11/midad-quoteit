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

        // شعرائي is public unless its owner chose to keep it to themselves.
        if (! $isOwner && ! $user->show_poets) {
            $allowedTabs = array_values(array_diff($allowedTabs, ['poets']));
        }

        $tab = in_array($request->query('tab'), $allowedTabs, true) ? $request->query('tab') : 'quotes';

        // Visitors see only what the owner made public; the owner sees their private quotes too.
        $myQuotes = fn () => $user->quotes()->visibleTo($viewer);

        $quotes = match ($tab) {
            'quotes' => $myQuotes()->latest(),
            'original' => $myQuotes()->where('is_original', true)->latest(),
            'saved' => $user->bookmarkedQuotes()->visibleTo($viewer)->orderByPivot('created_at', 'desc'),
            default => null,
        };

        return view('profile.show', [
            'user' => $user,
            'tab' => $tab,
            'tabs' => $allowedTabs,
            'isOwner' => $isOwner,
            'quotes' => $quotes?->withCardData($viewer)->paginate(12)->withQueryString(),
            'pinned' => $user->pinned_quote_id ? Quote::visibleTo($viewer)->withCardData($viewer)->find($user->pinned_quote_id) : null,
            'anthologies' => $tab === 'anthologies'
                ? $user->anthologies()->when(! $isOwner, fn ($public) => $public->where('is_public', true))
                    ->withCount(['quotes' => fn ($quotes) => $quotes->visibleTo($viewer)])
                    ->with(['quotes' => fn ($quotes) => $quotes->visibleTo($viewer)->latest('anthology_quote.created_at')->limit(3)])
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
            // Counted over what this viewer may see, so private quotes leave no trace for visitors.
            'stats' => [
                'اقتباس' => $myQuotes()->count(),
                'بقلمه' => $myQuotes()->where('is_original', true)->count(),
                'إعجاب' => DB::table('likes')->whereIn('quote_id', $myQuotes()->select('quotes.id'))->count(),
                'رد' => Reply::whereIn('quote_id', $myQuotes()->select('quotes.id'))->count(),
            ],
        ]);
    }

    /**
     * Update the logged-in user's own bio and corner colour.
     */
    public function updateBio(BioRequest $request): RedirectResponse
    {
        $request->user()->update($request->validated());

        return redirect()->route('profile.show', $request->user())->with('toast', 'تزيّن ركنك.');
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
