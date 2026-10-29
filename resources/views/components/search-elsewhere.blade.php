@props(['term', 'here' => 'quotes'])

{{--
    When a search finds nothing: try the other parts of مِداد, search trusted sites (plain links that
    open their own search — nothing is fetched from them), or quote the text yourself with its source.
--}}
@php
    $inside = array_filter([
        'quotes' => $here !== 'quotes' ? ['الاقتباسات', route('home', ['q' => $term])] : null,
        'library' => $here !== 'library' ? ['خزانة الأدب', route('library.index', ['q' => $term])] : null,
        'books' => $here !== 'books' ? ['رفوف القرّاء', route('books.index', ['q' => $term])] : null,
    ]);
    $outside = [
        'ويكي مصدر' => 'https://ar.wikisource.org/w/index.php?'.http_build_query(['search' => $term, 'fulltext' => 1]),
        'الديوان' => 'https://www.aldiwan.net/search.php?'.http_build_query(['q' => $term]),
        'جوجل' => 'https://www.google.com/search?'.http_build_query(['q' => '"'.$term.'"']),
    ];
@endphp

<div {{ $attributes->class('mx-auto max-w-2xl space-y-6 text-start') }}>
    @if ($inside)
        <div>
            <p class="text-sm font-medium">ابحث عن «{{ $term }}» في مِداد</p>
            <div class="mt-2 flex flex-wrap gap-2">
                @foreach ($inside as [$label, $url])
                    <x-chip :href="$url">{{ $label }}</x-chip>
                @endforeach
            </div>
        </div>
    @endif

    <div>
        <p class="text-sm font-medium">أو ابحث عنه خارج مِداد</p>
        <div class="mt-2 flex flex-wrap gap-2">
            @foreach ($outside as $label => $url)
                <x-external-link :href="$url"
                    class="h-9 rounded-full border border-line bg-surface px-4 text-sm no-underline hover:border-accent hover:text-accent hover:no-underline">{{ $label }}</x-external-link>
            @endforeach
        </div>
        <p class="mt-1.5 text-xs text-muted">تُفتح نتائج البحث في الموقع نفسه، في تبويب جديد.</p>
    </div>

    <div class="rounded-2xl border border-dashed border-line p-4">
        <p class="text-sm font-medium">وجدتَه في مكان آخر؟</p>
        <p class="mt-1 text-sm text-muted">اقتبس بيتاً أو جملة منه في رواق الحرف، واذكر قائله ومصدره، فيصل إليه كل من يبحث عنه بعدك.</p>
        @auth
            <x-button :href="route('quotes.create')" variant="secondary" icon="feather" class="mt-3">اقتبسه بنفسك</x-button>
        @else
            <x-button :href="route('login')" variant="secondary" class="mt-3">سجّل دخولك لتقتبسه</x-button>
        @endauth
    </div>
</div>
