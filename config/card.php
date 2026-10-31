<?php

/*
|--------------------------------------------------------------------------
| مرسم الحروف — the designed card
|--------------------------------------------------------------------------
|
| A simple studio: a background (palette or own colour), its texture and dim, a small divider under
| the words, and an optional box that holds them. The keys are what quotes.design stores
| (App\Support\CardDesign whitelists them); the live preview reads the same lists from the page.
|
*/

return [

    // Heritage palettes: background, text, accent (divider, marks), and the box's colour.
    'palettes' => [
        'raqq' => ['name' => 'رَقّ', 'bg' => '#F5EFE4', 'text' => '#1E1A17', 'accent' => '#8A3324', 'box' => '#FBF7EF'],
        'qani' => ['name' => 'قاني', 'bg' => '#8A3324', 'text' => '#F5EFE4', 'accent' => '#E3C78A', 'box' => '#6E2419'],
        'kraft' => ['name' => 'كرافت', 'bg' => '#8E8B86', 'text' => '#1E1A17', 'accent' => '#6B4E2E', 'box' => '#C9A97A'],
        'charcoal' => ['name' => 'فحم', 'bg' => '#2A2724', 'text' => '#EFE6D6', 'accent' => '#C9A45C', 'box' => '#B9A994'],
        'indigo' => ['name' => 'مخطوط نيلي', 'bg' => '#1B2440', 'text' => '#EFE6D2', 'accent' => '#C9A24A', 'box' => '#23305A'],
        'olive' => ['name' => 'زيتون', 'bg' => '#4F5634', 'text' => '#F2EBD9', 'accent' => '#D8C27A', 'box' => '#5D6540'],
        'rose' => ['name' => 'ورد مُغبَّر', 'bg' => '#E9D6CF', 'text' => '#4A2C2A', 'accent' => '#A0574E', 'box' => '#F6ECE7'],
        'desert' => ['name' => 'صحراء', 'bg' => '#D9B88F', 'text' => '#3B2616', 'accent' => '#8A3324', 'box' => '#F1E2C8'],
        'turquoise' => ['name' => 'فيروز قديم', 'bg' => '#E8EFEA', 'text' => '#173B3A', 'accent' => '#2E7D74', 'box' => '#F7FAF7'],
        'ink-gold' => ['name' => 'حِبر وذهب', 'bg' => '#121110', 'text' => '#E9DFC8', 'accent' => '#A8873F', 'box' => '#1E1A17'],
        'lapis' => ['name' => 'لازورد', 'bg' => '#E6ECF3', 'text' => '#12294A', 'accent' => '#1F4E8C', 'box' => '#FFFFFF'],
        'sky' => ['name' => 'رماد السماء', 'bg' => '#CBD5DD', 'text' => '#1E1A17', 'accent' => '#8A3324', 'box' => '#FFFFFF'],
    ],

    'textures' => [
        'none' => 'بلا',
        'paper' => 'ورق',
        'linen' => 'كتّان',
        'kraft' => 'كرافت',
    ],

    'dividers' => [
        'none' => 'بلا',
        'star' => 'نجمة',
        'diamond' => 'معيّن',
        'dots' => 'نقاط',
    ],

    'box_paddings' => ['s' => 'ضيّقة', 'm' => 'متوسطة', 'l' => 'واسعة'],
    'box_shadows' => ['none' => 'بلا', 'soft' => 'ناعم', 'lifted' => 'مرفوع'],
    'aligns' => ['center' => 'وسط', 'start' => 'يمين'],

    // What a new card starts as.
    'defaults' => [
        'palette' => 'raqq',
        'texture' => 'paper',
        'dim' => 0,
        'divider' => 'none',
        'align' => 'center',
        'box' => false,
        'box_color' => '#FBF7EF',
        'box_padding' => 'm',
        'box_shadow' => 'soft',
        'box_outline' => false,
    ],

];
