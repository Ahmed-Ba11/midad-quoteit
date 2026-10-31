<?php

use App\Support\CardDesign;
use Tests\TestCase;

uses(TestCase::class);

test('an empty design is the default card', function () {
    expect(CardDesign::normalize(null))->toBe(config('card.defaults'))
        ->and(CardDesign::normalize([]))->toBe(config('card.defaults'));
});

test('known choices, the box colour, switches and the dim are kept', function () {
    $design = CardDesign::normalize([
        'palette' => 'indigo', 'texture' => 'linen', 'divider' => 'dots', 'align' => 'start',
        'box' => '1', 'box_color' => '#aabbcc', 'box_padding' => 'l', 'box_shadow' => 'none', 'box_outline' => 'true', 'dim' => '35',
    ]);

    expect($design)->palette->toBe('indigo')->texture->toBe('linen')->divider->toBe('dots')->align->toBe('start')
        ->box->toBeTrue()->box_color->toBe('#AABBCC')->box_padding->toBe('l')->box_shadow->toBe('none')
        ->box_outline->toBeTrue()->dim->toBe(35);
});

test('anything unknown or unsafe falls back to the default, and nothing else is stored', function () {
    $design = CardDesign::normalize([
        'palette' => '<script>', 'texture' => ['paper'], 'box_color' => 'red;background:url(x)',
        'dim' => '900', 'align' => 'justify', 'frame' => 'mihrab', 'seal' => '1', 'message' => '<b>hi</b>',
    ]);

    expect($design)->palette->toBe('raqq')->texture->toBe('paper')->box_color->toBe('#FBF7EF')
        ->dim->toBe(70)->align->toBe('center')
        ->and(array_keys($design))->toBe(array_keys(config('card.defaults')));
});
