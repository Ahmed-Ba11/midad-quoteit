@extends('layouts.app')

@section('title', 'الرئيسية')

@section('content')
    @php
        $editingId = request()->integer('edit') ?: null;
        $sorts = ['latest' => 'الأحدث', 'likes' => 'الأكثر إعجاباً', 'replies' => 'الأكثر نقاشاً'];
        $keep = array_filter(['q' => $filters['q'], 'sort' => $filters['sort'] === 'latest' ? null : $filters['sort'], 'original' => $filters['original'] ? '1' : null]);
    @endphp

    @if ($isFrontPage)
        {{-- The front door: what رقيم is, today's quote, and a way in for everyone. --}}
        <section class="hero-ink relative isolate overflow-hidden">
            <x-ornament name="girih" class="girih absolute inset-0 -z-10 h-full w-full" />
            <div class="mx-auto grid max-w-6xl items-center gap-10 px-4 py-14 lg:grid-cols-[minmax(0,1fr)_minmax(0,1.1fr)] lg:py-20">
                <div class="text-center lg:text-start">
                    <p class="inline-flex items-center gap-2 rounded-full border border-[#C9A45C]/40 px-3 py-1 text-xs text-[#E2C27E]">
                        <x-ornament name="star" class="size-3" /> منصّة الاقتباس العربي
                    </p>
                    <h1 class="mt-5 font-display text-4xl font-black leading-tight text-[#F2ECE2] [text-wrap:balance] sm:text-5xl">
                        لكلّ قارئٍ <span class="text-[#E2C27E]">جملةٌ</span> بقيت معه
                    </h1>
                    <p class="mx-auto mt-4 max-w-md text-lg leading-relaxed text-[#F2ECE2]/75 lg:mx-0">
                        اقتبس من كتاب تقرؤه أو بيتٍ تحفظه، صمّمه بخطٍّ يليق به، وشاركه مع من يقدّر الكلمة.
                    </p>
                    <x-epigraph context="welcome" class="mx-auto mt-6 max-w-md text-base !text-[#E2C27E] lg:mx-0 [&_a]:!text-[#F2ECE2]/60" />

                    <div class="mt-8 flex flex-wrap justify-center gap-3 lg:justify-start">
                        @auth
                            <x-button popovertarget="quote-popup" icon="plus" class="!bg-[#E2C27E] !text-[#1C1A18]">شارك اقتباساً</x-button>
                        @else
                            <x-button :href="route('signup')" icon="plus" class="!bg-[#E2C27E] !text-[#1C1A18]">انضم وشارك اقتباسك</x-button>
                        @endauth
                        <x-button :href="route('library.index')" variant="secondary" icon="book-open" class="!border-[#F2ECE2]/40 !text-[#F2ECE2] hover:!bg-[#F2ECE2]/10">خزانة الأدب</x-button>
                    </div>

                    {{-- Only numbers that mean something: the verified verses always; quotes and readers once there are ten. --}}
                    <dl class="mt-10 flex justify-center gap-8 text-[#F2ECE2] lg:justify-start">
                        @foreach (['quotes' => 'اقتباس', 'readers' => 'قارئ', 'verses' => 'بيتاً موثّقاً'] as $key => $label)
                            @continue($key !== 'verses' && $counts[$key] < \App\Http\Controllers\QuoteController::QUOTE_OF_THE_DAY_FROM)
                            <div class="flex flex-col-reverse">
                                <dt class="text-xs text-[#F2ECE2]/60">{{ $label }}</dt>
                                <dd class="font-display text-3xl font-bold text-[#E2C27E]">{{ number_format($counts[$key]) }}</dd>
                            </div>
                        @endforeach
                    </dl>
                </div>

                {{-- The day's leaf: a reader's quote once there are enough to choose from, else a verse. --}}
                @if ($quoteOfTheDay)
                    <x-quote-of-the-day :quote="$quoteOfTheDay" class="quote-leaf" />
                @elseif ($verseOfTheDay)
                    <x-quote-of-the-day :verse="$verseOfTheDay" class="quote-leaf" />
                @endif
            </div>
        </section>

        {{-- أقلام رقيم: readers and what they chose to share publicly — a way to visit their corners. --}}
        @if ($pens->isNotEmpty())
            <section aria-labelledby="pens-title" class="border-b border-line bg-surface">
                <div class="mx-auto max-w-6xl px-4 py-8">
                    <div class="flex flex-wrap items-baseline justify-between gap-2">
                        <h2 id="pens-title" class="font-display text-2xl font-bold">أقـــلام رقيم</h2>
                        <p class="text-sm text-muted">قرّاء يشاركون ما بقي معهم؛ زُر أركانهم.</p>
                    </div>
                    <ul class="-mx-4 mt-5 flex snap-x gap-3 overflow-x-auto px-4 pb-2">
                        @foreach ($pens as $pen)
                            <li class="snap-start">
                                <a href="{{ route('profile.show', $pen) }}"
                                    class="flex w-52 items-center gap-3 rounded-2xl border border-line bg-paper p-3 transition hover:-translate-y-0.5 hover:border-accent hover:shadow-md">
                                    <x-avatar :user="$pen" size="size-12" />
                                    <span class="min-w-0">
                                        <span class="block truncate font-medium">{{ $pen->name }}</span>
                                        <span class="block truncate text-xs text-muted">{{ $pen->public_quotes_count }} اقتباس{{ $pen->bio ? ' · '.$pen->bio : '' }}</span>
                                    </span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </section>
        @endif
    @endif

    <section class="feed relative overflow-hidden">
    {{-- The wall behind the quotes: afternoon light, an arched window, and the mark's shadow on its sill. --}}
    <div class="wall" aria-hidden="true">
        <div class="shafts"></div>
        <div class="cast">
            <svg viewBox="0 0 600 900">
                <path class="glow" d="M60 900 V330 C60 170 180 70 300 30 C420 70 540 170 540 330 V900 Z" />
                <g class="bars">
                    <rect x="288" y="40" width="24" height="860" />
                    <rect x="60" y="440" width="480" height="22" />
                    <path d="M60 330 C60 170 180 70 300 30 C420 70 540 170 540 330 L520 330 C520 182 410 92 300 54 C190 92 80 182 80 330 Z" />
                </g>
                <g class="mk"><svg x="110" y="520" width="380" height="380"><use href="#mark" /></svg></g>
            </svg>
        </div>
    </div>

    <div class="wall-content mx-auto max-w-6xl px-4 py-10">
        <header class="mb-6 flex flex-wrap items-end justify-between gap-4">
            <div>
                <h2 class="font-display text-4xl font-bold">{{ $isFrontPage ? 'أحدث الاقتباســات' : 'الاقتباســات' }}</h2>
                <x-epigraph context="wisdom" class="mt-2 max-w-xl" />
            </div>

            @guest
                <x-button :href="route('login')" variant="ghost" icon="plus">سجّل دخولك لتشارك اقتباساً</x-button>
            @endguest
        </header>

        {{-- Search + sort (GET, so results can be shared as a link) --}}
        <form method="GET" action="{{ route('home') }}" role="search" class="flex flex-col gap-3 sm:flex-row">
            @if ($filters['category'])
                <input type="hidden" name="category" value="{{ $filters['category'] }}">
            @endif
            @if ($filters['original'])
                <input type="hidden" name="original" value="1">
            @endif

            <div class="relative flex-1">
                <label for="q" class="sr-only">ابحث في الاقتباسات</label>
                <x-icon name="search" class="pointer-events-none absolute start-4 top-1/2 -translate-y-1/2 text-muted" />
                <input id="q" name="q" type="search" aria-describedby="search-hint" value="{{ $filters['q'] }}" maxlength="100"
                    placeholder="ابحث في النص أو القائل أو الكتاب…"
                    class="block h-12 w-full rounded-full border border-line bg-surface ps-12 pe-4 text-ink placeholder:text-muted/70 focus:border-accent focus:outline-none focus:ring-3 focus:ring-accent/20">
            </div>

            <div class="flex gap-2">
                <label for="sort" class="sr-only">الترتيب</label>
                <select id="sort" name="sort" aria-describedby="search-hint" onchange="this.form.requestSubmit()"
                    class="h-12 flex-1 rounded-full border border-line bg-surface px-5 text-ink focus:border-accent focus:outline-none focus:ring-3 focus:ring-accent/20">
                    @foreach ($sorts as $value => $label)
                        <option value="{{ $value }}" @selected($filters['sort'] === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <x-button type="submit" class="h-12">بحث</x-button>
            </div>
        </form>

        <p id="search-hint" class="mt-2 text-xs text-muted">ابحث بكلمة من النص أو باسم القائل أو الكتاب (حتى ١٠٠ حرف)، واختر الترتيب.</p>

        {{-- Category chips --}}
        <nav aria-label="التصنيفات" class="no-scrollbar relative -mx-4 mt-5 flex gap-2 overflow-x-auto px-4 pb-1 sm:mx-0 sm:flex-wrap sm:overflow-visible sm:px-0 [&>*]:shrink-0">
            <x-chip :href="route('home', \Illuminate\Support\Arr::except($keep, 'original'))" :active="$filters['category'] === null && ! $filters['original']">الكل</x-chip>
            <x-chip :href="route('home', [...$keep, 'original' => $filters['original'] ? null : '1'])" :active="$filters['original']">
                <x-icon name="feather" class="size-4" /> بأقلام القرّاء
            </x-chip>
            <span aria-hidden="true" class="mx-1 h-6 w-px self-center bg-line"></span>
            @foreach ($categories as $category)
                <x-chip :href="route('home', [...$keep, 'category' => $category->slug])"
                    :active="$filters['category'] === $category->slug">{{ $category->name }}</x-chip>
            @endforeach
        </nav>

        <div class="mt-8">
            @if ($quotes->isEmpty())
                @if ($filters['q'] !== '' || $filters['category'] || $filters['original'])
                    <x-empty-state title="لا نتائج" icon="search">
                        لم نجد اقتباساً يطابق بحثك. جرّب كلمة أخرى أو
                        <a href="{{ route('home') }}" class="font-medium text-accent hover:underline">اعرض الكل</a>.
                    </x-empty-state>
                    @if ($filters['q'] !== '')
                        <x-search-elsewhere :term="$filters['q']" here="quotes" class="mt-8" />
                    @endif
                @else
                    <x-empty-state title="لا توجد اقتباسات بعد">
                        كن أول من يشارك اقتباساً يستحق أن يُقرأ.
                    </x-empty-state>
                @endif
            @else
                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($quotes as $quote)
                        @if ($quote->id === $editingId && auth()->user()?->can('update', $quote))
                            <form method="POST" action="{{ route('edit_quote', $quote) }}"
                                class="flex flex-col gap-4 rounded-3xl border-2 border-accent bg-surface p-5">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="_form" value="edit">

                                <x-textarea name="content" id="content-{{ $quote->id }}" label="تعديل الاقتباس"
                                    hint="من ٣ إلى ١٥٥ حرفاً" rows="5" minlength="3" maxlength="155" required autofocus
                                    :value="old('_form') === 'edit' ? old('content') : $quote->content"
                                    :show-error="old('_form') === 'edit'" />

                                <div class="flex gap-2">
                                    <x-button type="submit">حفظ</x-button>
                                    <x-button variant="ghost" :href="request()->fullUrlWithoutQuery('edit')">إلغاء</x-button>
                                </div>
                            </form>
                        @else
                            <x-quote-card :quote="$quote" />
                        @endif
                    @endforeach
                </div>

                <x-pagination :paginator="$quotes" class="mt-10" />
            @endif
        </div>
    </div>
    </section>
@endsection
