@props(['term'])

{{--
    When خزانة الأدب has no verse for a search: say so kindly, and hand the reader straight to where the
    full diwans are — the search opens on that site (a plain link; nothing is fetched from it) — then
    invite them back to quote what they find, or to start a diwan for a poet who is not here.
--}}
@php
    $aldiwan = 'https://www.aldiwan.net/search.php?'.http_build_query(['q' => $term]);
    $wikisource = 'https://ar.wikisource.org/w/index.php?'.http_build_query(['search' => $term, 'fulltext' => 1]);
    $google = 'https://www.google.com/search?'.http_build_query(['q' => '"'.$term.'" شعر']);
    $outLink = 'inline-flex min-h-11 items-center gap-2 rounded-full px-5 text-sm font-medium transition';
@endphp

<div {{ $attributes->class('overflow-hidden rounded-3xl border border-line bg-surface') }}>
    <div class="flex flex-col items-center gap-6 p-6 text-center sm:p-10 md:flex-row md:text-start">
        <x-ornament name="shamsa" class="size-20 shrink-0 text-gold" />
        <div class="flex-1">
            <h3 class="font-display text-2xl font-bold">لم نجد «{{ $term }}» على رفوف الخزانة</h3>
            <p class="mt-2 leading-relaxed text-muted">
                تضم الخزانة عيون الأبيات المحقّقة فقط. أكمل بحثك في الدواوين الكاملة، ثم عُد لتقتبس ما وجدت.
            </p>
        </div>
    </div>

    <div class="flex flex-wrap justify-center gap-2 border-t border-line bg-paper/60 px-6 py-5 md:justify-start">
        <a href="{{ $aldiwan }}" target="_blank" rel="noopener noreferrer external" class="{{ $outLink }} bg-accent text-paper hover:opacity-90">
            <x-icon name="search" class="size-4" /> ابحث في الديوان <span class="sr-only">(موقع خارجي)</span>
        </a>
        <a href="{{ $wikisource }}" target="_blank" rel="noopener noreferrer external" class="{{ $outLink }} border border-line hover:border-accent hover:text-accent">
            ويكي مصدر <x-icon name="external" class="size-3.5" /><span class="sr-only">(موقع خارجي)</span>
        </a>
        <a href="{{ $google }}" target="_blank" rel="noopener noreferrer external" class="{{ $outLink }} border border-line hover:border-accent hover:text-accent">
            جوجل <x-icon name="external" class="size-3.5" /><span class="sr-only">(موقع خارجي)</span>
        </a>
    </div>

    <div class="grid gap-3 border-t border-line p-6 text-sm sm:grid-cols-2">
        <a href="{{ auth()->check() ? route('quotes.create') : route('login') }}" class="flex items-start gap-3 rounded-2xl p-3 transition hover:bg-accent-soft">
            <x-icon name="quill" class="mt-0.5 size-5 shrink-0 text-accent" />
            <span><span class="block font-medium">وجدتَه؟ اقتبسه بنفسك</span><span class="text-muted">بيتاً أو بيتين، مع قائله ومصدره.</span></span>
        </a>
        <a href="{{ auth()->check() ? route('diwans.create') : route('login') }}" class="flex items-start gap-3 rounded-2xl p-3 transition hover:bg-accent-soft">
            <x-icon name="scroll" class="mt-0.5 size-5 shrink-0 text-accent" />
            <span><span class="block font-medium">شاعرك ليس هنا؟</span><span class="text-muted">أنشئ له ديواناً في دواوين القرّاء.</span></span>
        </a>
    </div>

    <p class="border-t border-line px-6 py-4 text-center text-xs text-muted">
        وإن لم تجده هناك أيضاً، فلعلّه بيتٌ لم يُدوَّن بعد — فكن أول من يرقمه.
    </p>
</div>
