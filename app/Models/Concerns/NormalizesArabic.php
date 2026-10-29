<?php

namespace App\Models\Concerns;

/**
 * Arabic text as people type it into a search box: no tashkeel or tatweel, one alef, ة = ه, ى = ي,
 * lower-case Latin and single spaces. "قفا نبك" then finds "قفا نبكِ", and "الى" finds "إلى".
 */
trait NormalizesArabic
{
    public static function normalizeArabic(?string $text): string
    {
        $text = preg_replace('/[\x{0610}-\x{061A}\x{064B}-\x{065F}\x{0670}\x{0640}]/u', '', mb_strtolower((string) $text));

        $text = strtr($text, ['أ' => 'ا', 'إ' => 'ا', 'آ' => 'ا', 'ٱ' => 'ا', 'ة' => 'ه', 'ى' => 'ي', 'ؤ' => 'و', 'ئ' => 'ي']);

        return trim(preg_replace('/\s+/u', ' ', $text));
    }
}
