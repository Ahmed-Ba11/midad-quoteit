<?php

/*
|--------------------------------------------------------------------------
| مرسم الحروف — quote card design options
|--------------------------------------------------------------------------
|
| The keys are what gets stored on the quote; validation and the live
| preview both read from here so there is one source of truth.
|
*/

return [

    /*
     * Fonts, grouped the way a reader thinks of them («بأيّ قلمٍ تكتب؟»). Every Google face is SIL OFL 1.1
     * (github.com/google/fonts/tree/main/ofl/<slug>), loaded from fonts.googleapis.com and never committed;
     * `google` is its css2 family spec. ثمانية is served locally (resources/fonts/thmanyah, gitignored).
     * `best`: verse, prose or both — a hint shown in the list, never a restriction.
     */
    'fonts' => [
        'amiri' => ['label' => 'أميري', 'lang' => 'ar', 'group' => 'naskh', 'best' => 'verse', 'stack' => "'Amiri', serif", 'google' => 'Amiri:wght@400;700'],
        'scheherazade' => ['label' => 'شهرزاد', 'lang' => 'ar', 'group' => 'naskh', 'best' => 'verse', 'stack' => "'Scheherazade New', serif", 'google' => 'Scheherazade+New:wght@400;700'],
        'naskh' => ['label' => 'نسخ', 'lang' => 'ar', 'group' => 'naskh', 'best' => 'both', 'stack' => "'Noto Naskh Arabic', serif", 'google' => 'Noto+Naskh+Arabic:wght@400;700'],
        'markazi' => ['label' => 'مركزي', 'lang' => 'ar', 'group' => 'naskh', 'best' => 'prose', 'stack' => "'Markazi Text', serif", 'google' => 'Markazi+Text:wght@400;600'],
        'thmanyah-text' => ['label' => 'ثمانية نصّي', 'lang' => 'ar', 'group' => 'naskh', 'best' => 'prose', 'stack' => "'thmanyah serif text', 'Amiri', serif"],
        'ruqaa' => ['label' => 'رقعة', 'lang' => 'ar', 'group' => 'ruqaa', 'best' => 'verse', 'stack' => "'Aref Ruqaa', serif", 'google' => 'Aref+Ruqaa:wght@400;700'],
        'reem-kufi' => ['label' => 'ريم كوفي', 'lang' => 'ar', 'group' => 'kufi', 'best' => 'prose', 'stack' => "'Reem Kufi', sans-serif", 'google' => 'Reem+Kufi:wght@400;600'],
        'kufam' => ['label' => 'كوفام', 'lang' => 'ar', 'group' => 'kufi', 'best' => 'prose', 'stack' => "'Kufam', sans-serif", 'google' => 'Kufam:wght@400;700'],
        'qahiri' => ['label' => 'قاهري', 'lang' => 'ar', 'group' => 'kufi', 'best' => 'prose', 'stack' => "'Qahiri', sans-serif", 'google' => 'Qahiri'],
        'thmanyah-display' => ['label' => 'ثمانية عرض', 'lang' => 'ar', 'group' => 'display', 'best' => 'both', 'stack' => "'thmanyah serif display', 'Noto Naskh Arabic', serif"],
        'mirza' => ['label' => 'ميرزا', 'lang' => 'ar', 'group' => 'display', 'best' => 'verse', 'stack' => "'Mirza', serif", 'google' => 'Mirza:wght@400;700'],
        'katibeh' => ['label' => 'كاتبة', 'lang' => 'ar', 'group' => 'display', 'best' => 'verse', 'stack' => "'Katibeh', serif", 'google' => 'Katibeh'],
        'rakkas' => ['label' => 'رقّاص', 'lang' => 'ar', 'group' => 'display', 'best' => 'prose', 'stack' => "'Rakkas', serif", 'google' => 'Rakkas'],
        'messiri' => ['label' => 'المسيري', 'lang' => 'ar', 'group' => 'modern', 'best' => 'both', 'stack' => "'El Messiri', sans-serif", 'google' => 'El+Messiri:wght@400;600'],
        'almarai' => ['label' => 'المراعي', 'lang' => 'ar', 'group' => 'modern', 'best' => 'prose', 'stack' => "'Almarai', sans-serif", 'google' => 'Almarai:wght@400;700'],
        'plex' => ['label' => 'بلكس', 'lang' => 'ar', 'group' => 'modern', 'best' => 'prose', 'stack' => "'IBM Plex Sans Arabic', sans-serif", 'google' => 'IBM+Plex+Sans+Arabic:wght@400;600'],
        'lalezar' => ['label' => 'لاله‌زار', 'lang' => 'ar', 'group' => 'playful', 'best' => 'prose', 'stack' => "'Lalezar', sans-serif", 'google' => 'Lalezar'],
        'marhey' => ['label' => 'مرحى', 'lang' => 'ar', 'group' => 'playful', 'best' => 'prose', 'stack' => "'Marhey', sans-serif", 'google' => 'Marhey:wght@400;600'],
        'cormorant' => ['label' => 'Cormorant', 'lang' => 'en', 'group' => 'latin', 'best' => 'both', 'stack' => "'Cormorant Garamond', serif", 'italic' => true, 'google' => 'Cormorant+Garamond:ital,wght@0,500;1,500'],
        'eb-garamond' => ['label' => 'EB Garamond', 'lang' => 'en', 'group' => 'latin', 'best' => 'both', 'stack' => "'EB Garamond', serif", 'google' => 'EB+Garamond:ital@0;1'],
        'playfair' => ['label' => 'Playfair', 'lang' => 'en', 'group' => 'latin', 'best' => 'both', 'stack' => "'Playfair Display', serif", 'google' => 'Playfair+Display:ital@0;1'],
        'baskerville' => ['label' => 'Baskerville', 'lang' => 'en', 'group' => 'latin', 'best' => 'both', 'stack' => "'Libre Baskerville', serif", 'google' => 'Libre+Baskerville:ital@0;1'],
        'im-fell' => ['label' => 'IM Fell', 'lang' => 'en', 'group' => 'latin', 'best' => 'both', 'stack' => "'IM Fell English', serif", 'google' => 'IM+Fell+English:ital@0;1'],
    ],

    'font_groups' => [
        'naskh' => 'نسخ',
        'ruqaa' => 'رقعة',
        'kufi' => 'كوفي',
        'display' => 'زخرفي',
        'modern' => 'حديث',
        'playful' => 'مرِح',
        'latin' => 'لاتيني',
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
        'story' => ['label' => 'قصة', 'aspect' => '9 / 16', 'width' => 420],
    ],

    'font_size' => ['min' => 20, 'max' => 56],

    // How a text-only quote is framed (presentation = text).
    'marks' => [
        'guillemets' => ['label' => '«علامتا تنصيص»', 'open' => '«', 'close' => '»'],
        'curly' => ['label' => 'علامة كبيرة', 'open' => '❝', 'close' => '❞'],
        'ornament' => ['label' => 'زخرفة ذهبية', 'open' => null, 'close' => null],
        'arch' => ['label' => 'لوح رقيم', 'open' => null, 'close' => null],
    ],

    // How the words are set (App\Support\QuoteLayout): detected, or forced to verse or prose.
    'layouts' => [
        'auto' => ['label' => 'تلقائي', 'hint' => 'يتعرّف على الشعر من طريقة كتابتك'],
        'verse' => ['label' => 'شعر', 'hint' => 'صدرٌ وعجز كما في الدواوين'],
        'prose' => ['label' => 'نثر', 'hint' => 'فقرة كما كتبتها'],
    ],

    // Twelve colours for emphasis (shading or coloured letters), each readable on paper and at night.
    'highlight_colors' => [
        '#8A3324' => 'قاني', '#B5543C' => 'آجرّي', '#D98C5F' => 'مشمشي', '#C9A45C' => 'ذهبي',
        '#E3B448' => 'زعفراني', '#6E8B3D' => 'زيتي', '#3E8E7E' => 'فيروزي', '#3A6EA5' => 'لازوردي',
        '#5B4B8A' => 'بنفسجي', '#B0577E' => 'وردي', '#7A5C45' => 'بُنّي', '#1E1A17' => 'حبر',
    ],

    // شارتي: emblems a reader can wear instead of their initial (drawn in resources/views/components/badge.blade.php).
    'badges' => [
        'star8' => ['label' => 'النجمة الثمانية', 'group' => 'هندسية'],
        'girih' => ['label' => 'المثمّن', 'group' => 'هندسية'],
        'dots' => ['label' => 'نقاط الإعجام', 'group' => 'هندسية'],
        'arch' => ['label' => 'القوس', 'group' => 'هندسية'],
        'quill' => ['label' => 'الريشة', 'group' => 'أدبية'],
        'book' => ['label' => 'الكتاب', 'group' => 'أدبية'],
        'lamp' => ['label' => 'السراج', 'group' => 'أدبية'],
        'seal' => ['label' => 'الختم المدوّر', 'group' => 'أختام'],
        'diamond' => ['label' => 'المعيّن', 'group' => 'أختام'],
        'bookmark' => ['label' => 'فاصل الكتاب', 'group' => 'أختام'],
        'nib' => ['label' => 'سنّ القلم', 'group' => 'أختام'],
        'quote' => ['label' => 'علامة الاقتباس', 'group' => 'أختام'],
    ],

    // Book shelves (رفوف القرّاء): reading statuses and genres a reader can pick.
    'reading_statuses' => [
        'reading' => 'أقرؤه الآن',
        'read' => 'قرأته',
        'want' => 'أريد قراءته',
    ],

    'book_genres' => [
        'novel' => 'رواية',
        'poetry' => 'شعر وأدب',
        'thought' => 'فكر وفلسفة',
        'self' => 'تطوير ذات',
        'history' => 'تاريخ وسير',
        'religion' => 'دين',
        'science' => 'علوم',
        'other' => 'أخرى',
    ],

];
