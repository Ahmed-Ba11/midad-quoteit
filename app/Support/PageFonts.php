<?php

namespace App\Support;

/**
 * The card fonts a page actually shows. Each quote card notes its font while the page renders; the
 * layout's <head> (rendered after the page body, as Blade does with @extends) then asks Google Fonts
 * for exactly those families in one request, instead of every card font on every page.
 * Bound per request (AppServiceProvider), so nothing carries over between requests.
 */
class PageFonts
{
    /**
     * Already in the layout's first stylesheet (the reading fonts), so never asked for twice.
     */
    private const ALWAYS_LOADED = ['amiri', 'naskh', 'plex'];

    /** @var array<string, true> */
    private array $used = [];

    public function use(?string $key): void
    {
        if ($key !== null && ! in_array($key, self::ALWAYS_LOADED, true) && config("quotes.fonts.$key.google")) {
            $this->used[$key] = true;
        }
    }

    /**
     * One Google Fonts css2 URL for the noted families, or null when the page needs none.
     */
    public function stylesheetUrl(): ?string
    {
        if ($this->used === []) {
            return null;
        }

        $families = collect(array_keys($this->used))->sort()
            ->map(fn (string $key) => 'family='.config("quotes.fonts.$key.google"))
            ->implode('&');

        return 'https://fonts.googleapis.com/css2?'.$families.'&display=block';
    }
}
