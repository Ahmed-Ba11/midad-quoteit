<?php

namespace App\Support;

use Normalizer;

/**
 * How a quote's words are laid out: verse (صدر and عجز side by side, as in a printed diwan), lines
 * (free verse, kept line by line) or prose. «تلقائي» detects it from how people really type verse —
 * a tab, a separator with spaces on both sides (… * / - | ⁂ ◆), two or more spaces, or one line per
 * hemistich — while «شعر» and «نثر» force a mode. The raw text is what is stored; this runs at render
 * time. resources/js/quote-layout.js is the same algorithm for the live preview, and both are checked
 * against tests/fixtures/quote-layout.json so they cannot drift apart.
 */
class QuoteLayout
{
    /**
     * In order of trust: a tab, a separator with whitespace on both sides, then 2+ plain spaces.
     */
    private const SEPARATORS = [
        '/\t+/u',
        '/\s+(?:\*{1,3}|⁂|٭|✽|✳|※|◆|…+|\.{3,}|-{1,3}|–|—|\/{1,2}|\|{1,2})\s+/u',
        '/ {2,}/u',
    ];

    private const DIACRITICS = '/[\x{0610}-\x{061A}\x{064B}-\x{065F}\x{0670}\x{06D6}-\x{06ED}\x{0640}]/u';

    /**
     * @param  'auto'|'verse'|'prose'  $forced
     * @return array{mode: 'verse'|'lines'|'prose', verses: list<array{sadr: string, ajuz: string}>, lines: list<string>, paragraphs: list<string>, attribution: ?string, confidence: float}
     */
    public static function parse(string $raw, string $forced = 'auto'): array
    {
        $text = self::normalize($raw);
        $attribution = null;
        $allLines = explode("\n", $text);
        $last = end($allLines);

        // A closing «— المتنبي» is a signature, not a line of the poem.
        if (count($allLines) > 1 && preg_match('/^[—–-]\s*\S.{0,40}$/u', $last)) {
            $attribution = preg_replace('/^[—–-]\s*/u', '', $last);
            array_pop($allLines);
            $text = trim(implode("\n", $allLines));
        }

        $nonEmpty = array_values(array_filter(explode("\n", $text), fn (string $line) => trim($line) !== ''));
        $result = fn (string $mode, array $parts, float $confidence) => [
            'mode' => $mode,
            'verses' => $parts['verses'] ?? [],
            'lines' => $parts['lines'] ?? [],
            'paragraphs' => $parts['paragraphs'] ?? [],
            'attribution' => $attribution,
            'confidence' => $confidence,
        ];
        $prose = $result('prose', ['paragraphs' => array_values(array_filter(array_map('trim', preg_split('/\n\s*\n/u', $text))))], 0.5);

        if ($forced === 'prose' || $nonEmpty === []) {
            return $prose;
        }

        $loose = $forced === 'verse';

        // One line per verse, split inside the line.
        $splits = array_map(fn (string $line) => self::splitInline($line, $loose ? 0.35 : 0.55), $nonEmpty);
        $hits = count(array_filter($splits));

        if ($hits > 0 && ($hits / count($nonEmpty) >= 0.8 || $loose)) {
            return $result('verse', ['verses' => array_map(
                fn (string $line, ?array $split) => $split ?? ['sadr' => $line, 'ajuz' => ''], $nonEmpty, $splits,
            )], 0.95);
        }

        // One line per hemistich, paired.
        $blocks = array_map(
            fn (string $block) => array_values(array_filter(explode("\n", $block), fn (string $line) => trim($line) !== '')),
            preg_split('/\n\s*\n/u', $text),
        );
        $allPairs = count($blocks) > 1 && array_reduce($blocks, fn (bool $carry, array $block) => $carry && count($block) === 2, true);
        $count = count($nonEmpty);
        $wordCounts = array_map(self::words(...), $nonEmpty);
        $letterCounts = array_map(self::letters(...), $nonEmpty);
        $shortEven = $count >= 2 && $count % 2 === 0
            && min($wordCounts) >= 2 && max($wordCounts) <= 10 && max($letterCounts) <= 55;

        $pairs = [];
        for ($i = 0; $i + 1 < $count; $i += 2) {
            $pairs[] = ['sadr' => $nonEmpty[$i], 'ajuz' => $nonEmpty[$i + 1]];
        }
        $balanced = array_reduce($pairs, fn (bool $carry, array $pair) => $carry && self::balance($pair['sadr'], $pair['ajuz']) >= 0.6, true);

        if ($allPairs || ($shortEven && self::variation($letterCounts) <= 0.35 && $balanced) || ($loose && $count >= 2)) {
            $confidence = $allPairs ? 0.9 : ($count === 2 ? 0.6 : 0.7);

            if ($count >= 4) {
                $rhymes = array_count_values(array_map(fn (array $pair) => self::rhymeLetter($pair['ajuz']), $pairs));
                $confidence += max($rhymes) / count($pairs) >= 0.7 ? 0.2 : ($allPairs ? 0 : -0.2);
            }

            if ($count === 2 && ! $allPairs && ! ($wordCounts[0] <= 8 && $wordCounts[1] <= 8) && ! $loose) {
                $confidence = 0.3;
            }

            if ($count % 2 === 1 && $loose) {
                $pairs[] = ['sadr' => $nonEmpty[$count - 1], 'ajuz' => ''];
            }

            if ($confidence >= 0.55 || $loose) {
                return $result('verse', ['verses' => $pairs], min($confidence, 1.0));
            }
        }

        // «شعر» on a single line with no separator: split at the space nearest the middle.
        if ($loose && $count === 1) {
            $line = $nonEmpty[0];
            $middle = mb_strlen($line) / 2;
            $best = null;

            foreach (self::spacePositions($line) as $position) {
                if ($best === null || abs($position - $middle) < abs($best - $middle)) {
                    $best = $position;
                }
            }

            if ($best !== null && $best > 0) {
                return $result('verse', ['verses' => [[
                    'sadr' => trim(mb_substr($line, 0, $best)),
                    'ajuz' => trim(mb_substr($line, $best)),
                ]]], 0.3);
            }
        }

        // Free verse or a short multi-line saying: line by line.
        $sorted = $wordCounts;
        sort($sorted);

        if ($count >= 2 && $sorted[intdiv($count, 2)] <= 12 && max($wordCounts) <= 25) {
            return $result('lines', ['lines' => $nonEmpty], 0.6);
        }

        return $prose;
    }

