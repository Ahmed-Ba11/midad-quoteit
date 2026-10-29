<?php

namespace App\Http\Controllers;

use App\Http\Requests\BioRequest;
use App\Models\Reply;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * A user's profile: bio, stats and the tabs اقتباساتي / المحفوظات (saved is visible to its owner only).
     */
    public function show(Request $request, User $user): View
    {
        $isOwner = $request->user()?->is($user) ?? false;
        $tab = $isOwner && $request->query('tab') === 'saved' ? 'saved' : 'quotes';

        $quotes = ($tab === 'saved' ? $user->bookmarkedQuotes()->orderByPivot('created_at', 'desc') : $user->quotes()->latest())
            ->withCardData($request->user())
            ->paginate(12)
            ->withQueryString();

        return view('profile.show', [
            'user' => $user,
            'quotes' => $quotes,
            'tab' => $tab,
            'isOwner' => $isOwner,
            'stats' => [
                'اقتباس' => $user->quotes()->count(),
                'إعجاب' => DB::table('likes')->whereIn('quote_id', $user->quotes()->select('id'))->count(),
                'رد' => Reply::whereIn('quote_id', $user->quotes()->select('id'))->count(),
            ],
        ]);
    }

    /**
     * Update the logged-in user's own bio.
     */
    public function updateBio(BioRequest $request): RedirectResponse
    {
        $request->user()->update($request->validated());

        return redirect()->route('profile.show', $request->user());
    }
}
