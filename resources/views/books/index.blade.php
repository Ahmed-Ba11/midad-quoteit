@extends('layouts.app')

@section('title', 'رفوف القرّاء')

@section('content')
    @php
        $genres = config('quotes.book_genres');
    @endphp

    <section class="shelves relative overflow-hidden">
    {{-- The wall: light from a window falls on it with the shadow of a shelf of books, the mark standing among them. --}}
    <div class="wall" aria-hidden="true">
        <div class="shafts"></div>
        <div class="cast">
            <svg viewBox="0 0 520 330">
                <rect class="glow" x="10" y="10" width="500" height="310" rx="6" />
                <g class="bk">
                    <rect x="40" y="250" width="440" height="12" />
                    <rect x="62" y="120" width="30" height="130" /><rect x="96" y="96" width="24" height="154" /><rect x="124" y="140" width="36" height="110" />
                    <rect x="164" y="108" width="22" height="142" /><rect x="190" y="126" width="30" height="124" />
                    <rect x="236" y="112" width="26" height="140" transform="rotate(-14 236 252)" />
                    <rect x="300" y="132" width="34" height="118" /><rect x="338" y="104" width="24" height="146" /><rect x="366" y="122" width="30" height="128" />
                    <svg x="400" y="150" width="100" height="100"><use href="#mark" /></svg>
                </g>
            </svg>
        </div>
    </div>

    <div class="wall-content mx-auto max-w-6xl px-4 py-10">
        <header class="flex flex-wrap items-end justify-between gap-6">
            <div class="max-w-2xl">
                <h1 class="font-display text-4xl font-bold sm:text-5xl">رفوف القـــرّاء</h1>
                <p class="mt-3 text-lg leading-relaxed text-muted">على هذه الرفوف كتبٌ وضعها قرّاءٌ مثلك، وترك كلٌّ منهم فيها جملةً لم تفارقه. تصفّح أجمل ما اقتُبس، أو ضع كتابك بينها.</p>
                <x-epigraph context="knowledge" class="mt-3" />
            </div>
            @auth
                <x-button :href="route('quotes.create')" icon="plus">اقتبس من كتاب تقرؤه</x-button>
            @else
                <x-button :href="route('login')" icon="plus">سجّل دخولك لتضيف كتابك</x-button>
            @endauth
        </header>

        <form method="GET" action="{{ route('books.index') }}" role="search" class="mt-8">
            @if ($genre)
                <input type="hidden" name="genre" value="{{ $genre }}">
            @endif
            <div class="relative">
                <label for="book-q" class="sr-only">ابحث عن كتاب</label>
                <x-icon name="search" class="pointer-events-none absolute start-4 top-1/2 -translate-y-1/2 text-muted" />
                <input id="book-q" name="q" type="search" value="{{ $term }}" maxlength="100" aria-describedby="book-q-hint"
                    placeholder="عنوان الكتاب أو اسم مؤلفه…"
                    class="block h-12 w-full rounded-full border border-line bg-surface ps-12 pe-4 text-ink placeholder:text-muted/70 focus:border-accent focus:outline-none focus:ring-3 focus:ring-accent/20">
            </div>
            <p id="book-q-hint" class="mt-2 text-xs text-muted">ابحث بالعنوان أو المؤلف، بلا حاجة إلى تشكيل أو همزات (حتى ١٠٠ حرف).</p>
        </form>

        <nav aria-label="أنواع الكتب" class="mt-5 flex flex-wrap gap-2">
            <x-chip :href="route('books.index', array_filter(['q' => $term]))" :active="$genre === null">الكل</x-chip>
            @foreach ($genres as $key => $label)
                <x-chip :href="route('books.index', array_filter(['q' => $term, 'genre' => $key]))" :active="$genre === $key">{{ $label }}</x-chip>
            @endforeach
        </nav>

        @if ($results !== null)
            <section aria-label="نتائج البحث" class="mt-10">
                @if ($results->isEmpty())
                    <x-empty-state title="لم نجد هذا الكتاب بعد" icon="book-open">
                        كن أول من يضيفه: اقتبس منه جملة، واكتب اسم الكتاب ومؤلفه، فيظهر هنا لكل القرّاء.
                        @auth
                            <x-button :href="route('quotes.create', array_filter(['book' => $term]))" class="mt-5">أضف «{{ \Illuminate\Support\Str::limit($term ?: 'كتابك', 30) }}» باقتباس منه</x-button>
                        @endauth
                    </x-empty-state>
                    @if ($term !== '')
                        <x-search-elsewhere :term="$term" here="books" class="mt-8" />
                    @endif
                @else
                    <ul class="grid grid-cols-2 gap-x-5 gap-y-8 sm:grid-cols-4 lg:grid-cols-6">
                        @foreach ($results as $book)
                            <li><x-book-tile :book="$book" /></li>
                        @endforeach
                    </ul>
                    <x-pagination :paginator="$results" class="mt-10" />
                @endif
            </section>
        @else
            @foreach (['الأكثر اقتباساً' => $mostQuoted, 'أُضيفت حديثاً' => $newest] as $shelfTitle => $books)
                @if ($books->isNotEmpty())
                    <section class="mt-12" aria-label="{{ $shelfTitle }}">
                        <h2 class="font-display text-2xl font-bold">{{ $shelfTitle }}</h2>
                        {{-- The covers (2:3, 144px wide; 160px from sm) stand on a wooden ledge the width of the row. --}}
                        <div class="relative mt-5">
                            <div class="ledge absolute inset-x-0 top-[216px] sm:top-[240px]" aria-hidden="true"></div>
                            <ul class="relative flex overflow-x-auto pb-4">
                                @foreach ($books as $book)
                                    <li class="w-41 shrink-0 px-2.5 sm:w-45"><x-book-tile :book="$book" /></li>
                                @endforeach
                            </ul>
                        </div>
                    </section>
                @endif
            @endforeach

            @if ($newest->isEmpty())
                <x-empty-state title="الرفوف تنتظر أول كتاب" icon="book-open" class="mt-12">
                    اقتبس جملةً من كتاب تقرؤه واذكر اسمه، فيُضاف هنا لكل القرّاء.
                </x-empty-state>
            @endif
        @endif
    </div>
    </section>
@endsection
