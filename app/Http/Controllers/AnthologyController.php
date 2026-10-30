<?php

namespace App\Http\Controllers;

use App\Http\Requests\AnthologyRequest;
use App\Models\Anthology;
use App\Models\Quote;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AnthologyController extends Controller
{
    /**
     * One anthology: its cover and its quotes, newest addition first (AnthologyPolicy@view on the route).
     */
    public function show(Request $request, Anthology $anthology): View
    {
        $anthology->load('user')->loadCount('quotes');

        return view('anthologies.show', [
            'anthology' => $anthology,
            'quotes' => $anthology->quotes()->withCardData($request->user())
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
            $anthology->quotes()->attach($request->integer('quote_id'));

            return $this->backTo('#anthologies');
        }

        return redirect()->route('anthologies.show', $anthology);
    }

    /**
     * Rename, recolour, or change who can see it (AnthologyPolicy@update on the route).
     */
    public function update(AnthologyRequest $request, Anthology $anthology): RedirectResponse
    {
        $anthology->update($request->safe()->except('quote_id'));

        return redirect()->route('anthologies.show', $anthology);
    }

    /**
     * Delete the anthology; the quotes themselves stay (AnthologyPolicy@delete on the route).
     */
    public function destroy(Request $request, Anthology $anthology): RedirectResponse
    {
        $anthology->delete();

        return redirect()->route('profile.show', [$request->user(), 'tab' => 'anthologies']);
    }

    /**
     * Put a quote into the anthology, or take it out (AnthologyPolicy@update on the route).
     */
    public function toggle(Anthology $anthology, Quote $quote): RedirectResponse
    {
        $anthology->quotes()->toggle($quote);
        $anthology->touch();

        return $this->backTo('#anthologies');
    }
}
