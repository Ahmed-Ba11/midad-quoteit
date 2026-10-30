<?php

namespace App\Http\Controllers;

use App\Models\Poet;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class FavoritePoetController extends Controller
{
    /**
     * Add the poet to شعرائي, or take them off.
     */
    public function toggle(Request $request, Poet $poet): RedirectResponse
    {
        $request->user()->favoritePoets()->toggle($poet);

        return $this->backTo();
    }
}
