<?php

use App\Support\CardDesign;
use Tests\TestCase;

uses(TestCase::class);

test('an empty design is the default card', function () {
    expect(CardDesign::normalize(null))->toBe(config('card.defaults'))
        ->and(CardDesign::normalize([]))->toBe(config('card.defaults'));
});

test('known choices, colours, switches and the dim are kept', function () {
    $design = CardDesign::normalize([
        'palette' => 'indigo', 'frame' => 'mihrab', 'texture' => 'linen', 'box' => '1', 'box_style' => 'glass',
        'box_color' => '#aabbcc', 'dim' => '35', 'seal' => 'true', 'seal_color' => 'gold', 'seal_emblem' => 'initial',
    ]);

    expect($design)->palette->toBe('indigo')->frame->toBe('mihrab')->texture->toBe('linen')
        ->box->toBeTrue()->box_style->toBe('glass')->box_color->toBe('#AABBCC')
        ->dim->toBe(35)->seal->toBeTrue()->seal_color->toBe('gold')->seal_emblem->toBe('initial');
});

test('anything unknown or unsafe falls back to the default', function () {
    $design = CardDesign::normalize([
        'palette' => '<script>', 'frame' => ['corners'], 'box_color' => 'red;background:url(x)',
        'dim' => '900', 'align' => 'justify', 'to' => 'سارة', 'message' => '<b>hi</b>', 'onload' => 'x',
    ]);

    expect($design)->palette->toBe('raqq')->frame->toBe('none')->box_color->toBe('#FBF7EF')
        ->dim->toBe(70)->align->toBe('center')
        ->and(array_keys($design))->toBe(array_keys(config('card.defaults')));
});
