<?php

/*
|--------------------------------------------------------------------------
| مرسم الحروف — the designed card
|--------------------------------------------------------------------------
|
| Every option a card can take, beyond its font and its words. The keys are what quotes.design stores
| (App\Support\CardDesign whitelists them); the live preview reads the same lists from the page.
| Rule of elegance: at most one heavy ornament (girih band, background pattern or mihrab) per card.
|
*/

return [

    // Heritage palettes: background, text, accent (marks, divider), frame, and the inner box's colour.
    'palettes' => [
        'raqq' => ['name' => 'رَقّ', 'bg' => '#F5EFE4', 'text' => '#1E1A17', 'accent' => '#8A3324', 'frame' => '#A8873F', 'box' => '#FBF7EF'],
        'qani' => ['name' => 'قاني', 'bg' => '#8A3324', 'text' => '#F5EFE4', 'accent' => '#E3C78A', 'frame' => '#E3C78A', 'box' => '#6E2419'],
        'kraft' => ['name' => 'كرافت', 'bg' => '#8E8B86', 'text' => '#1E1A17', 'accent' => '#6B4E2E', 'frame' => '#1E1A17', 'box' => '#C9A97A'],
        'charcoal' => ['name' => 'فحم', 'bg' => '#2A2724', 'text' => '#1E1A17', 'accent' => '#8A3324', 'frame' => '#A8873F', 'box' => '#B9A994'],
        'indigo' => ['name' => 'مخطوط نيلي', 'bg' => '#1B2440', 'text' => '#EFE6D2', 'accent' => '#C9A24A', 'frame' => '#C9A24A', 'box' => '#23305A'],
        'olive' => ['name' => 'زيتون', 'bg' => '#4F5634', 'text' => '#F2EBD9', 'accent' => '#D8C27A', 'frame' => '#D8C27A', 'box' => '#5D6540'],
        'rose' => ['name' => 'ورد مُغبَّر', 'bg' => '#E9D6CF', 'text' => '#4A2C2A', 'accent' => '#A0574E', 'frame' => '#A0574E', 'box' => '#F6ECE7'],
        'desert' => ['name' => 'صحراء', 'bg' => '#D9B88F', 'text' => '#3B2616', 'accent' => '#8A3324', 'frame' => '#6E4A2A', 'box' => '#F1E2C8'],
        'turquoise' => ['name' => 'فيروز قديم', 'bg' => '#E8EFEA', 'text' => '#173B3A', 'accent' => '#2E7D74', 'frame' => '#B08D4A', 'box' => '#F7FAF7'],
        'ink-gold' => ['name' => 'حِبر وذهب', 'bg' => '#121110', 'text' => '#E9DFC8', 'accent' => '#A8873F', 'frame' => '#A8873F', 'box' => '#1E1A17'],
        'lapis' => ['name' => 'لازورد', 'bg' => '#E6ECF3', 'text' => '#12294A', 'accent' => '#1F4E8C', 'frame' => '#1F4E8C', 'box' => '#FFFFFF'],
        'sky' => ['name' => 'رماد السماء', 'bg' => '#CBD5DD', 'text' => '#1E1A17', 'accent' => '#8A3324', 'frame' => '#FFFFFF', 'box' => '#FFFFFF'],
    ],

    // Ready gradients only (no free picker, no angle): always at 160°.
    'gradients' => [
        'none' => ['name' => 'بلا'],
        'dusk' => ['name' => 'غسق', 'from' => '#2B2140', 'to' => '#8A3324'],
        'dawn' => ['name' => 'فجر', 'from' => '#F5EFE4', 'to' => '#E8D3B8'],
        'night' => ['name' => 'ليل المخطوط', 'from' => '#1B2440', 'to' => '#0F1422'],
        'olive' => ['name' => 'زيتون', 'from' => '#59603A', 'to' => '#2F3420'],
    ],

    'textures' => [
        'none' => 'بلا',
        'paper' => 'ورق',
        'linen' => 'كتّان',
        'kraft' => 'كرافت',
        'pattern' => 'نقش',
    ],

    'frames' => [
        'none' => 'بلا',
        'line' => 'خط رفيع',
        'double' => 'مزدوج',
        'corners' => 'زوايا هندسية',
        'arabesque' => 'أرابيسك',
        'mihrab' => 'محراب',
        'girih' => 'شريط هندسي',
    ],

    'dividers' => [
        'none' => 'بلا',
        'star' => 'نجمة',
        'diamond' => 'معيّن',
        'dots' => 'نقاط',
    ],

    'box_styles' => ['solid' => 'مصمت', 'glass' => 'زجاجي'],
    'box_paddings' => ['s' => 'ضيّقة', 'm' => 'متوسطة', 'l' => 'واسعة'],
    'box_shadows' => ['none' => 'بلا', 'soft' => 'ناعم', 'lifted' => 'مرفوع'],
    'aligns' => ['center' => 'وسط', 'start' => 'يمين'],

    // The wax seal: four waxes, three emblems (the ر nib of رقيم, an eight-point star, the giver's initial).
    'seal_colors' => [
        'qani' => ['name' => 'قاني', 'light' => '#B5483A', 'base' => '#8A3324', 'dark' => '#5A1E15'],
        'gold' => ['name' => 'ذهبي', 'light' => '#D1AE62', 'base' => '#A8873F', 'dark' => '#6E5523'],
        'olive' => ['name' => 'زيتي', 'light' => '#737C4E', 'base' => '#4F5634', 'dark' => '#30351F'],
        'navy' => ['name' => 'كحلي', 'light' => '#3A4A80', 'base' => '#23305A', 'dark' => '#141C38'],
    ],
    'seal_emblems' => ['nib' => 'ر رقيم', 'star' => 'نجمة', 'initial' => 'حرفك'],
    'seal_positions' => ['corner' => 'الزاوية', 'top' => 'الأعلى'],

    // What a new card starts as.
    'defaults' => [
        'palette' => 'raqq',
        'gradient' => 'none',
        'texture' => 'paper',
        'dim' => 0,
        'box' => false,
        'box_style' => 'solid',
        'box_color' => '#FBF7EF',
        'box_padding' => 'm',
        'box_shadow' => 'soft',
        'box_outline' => false,
        'frame' => 'none',
        'frame_color' => '#A8873F',
        'divider' => 'none',
        'align' => 'center',
        'brand' => true,
        'big_marks' => false,
        'seal' => false,
        'seal_color' => 'qani',
        'seal_emblem' => 'nib',
        'seal_position' => 'corner',
    ],

    /*
     * One tap sets everything but the words and the photo. Three answer the owner's references:
     * «سماء» (frosted glass over a photo), «كرافت» (a kraft box on grey linen with a black line) and
     * «فحم» (a taupe box with a soft shadow on charcoal).
     */
    'templates' => [
        'raqq' => ['name' => 'رَقّ', 'palette' => 'raqq', 'texture' => 'paper', 'frame' => 'corners', 'font' => 'amiri', 'divider' => 'star'],
        'kraft' => ['name' => 'كرافت', 'palette' => 'kraft', 'texture' => 'linen', 'box' => true, 'box_style' => 'solid', 'box_outline' => true, 'box_shadow' => 'none', 'font' => 'naskh'],
        'charcoal' => ['name' => 'فحم', 'palette' => 'charcoal', 'texture' => 'none', 'box' => true, 'box_shadow' => 'soft', 'font' => 'almarai'],
        'sky' => ['name' => 'سماء', 'palette' => 'sky', 'texture' => 'none', 'box' => true, 'box_style' => 'glass', 'box_shadow' => 'none', 'font' => 'rakkas', 'dim' => 10],
        'mihrab' => ['name' => 'محراب', 'palette' => 'indigo', 'texture' => 'none', 'frame' => 'mihrab', 'font' => 'mirza', 'ratio' => 'tall'],
        'qani' => ['name' => 'قاني', 'palette' => 'qani', 'texture' => 'paper', 'frame' => 'double', 'big_marks' => true, 'font' => 'scheherazade'],
        'manuscript' => ['name' => 'مخطوط', 'palette' => 'raqq', 'texture' => 'paper', 'frame' => 'girih', 'divider' => 'star', 'font' => 'amiri'],
        'olive' => ['name' => 'زيتون', 'palette' => 'olive', 'texture' => 'linen', 'frame' => 'line', 'divider' => 'diamond', 'font' => 'ruqaa'],
        'rose' => ['name' => 'ورد', 'palette' => 'rose', 'texture' => 'paper', 'frame' => 'arabesque', 'font' => 'markazi'],
        'desert' => ['name' => 'صحراء', 'palette' => 'desert', 'texture' => 'kraft', 'frame' => 'line', 'font' => 'reem-kufi'],
        'ink-gold' => ['name' => 'حبر وذهب', 'palette' => 'ink-gold', 'texture' => 'pattern', 'frame' => 'arabesque', 'divider' => 'star', 'font' => 'amiri'],
        'gift' => ['name' => 'إهداء', 'palette' => 'raqq', 'texture' => 'paper', 'box' => true, 'box_shadow' => 'soft', 'seal' => true, 'font' => 'ruqaa'],
    ],

];
