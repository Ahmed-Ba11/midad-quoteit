<?php

/*
|--------------------------------------------------------------------------
| خزانة الأدب — labels for the library's fixed keys
|--------------------------------------------------------------------------
*/

return [

    'eras' => [
        'jahili' => 'العصر الجاهلي',
        'mukhadram' => 'المخضرمون',
        'umawi' => 'العصر الأموي',
        'abbasi' => 'العصر العباسي',
    ],

    'occasions' => [
        'himma' => 'الهمّة',
        'najah' => 'النجاح',
        'sabr' => 'الصبر',
        'hikma' => 'الحكمة',
        'ilm' => 'العلم',
        'hubb' => 'الحب',
        'hanin' => 'الحنين',
        'wafa' => 'الوفاء والرثاء',
    ],

    'work_types' => [
        'muallaqa' => 'معلقة',
        'diwan' => 'ديوان',
        'collection' => 'مختارات',
    ],

    // رواق الشعراء: the order readers meet the poets in — the best-known names first.
    'featured_poets' => [
        'al-mutanabbi', 'imru-al-qays', 'antara', 'al-shafii', 'zuhair', 'majnun-layla', 'al-khansa',
        'abu-tammam', 'al-maarri', 'jarir', 'tarafa', 'labid', 'amr-ibn-kulthum', 'harith-ibn-hilliza',
    ],

    // Library text in a quote is built from up to this many verses.
    'max_selection' => 2,

    /*
     * Epigraphs (x-epigraph): for each page, the verses that suit it — one is shown at random on
     * each visit. Each entry is the start of a صدر without tashkeel; it must match exactly one
     * verse in the library (checked by LibraryTest). Verses of war, boasting or death are left out.
     */
    'epigraphs' => [
        // The front door: the word, the book, resolve.
        'welcome' => [
            'اعز مكان في الدني', 'علي قدر اهل العزم', 'لسان الفتي نصف', 'ستبدي لك الايام',
            'انا الذي نظر الاعمي', 'بقدر الكد تكتسب', 'واذا كانت النفوس كبارا', 'الخيل والليل والبيداء',
        ],
        // Above the quotes: everyday wisdom.
        'wisdom' => [
            'لسان الفتي نصف', 'ستبدي لك الايام', 'ومهما تكن عند امري', 'نعيب زماننا',
            'وعين الرضا عن كل', 'ليس الغبي بسيد', 'وتعظم في عين الصغير', 'اذا انت اكرمت الكريم',
            'الراي قبل شجاعه', 'ومن يك ذا فضل',
        ],
        // Books, reading and knowledge.
        'knowledge' => [
            'اعز مكان في الدني', 'واخبرني بان العلم نور', 'شكوت الي وكيع', 'ستبدي لك الايام', 'واعلم علم اليوم',
        ],
        // Replies: speech, and how words are received.
        'conversation' => [
            'لسان الفتي نصف', 'ومهما تكن عند امري', 'وعين الرضا عن كل', 'انا الذي نظر الاعمي',
            'ومن العداوه ما ينالك', 'واتعب من ناداك', 'ومن يجعل المعروف',
        ],
        // Logging in: coming back to the pen and the page.
        'return' => [
            'الخيل والليل والبيداء', 'انا الذي نظر الاعمي', 'اعز مكان في الدني', 'علي قدر اهل العزم', 'بقدر الكد تكتسب',
        ],
        // Signing up: setting out.
        'beginning' => [
            'اذا غامرت في شرف', 'علي قدر اهل العزم', 'بقدر الكد تكتسب', 'سافر تجد عوضا',
            'واذا كانت النفوس كبارا', 'الا في سبيل المجد',
        ],
        // The library's front: books and the craft of words.
        'library' => [
            'اعز مكان في الدني', 'واخبرني بان العلم نور', 'لسان الفتي نصف', 'انا الذي نظر الاعمي',
            'الخيل والليل والبيداء', 'ستبدي لك الايام',
        ],
        // مرسم الحروف: writing, pens and well-made words.
        'writing' => [
            'وجلا السيول عن الطلول', 'الخيل والليل والبيداء', 'انا الذي نظر الاعمي', 'انام ملء جفوني',
            'فتح الفتوح تعالي', 'لسان الفتي نصف',
        ],
    ],

];
