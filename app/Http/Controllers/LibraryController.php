<?php

namespace App\Http\Controllers;

use App\Models\Diwan;
use App\Models\Poet;
use App\Models\Saying;
use App\Models\Verse;
use App\Models\Work;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LibraryController extends Controller
{
    /**
     * خزانة الأدب: search, the diwan shelf, the poets' arcade, "لكلّ مقامٍ مقال" and the sayings wall.
     */
    public function index(Request $request): View
    {
        $term = mb_substr(trim($request->string('q')), 0, 60);

        $poets = Poet::with(['works' => fn ($works) => $works->withCount('verses')->with('verses')])
            ->withCount('verses')
            ->orderBy('sort_order')->get();

        $works = Work::with(['poet', 'verses'])->withCount('verses')->orderBy('poet_id')->orderBy('sort_order')->get();

        return view('library.index', [
            'term' => $term,
            'results' => $term === '' ? null : Verse::with('work.poet')->search($term)->orderBy('work_id')->orderBy('sort_order')->limit(40)->get(),
            'matchingPoets' => $term === '' ? collect() : $poets->filter(fn (Poet $poet) => str_contains($poet->name, $term)),
            'poets' => $poets,
            'muallaqat' => $works->where('type', 'muallaqa')->sortBy(fn (Work $work) => $work->poet->sort_order)->values(),
            'diwans' => $works->where('type', '!=', 'muallaqa')->sortBy(fn (Work $work) => [$work->poet->sort_order, $work->sort_order])->values(),
            'occasionVerses' => Verse::with('work.poet')->whereNotNull('occasion')->orderBy('id')->get()->groupBy('occasion'),
            'sayings' => Saying::orderBy('id')->get(),
            'readersDiwans' => Diwan::where('is_public', true)->latest('updated_at')->take(6)->get(),
            'counts' => [
                'poets' => $poets->count(),
                'works' => $works->count(),
                'verses' => Verse::count(),
            ],
        ]);
    }

    /**
     * One diwan or muallaqa with all its verses.
     */
    public function work(Work $work): View
    {
        $work->load(['poet.works', 'verses']);

        return view('library.work', ['work' => $work]);
    }

    /**
     * One poet: biography, works and every verse in the library.
     */
    public function poet(Request $request, Poet $poet): View
    {
        $poet->load(['works.verses'])->loadCount('fans');

        return view('library.poet', [
            'poet' => $poet,
            'isFavorite' => $request->user()?->favoritePoets()->whereKey($poet->id)->exists() ?? false,
        ]);
    }
}
