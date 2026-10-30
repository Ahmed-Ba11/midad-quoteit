<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;

abstract class Controller
{
    /**
     * Go back to the previous page of this site (optionally to an anchor on it).
     * The previous URL can come from the Referer header, so anything off-site falls back to home.
     */
    protected function backTo(string $fragment = ''): RedirectResponse
    {
        return redirect()->to($this->previousOnSite().$fragment);
    }

    /**
     * The previous page if it is on this site (and not the current page), otherwise home.
     */
    protected function previousOnSite(): string
    {
        $previous = url()->previous();
        $home = rtrim(url('/'), '/');

        // Same scheme, host and port as this site — so never another site, nor a "javascript:" link.
        $isOwnSite = $previous === $home || str_starts_with($previous, $home.'/') || str_starts_with($previous, $home.'?');

        return $isOwnSite && $previous !== request()->fullUrl() ? $previous : route('home');
    }
}
