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
        $isOwnSite = parse_url($previous, PHP_URL_HOST) === request()->getHost()
            && parse_url($previous, PHP_URL_PORT) === parse_url(url('/'), PHP_URL_PORT);

        return $isOwnSite && $previous !== request()->fullUrl() ? $previous : route('home');
    }
}
