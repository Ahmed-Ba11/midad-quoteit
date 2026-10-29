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
        $previous = url()->previous();
        $isOwnSite = parse_url($previous, PHP_URL_HOST) === request()->getHost()
            && parse_url($previous, PHP_URL_PORT) === parse_url(url('/'), PHP_URL_PORT);

        return redirect()->to(($isOwnSite ? $previous : route('home')).$fragment);
    }
}
