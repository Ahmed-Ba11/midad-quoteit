<?php

namespace App\Http\Controllers;

use App\Http\Requests\AnthologyRequest;
use App\Models\Anthology;
use App\Models\Quote;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class AnthologyController extends Controller
{
    /**
     * One anthology: its cover and its quotes, newest addition first (AnthologyPolicy@view on the route).
     */
    public function show(Request $request, Anthology $anthology): View
    {
        $viewer = $request->user();
        $anthology->load('user')->loadCount(['quotes' => fn ($quotes) => $quotes->visibleTo($viewer)]);

        return view('anthologies.show', [
            'anthology' => $anthology,
            // A quote its owner later made private drops out of everyone else's anthologies.
            'quotes' => $anthology->quotes()->visibleTo($viewer)->withCardData($viewer)
                ->orderByPivot('created_at', 'desc')->paginate(12),
        ]);
    }

    /**
     * Start a new anthology; from a quote page it also takes that quote in.
     */
    public function store(AnthologyRequest $request): RedirectResponse
    {
        $anthology = $request->user()->anthologies()->create($request->safe()->except('quote_id'));

        if ($request->filled('quote_id')) {
            Gate::authorize('view', $quote = Quote::findOrFail($request->integer('quote_id')));
            $anthology->quotes()->attach($quote);

            return $this->backTo('#anthologies')->with('toast', 'أُنشئت المختارات وفيها هذا الاقتباس.');
        }

        return redirect()->route('anthologies.show', $anthology)->with('toast', 'أُنشئت مختاراتك؛ أضف إليها من أي اقتباس.');
    }

    /**
     * Rename, recolour, or change who can see it (AnthologyPolicy@update on the route).
     */
    public function update(AnthologyRequest $request, Anthology $anthology): RedirectResponse
    {
        $anthology->update($request->safe()->except('quote_id'));

        return redirect()->route('anthologies.show', $anthology)->with('toast', 'حُفظت المختارات.');
    }

    /**
     * Delete the anthology; the quotes themselves stay (AnthologyPolicy@delete on the route).
     */
    public function destroy(Request $request, Anthology $anthology): RedirectResponse
    {
        $anthology->delete();

        return redirect()->route('profile.show', [$request->user(), 'tab' => 'anthologies'])->with('toast', 'حُذفت المختارات، وبقيت الاقتباسات نفسها.');
    }

    /**
     * Put a quote into the anthology, or take it out (AnthologyPolicy@update on the route).
     */
    public function toggle(Anthology $anthology, Quote $quote): RedirectResponse
    {
        // Taking a quote out is always allowed; putting one in needs it to be visible to me.
        if (! $anthology->quotes()->whereKey($quote->id)->exists()) {
            Gate::authorize('view', $quote);
        }

        $anthology->quotes()->toggle($quote);
        $anthology->touch();

        return $this->backTo('#anthologies');
    }
}
