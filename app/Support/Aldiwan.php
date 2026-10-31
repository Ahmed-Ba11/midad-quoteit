<?php

namespace App\Support;

use App\Models\Poet;

/**
 * الديوان (aldiwan.net): where a reader goes on from رقيم to read a whole poem or diwan, or to search
 * poetry beyond our library. Links only — nothing is ever fetched from the site (its terms forbid
 * extraction; linking is free). Search is its own GET form: /search.php?q=…
 */
class Aldiwan
{
    public const BASE = 'https://www.aldiwan.net';

    /**
     * A search on الديوان: tashkeel and tatweel are dropped (its search matches bare letters), and the
     * term is cut to 100 characters.
     */
    public static function search(string $term): string
    {
        $bare = trim(preg_replace('/[\x{064B}-\x{065F}\x{0670}\x{0640}]/u', '', $term) ?? '');

        return self::BASE.'/search.php?'.http_build_query(['q' => mb_substr($bare, 0, 100)], '', '&', PHP_QUERY_RFC3986);
    }

    /**
     * Where to read a poet's whole diwan: their page on الديوان when we know it, otherwise a search
     * for their name. A stored address is used only if it really is on aldiwan.net.
     */
    public static function poet(Poet $poet): string
    {
        $url = (string) $poet->aldiwan_url;

        return str_starts_with($url, self::BASE.'/') ? $url : self::search($poet->name);
    }

    /**
     * Where to read the poem a verse comes from: الديوان's search for its first hemistich.
     */
    public static function verse(string $sadr): string
    {
        return self::search($sadr);
    }
}
