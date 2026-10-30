<?php

namespace App\Http\Controllers;

use App\Models\Saying;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class WallController extends Controller
{
    /**
     * Hang a saying on my wall (جداري), or take it down.
     */
    public function toggle(Request $request, Saying $saying): RedirectResponse
    {
        $isHung = $request->user()->wallSayings()->toggle($saying)['attached'] !== [];

        return $this->backTo('#saying-'.$saying->id)
            ->with('toast', $isHung ? 'عُلّق على جدارك في ركنك.' : 'أُنزل من جدارك.');
    }
}
