<?php

/*
|--------------------------------------------------------------------------
| رواق الحرف — quote card design options
|--------------------------------------------------------------------------
|
| The keys are what gets stored on the quote; validation and the live
| preview both read from here so there is one source of truth.
|
*/

return [

    'fonts' => [
        'amiri' => ['label' => 'أميري', 'lang' => 'ar', 'stack' => "'Amiri', serif"],
        'thmanyah-text' => ['label' => 'ثمانية نصّي', 'lang' => 'ar', 'stack' => "'thmanyah serif text', 'Amiri', serif"],
        'thmanyah-display' => ['label' => 'ثمانية عرض', 'lang' => 'ar', 'stack' => "'thmanyah serif display', 'Noto Naskh Arabic', serif"],
        'naskh' => ['label' => 'نسخ', 'lang' => 'ar', 'stack' => "'Noto Naskh Arabic', serif"],
        'ruqaa' => ['label' => 'رقعة', 'lang' => 'ar', 'stack' => "'Aref Ruqaa', serif"],
        'messiri' => ['label' => 'المسيري', 'lang' => 'ar', 'stack' => "'El Messiri', sans-serif"],
        'reem-kufi' => ['label' => 'ريم كوفي', 'lang' => 'ar', 'stack' => "'Reem Kufi', sans-serif"],
        'cormorant' => ['label' => 'Cormorant', 'lang' => 'en', 'stack' => "'Cormorant Garamond', serif", 'italic' => true],
        'eb-garamond' => ['label' => 'EB Garamond', 'lang' => 'en', 'stack' => "'EB Garamond', serif"],
        'playfair' => ['label' => 'Playfair', 'lang' => 'en', 'stack' => "'Playfair Display', serif"],
        'baskerville' => ['label' => 'Baskerville', 'lang' => 'en', 'stack' => "'Libre Baskerville', serif"],
        'im-fell' => ['label' => 'IM Fell', 'lang' => 'en', 'stack' => "'IM Fell English', serif"],
    ],

    'palettes' => [
        'sand' => ['name' => 'رملي', 'bg' => '#E8E1D5', 'text' => '#1E1A17', 'highlight' => '#8A3324'],
        'mauve' => ['name' => 'وردي', 'bg' => '#8F7378', 'text' => '#FFFFFF', 'highlight' => '#F3D9B1'],
        'cocoa' => ['name' => 'بُنّي', 'bg' => '#3A2A22', 'text' => '#E6C88F', 'highlight' => '#FFFFFF'],
        'sage' => ['name' => 'زيتي', 'bg' => '#7E8670', 'text' => '#FAF7F0', 'highlight' => '#F2E2B6'],
        'navy' => ['name' => 'كحلي', 'bg' => '#1E2533', 'text' => '#EDE6D6', 'highlight' => '#D9B97A'],
        'ink' => ['name' => 'حبر', 'bg' => '#1C1A18', 'text' => '#F2ECE2', 'highlight' => '#C98B6B'],
    ],

    'ratios' => [
        'wide' => ['label' => 'عريض', 'aspect' => '16 / 9', 'width' => 760],
        'square' => ['label' => 'مربّع', 'aspect' => '1 / 1', 'width' => 560],
        'tall' => ['label' => 'طولي', 'aspect' => '4 / 5', 'width' => 520],
    ],

    'font_size' => ['min' => 20, 'max' => 56],

];
