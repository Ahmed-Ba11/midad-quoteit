<?php

namespace App\Support;

/**
 * مرسم الحروف's card settings (quotes.design), whitelisted against config/card.php. Whatever arrives
 * from the browser, only known keys with known values (or a #RRGGBB colour, or a 0–70 dim) are kept;
 * anything else falls back to the default. No HTML and no free text are ever stored.
 */
class CardDesign
{
    private const CHOICES = [
        'palette' => 'palettes',
        'texture' => 'textures',
        'divider' => 'dividers',
        'align' => 'aligns',
        'box_padding' => 'box_paddings',
        'box_shadow' => 'box_shadows',
    ];

    private const COLOURS = ['box_color'];

    private const SWITCHES = ['box', 'box_outline'];

    /**
     * @param  array<string, mixed>|null  $input
     * @return array<string, mixed> every design key, valid
     */
    public static function normalize(?array $input): array
    {
        $design = config('card.defaults');
        $input ??= [];

        foreach (self::CHOICES as $key => $list) {
            if (is_string($input[$key] ?? null) && array_key_exists($input[$key], config("card.$list"))) {
                $design[$key] = $input[$key];
            }
        }

        foreach (self::COLOURS as $key) {
            if (is_string($input[$key] ?? null) && preg_match('/^#[0-9A-Fa-f]{6}$/', $input[$key])) {
                $design[$key] = strtoupper($input[$key]);
            }
        }

        foreach (self::SWITCHES as $key) {
            if (array_key_exists($key, $input)) {
                $design[$key] = filter_var($input[$key], FILTER_VALIDATE_BOOL);
            }
        }

        if (is_numeric($input['dim'] ?? null)) {
            $design['dim'] = max(0, min(70, (int) $input['dim']));
        }

        return $design;
    }
}
