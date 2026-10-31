<?php

namespace App\Http\Controllers;

use App\Http\Requests\DiwanRequest;
use App\Http\Requests\DiwanVerseRequest;
use App\Models\Diwan;
use App\Models\DiwanVerse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DiwanController extends Controller
{
    /**
     * دواوين القرّاء: public diwans of poets out of copyright, plus the viewer's own (private too).
     */
    public function index(Request $request): View
    {
        $term = mb_substr(trim($request->string('q')), 0, 60);
        $search = fn ($query) => $query->when($term !== '', fn ($matching) => $matching
            ->where(fn ($text) => $text->where('poet_name', 'like', "%{$term}%")->orWhere('title', 'like', "%{$term}%")));

        return view('diwans.index', [
            'term' => $term,
            'diwans' => Diwan::public()->tap($search)
                ->with('user')->withCount('verses')->latest('updated_at')->paginate(12)->withQueryString(),
            'mine' => $request->user()?->diwans()->tap($search)->withCount('verses')->latest('updated_at')->get() ?? collect(),
        ]);
    }

    public function create(): View
    {
        return view('diwans.form', ['diwan' => new Diwan]);
    }

    public function store(DiwanRequest $request): RedirectResponse
    {
        $diwan = $request->user()->diwans()->create($request->validated());

        return redirect()->route('diwans.show', $diwan)->with('toast', 'أُنشئ الديوان؛ أضف أول بيت.');
    }

    /**
     * One reader's diwan (DiwanPolicy@view on the route): its verses, and the add-a-verse form for its owner.
     */
    public function show(Diwan $diwan): View
    {
        $diwan->load(['user', 'verses']);

        return view('diwans.show', ['diwan' => $diwan]);
    }

    public function edit(Diwan $diwan): View
    {
        return view('diwans.form', ['diwan' => $diwan]);
    }

    public function update(DiwanRequest $request, Diwan $diwan): RedirectResponse
    {
        $diwan->update($request->validated());

        return redirect()->route('diwans.show', $diwan)->with('toast', 'حُفظت التعديلات.');
    }

    public function destroy(Request $request, Diwan $diwan): RedirectResponse
    {
        $diwan->delete();

        return redirect()->route('profile.show', [$request->user(), 'tab' => 'diwans'])->with('toast', 'حُذف الديوان.');
    }

    /**
     * Copy one more verse into the diwan (DiwanPolicy@update on the route).
     */
    public function storeVerse(DiwanVerseRequest $request, Diwan $diwan): RedirectResponse
    {
        $diwan->verses()->create($request->validated());
        $diwan->touch();

        return redirect()->to(route('diwans.show', $diwan).'#add-verse')->with('toast', 'أُضيف البيت إلى الديوان.');
    }

    /**
     * Remove a verse (scoped to its diwan; DiwanPolicy@update on the route).
     */
    public function destroyVerse(Diwan $diwan, DiwanVerse $verse): RedirectResponse
    {
        $verse->delete();

        return redirect()->to(route('diwans.show', $diwan).'#verses');
    }
}