    /**
     * NFC, one kind of line break and of space (tabs inside a line are kept: they mark hemistichs),
     * no zero-width characters, trimmed lines, at most one blank line in a row.
     */
    public static function normalize(string $text): string
    {
        if (class_exists(Normalizer::class)) {
            $text = Normalizer::normalize($text, Normalizer::FORM_C) ?: $text;
        }

        $text = preg_replace("/\r\n?/", "\n", $text);
        $text = preg_replace('/[\x{00A0}\x{2000}-\x{200A}\x{202F}\x{205F}\x{3000}]/u', ' ', $text);
        $text = preg_replace('/[\x{200B}\x{FEFF}]/u', '', $text);
        $text = implode("\n", array_map(fn (string $line) => trim($line, " \t"), explode("\n", $text)));

        return trim(preg_replace("/\n{3,}/", "\n\n", $text));
    }

    /**
     * Split one line into صدر and عجز at the separator nearest its middle, if both halves are real
     * hemistichs (2+ words, or 1 that carries a tatweel of تدوير) of balanced length.
     *
     * @return array{sadr: string, ajuz: string}|null
     */
    public static function splitInline(string $line, float $minBalance = 0.55): ?array
    {
        foreach (self::SEPARATORS as $pattern) {
            if (! preg_match_all($pattern, $line, $matches, PREG_OFFSET_CAPTURE)) {
                continue;
            }

            // Byte offsets throughout, so the maths never mixes bytes and characters.
            $middle = strlen($line) / 2;
            $hits = $matches[0];
            usort($hits, fn (array $a, array $b) => abs($a[1] + strlen($a[0]) / 2 - $middle) <=> abs($b[1] + strlen($b[0]) / 2 - $middle));
            [$separator, $at] = $hits[0];
            $sadr = trim(substr($line, 0, $at));
            $ajuz = trim(substr($line, $at + strlen($separator)));

            if (self::isHemistich($sadr) && self::isHemistich($ajuz) && self::balance($sadr, $ajuz) >= $minBalance) {
                return ['sadr' => $sadr, 'ajuz' => $ajuz];
            }
        }

        return null;
    }

    /**
     * Letters only: no tashkeel, no tatweel, no punctuation.
     */
    public static function letters(string $text): int
    {
        return preg_match_all('/\p{L}/u', preg_replace(self::DIACRITICS, '', $text));
    }

    public static function words(string $text): int
    {
        return count(preg_split('/\s+/u', trim($text), -1, PREG_SPLIT_NO_EMPTY));
    }

    private static function isHemistich(string $half): bool
    {
        return self::words($half) >= 2 || preg_match('/^ـ|ـ$/u', trim($half)) === 1;
    }

    private static function balance(string $a, string $b): float
    {
        $x = self::letters($a);
        $y = self::letters($b);

        return min($x, $y) / max($x, $y, 1);
    }

    /**
     * The rhyme letter (الرويّ) of a hemistich: its last letter once long vowels and ة/ه are set aside.
     */
    private static function rhymeLetter(string $text): string
    {
        $base = preg_replace('/[^\p{L}]/u', '', preg_replace(self::DIACRITICS, '', $text));
        $stripped = preg_replace('/[اىيوهة]+$/u', '', $base);

        return mb_substr($stripped !== '' ? $stripped : $base, -1);
    }

    /**
     * Coefficient of variation: how unequal the lines are in length.
     *
     * @param  list<int>  $values
     */
    private static function variation(array $values): float
    {
        $mean = array_sum($values) / count($values);
        $variance = array_sum(array_map(fn (int $value) => ($value - $mean) ** 2, $values)) / count($values);

        return sqrt($variance) / ($mean ?: 1);
    }

    /**
     * @return list<int> character positions of the spaces in a line
     */
    private static function spacePositions(string $line): array
    {
        $positions = [];

        foreach (mb_str_split($line) as $index => $character) {
            if ($character === ' ') {
                $positions[] = $index;
            }
        }

        return $positions;
    }
}
