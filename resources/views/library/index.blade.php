@extends('layouts.app')

@section('title', 'خزانة الأدب')

@section('content')
    @php
        $occasions = config('library.occasions');
        $palettes = array_values(config('quotes.palettes'));
        $tilts = [-2, 1.5, -1, 2.2, -1.6, 1];
        $sections = [
            'shelf' => 'رفّ الدواوين',
            'poets' => 'رواق الشعراء',
            'occasions' => 'لكلّ مقامٍ مقال',
            'sayings' => 'جدار المأثورات',
        ];
    @endphp

    {{-- Hero: the library's door. Dark ink, a faint girih lattice and the verse that names the place. --}}
    <section class="relative isolate overflow-hidden bg-[#1C1A18] text-[#F2ECE2]">
        <x-ornament name="girih" class="girih absolute inset-0 -z-10 h-full w-full" />
        <x-paper-texture class="-z-10 opacity-40" />

        <div class="mx-auto max-w-4xl px-4 pb-14 pt-16 text-center sm:pt-20">
            <x-ornament name="star" class="mx-auto size-7 text-[#C9A45C]" />
            <h1 class="logo mt-4 font-display text-5xl font-black sm:text-7xl">خزانـــة الأدب</h1>

            <x-epigraph context="library" layout="verse" class="mx-auto mt-8 max-w-2xl text-xl text-[#F2ECE2]/90 sm:text-2xl" />

            <form method="GET" action="{{ route('library.index') }}#results" role="search" class="relative mx-auto mt-10 max-w-xl">
                <label for="library-q" class="sr-only">ابحث في المكتبة</label>
                <x-icon name="search" class="pointer-events-none absolute start-5 top-1/2 size-5 -translate-y-1/2 text-[#C9A45C]" />
                <input id="library-q" name="q" type="search" aria-describedby="library-search-hint" value="{{ $term }}" maxlength="60"
                    placeholder="ابحث عن بيت، أو شاعر، أو ديوان…"
                    class="block h-14 w-full rounded-full border border-[#C9A45C]/40 bg-[#F2ECE2]/5 ps-14 pe-28 text-lg text-[#F2ECE2] placeholder:text-[#F2ECE2]/45 focus:border-[#C9A45C] focus:outline-none focus:ring-3 focus:ring-[#C9A45C]/25">
                <button type="submit" class="absolute end-2 top-1/2 h-10 -translate-y-1/2 rounded-full bg-[#C9A45C] px-5 font-medium text-[#1C1A18] transition hover:bg-[#E2C27E]">ابحث</button>
            </form>

            <p id="library-search-hint" class="mt-3 text-xs text-[#F2ECE2]/55">كلمة من البيت، أو اسم شاعر، أو عنوان ديوان (حتى ٦٠ حرفاً)</p>

            <p class="mt-5 text-sm text-[#F2ECE2]/60">
                {{ $counts['poets'] }} شاعراً، {{ $counts['works'] }} بين ديوانٍ ومعلقة، و{{ $counts['verses'] }} بيتاً مختاراً من عيون الشعر العربي
            </p>
        </div>

        <nav aria-label="أقسام المكتبة" class="border-t border-[#C9A45C]/20">
            <ul class="mx-auto flex max-w-4xl justify-start gap-1 overflow-x-auto px-4 py-2 text-sm sm:justify-center">
                @foreach ($sections as $id => $label)
                    <li><a href="#{{ $id }}" class="block whitespace-nowrap rounded-full px-4 py-2 text-[#F2ECE2]/75 transition hover:bg-[#F2ECE2]/10 hover:text-[#F2ECE2]">{{ $label }}</a></li>
                @endforeach
            </ul>
        </nav>
    </section>

    <div class="library-paper">
    <div class="mx-auto max-w-6xl px-4">
        {{-- Search results --}}
        @if ($results !== null)
            <section id="results" aria-labelledby="results-title" class="scroll-mt-20 py-12">
                <div class="flex flex-wrap items-end justify-between gap-3">
                    <h2 id="results-title" class="font-display text-3xl font-bold">نتائج «{{ $term }}»</h2>
                    <a href="{{ route('library.index') }}" class="text-sm text-accent hover:underline">مسح البحث</a>
                </div>

                @if ($matchingPoets->isNotEmpty())
                    <div class="mt-5 flex flex-wrap gap-2">
                        @foreach ($matchingPoets as $poet)
                            <x-chip :href="route('library.poet', $poet)">{{ $poet->name }}</x-chip>
                        @endforeach
                    </div>
                @endif

                @if ($results->isEmpty())
                    <x-library-miss :term="$term" class="mt-6" />
                @else
                    <ol class="mt-6 divide-y divide-line overflow-hidden rounded-3xl border border-line bg-surface">
                        @foreach ($results as $verse)
                            <li class="p-5 sm:p-6">
                                <x-verse layout="paired" :sadr="$verse->sadr" :ajuz="$verse->ajuz" class="text-lg" />
                                <div class="mt-3 flex flex-wrap items-center justify-center gap-x-4 gap-y-2">
                                    <a href="{{ route('library.work', $verse->work) }}" class="text-sm text-muted hover:text-accent">{{ $verse->work->poet->name }}</a>
                                    <x-external-link :href="\App\Support\Aldiwan::verse($verse->sadr)" class="inline-flex min-h-11 items-center text-xs text-muted">أكمل القصيدة في الديوان</x-external-link>
                                    <x-pick-button :id="$verse->id" :text="$verse->sadr.' / '.$verse->ajuz" />
                                </div>
                            </li>
                        @endforeach
                    </ol>
                @endif
            </section>
        @endif

        {!! \Illuminate\Support\Facades\Cache::remember('library.collections.'.$collectionsVersion, now()->addDay(), fn () => view('library.partials.collections', ['muallaqat' => $muallaqat, 'diwans' => $diwans, 'poets' => $poets])->render()) !!}

        <x-peninsula-voices class="mb-16" />

        {{-- لكلّ مقامٍ مقال: pick a mood, get the verse for it. --}}
        <section id="occasions" aria-labelledby="occasions-title" class="scroll-mt-20 border-t border-line py-16" data-occasions>
            <header class="mx-auto max-w-2xl text-center">
                <h2 id="occasions-title" class="font-display text-4xl font-bold">لكلّ مقامٍ مقـــال</h2>
                <p class="mt-3 text-muted">ما الذي في خاطرك اليوم؟ اختر مقاماً، ودع الشعراء يقولون عنك.</p>
            </header>

            <div role="tablist" aria-label="المقامات" class="mt-8 flex flex-wrap justify-center gap-2">
                @foreach ($occasions as $key => $label)
                    @if ($occasionVerses->has($key))
                        <button type="button" role="tab" data-occasion="{{ $key }}" aria-selected="{{ $loop->first ? 'true' : 'false' }}"
                            class="h-10 rounded-full border border-line bg-surface px-5 text-sm transition hover:border-accent aria-selected:border-ink aria-selected:bg-ink aria-selected:text-paper">
                            {{ $label }}
                        </button>
                    @endif
                @endforeach
            </div>

            <div class="relative mx-auto mt-8 max-w-3xl overflow-hidden rounded-[28px] bg-[#1C1A18] text-[#F2ECE2] shadow-xl">
                <x-paper-texture />
                <div aria-hidden="true" class="pointer-events-none absolute inset-3 rounded-[20px] border border-[#C9A45C]/35"></div>

                @foreach ($occasionVerses as $key => $verses)
                    <div data-occasion-group="{{ $key }}" @if (! $loop->first) hidden @endif>
                        @foreach ($verses as $verse)
                            <div data-slide @if (! $loop->first) hidden @endif class="relative px-5 py-12 text-center sm:px-16 sm:py-14">
                                <x-verse :sadr="$verse->sadr" :ajuz="$verse->ajuz" class="text-xl sm:text-3xl" />
                                <p class="mt-6 text-sm text-[#F2ECE2]/60">
                                    <a href="{{ route('library.poet', $verse->work->poet) }}" class="hover:text-[#C9A45C]">{{ $verse->work->poet->name }}</a>
                                    · <a href="{{ route('library.work', $verse->work) }}" class="hover:text-[#C9A45C]">{{ $verse->work->title }}</a>
                                </p>
                                <x-pick-button :id="$verse->id" :text="$verse->sadr.' / '.$verse->ajuz" label="اختر هذا البيت" class="mt-6 text-[#C9A45C]" />
                            </div>
                        @endforeach
                    </div>
                @endforeach

                <div class="relative flex items-center justify-between border-t border-[#C9A45C]/20 px-4 py-3">
                    <button type="button" data-slide-prev aria-label="البيت السابق" class="grid size-11 place-items-center rounded-full text-[#F2ECE2]/70 transition hover:bg-[#F2ECE2]/10 hover:text-[#F2ECE2]">
                        <x-icon name="chevron-right" />
                    </button>
                    <div data-slide-dots class="flex flex-wrap justify-center gap-1.5"></div>
                    <button type="button" data-slide-next aria-label="البيت التالي" class="grid size-11 place-items-center rounded-full text-[#F2ECE2]/70 transition hover:bg-[#F2ECE2]/10 hover:text-[#F2ECE2]">
                        <x-icon name="chevron-left" />
                    </button>
                </div>
            </div>
        </section>

        {{-- جدار المأثورات: plaques hung on nails. --}}
        <section id="sayings" aria-labelledby="sayings-title" class="scroll-mt-20 border-t border-line py-16">
            <header class="mx-auto max-w-2xl text-center">
                <h2 id="sayings-title" class="font-display text-4xl font-bold">جدار المأثـــورات</h2>
                <p class="mt-3 text-muted">أمثال وأقوال جرت على ألسنة العرب حتى صارت حكمة الجميع.</p>
            </header>

            <ul class="mt-12 grid gap-x-8 gap-y-14 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($sayings as $saying)
                    @php $palette = $palettes[$loop->index % count($palettes)]; @endphp
                    {{-- Nine plaques at first (a different nine each visit); the rest behind «more». --}}
                    <li id="saying-{{ $saying->id }}" @class(['relative scroll-mt-24 pt-12', 'saying-more hidden' => $loop->index >= 9])>
                        <svg aria-hidden="true" viewBox="0 0 200 48" preserveAspectRatio="none" class="absolute inset-x-[18%] top-0 h-12 w-[64%] text-muted">
                            <path d="M100 6 4 48M100 6l96 42" fill="none" stroke="currentColor" stroke-width="1.2" vector-effect="non-scaling-stroke" />
                        </svg>
                        <span aria-hidden="true" class="absolute left-1/2 top-0 size-3 -translate-x-1/2 rounded-full bg-gradient-to-br from-[#e2c27e] to-[#7d6229] shadow"></span>

                        <figure class="plaque relative overflow-hidden rounded-lg px-6 pb-5 pt-7 text-center shadow-lg"
                            style="--tilt: {{ $tilts[$loop->index % count($tilts)] }}deg; background: {{ $palette['bg'] }}; color: {{ $palette['text'] }};">
                            <x-paper-texture />
                            <blockquote class="relative font-text text-2xl leading-relaxed">{{ $saying->text }}</blockquote>
                            @if ($saying->meaning)
                                <p class="relative mt-2 text-sm opacity-85">{{ $saying->meaning }}</p>
                            @endif
                            <figcaption class="relative mt-3 text-xs opacity-75">
                                @if ($saying->source_url)
                                    <x-external-link :href="$saying->source_url" class="inline-flex min-h-11 items-center !text-current underline-offset-4">— {{ $saying->source }}</x-external-link>
                                @else
                                    — {{ $saying->source }}
                                @endif
                            </figcaption>
                            <div class="relative mt-4 flex flex-wrap items-center justify-center gap-2">
                                <x-pick-button type="saying" :id="$saying->id" :text="$saying->text" class="h-9" />
                                @auth
                                    @php $onWall = in_array($saying->id, $myWall, true); @endphp
                                    <form method="POST" action="{{ route('sayings.wall', $saying) }}">
                                        @csrf
                                        <button type="submit" aria-pressed="{{ $onWall ? 'true' : 'false' }}"
                                            class="inline-flex h-9 items-center gap-1.5 rounded-full border border-current/25 px-3 text-sm transition hover:border-current aria-pressed:bg-current/15">
                                            <x-icon name="pin" @class(['size-4', 'fill-current' => $onWall]) />
                                            {{ $onWall ? 'على جداري' : 'علّقه على جداري' }}
                                        </button>
                                    </form>
                                @endauth
                            </div>
                        </figure>
                    </li>
                @endforeach
            </ul>

            @if ($sayings->count() > 9)
                <div class="mt-10 text-center">
                    <button type="button" data-show-more=".saying-more"
                        class="inline-flex min-h-11 items-center gap-2 rounded-full border border-line bg-surface px-6 text-sm font-medium transition hover:border-accent hover:text-accent">
                        <x-icon name="plus" class="size-4" /> اعرض كل المأثورات ({{ $sayings->count() }})
                    </button>
                </div>
            @endif
        </section>

        {{-- دواوين القرّاء: poets the readers brought, kept apart from the verified shelves above. --}}
        <section id="readers-diwans" aria-labelledby="readers-diwans-title" class="scroll-mt-20 border-t border-line py-16">
            <div class="flex flex-col items-center gap-8 rounded-3xl border border-line bg-surface p-8 text-center md:flex-row md:text-start">
                <x-ornament name="shamsa" class="size-20 shrink-0 text-gold" />
                <div class="flex-1">
                    <h2 id="readers-diwans-title" class="font-display text-3xl font-bold">شاعرك ليس هنا؟</h2>
                    <p class="mt-2 text-muted">
                        أنشئ له ديواناً في «دواوين القرّاء» وانقل أبياته مع مصدرها، ثم صمّم منها في مرسم الحروف.
                        يبقى دفترك خاصاً، ولا يُنشر للجميع إلا شعر من تُوفّي قبل أكثر من ٥٠ سنة.
                    </p>
                    @if ($readersDiwans->isNotEmpty())
                        <p class="mt-3 flex flex-wrap justify-center gap-2 md:justify-start">
                            @foreach ($readersDiwans as $diwan)
                                <x-chip :href="route('diwans.show', $diwan)">{{ $diwan->poet_name }} · {{ $diwan->title }}</x-chip>
                            @endforeach
                        </p>
                    @endif
                </div>
                <div class="flex shrink-0 flex-col gap-2">
                    <x-button :href="route('diwans.index')" icon="scroll">تصفّح دواوين القرّاء</x-button>
                    @auth
                        <x-button :href="route('diwans.create')" variant="ghost" icon="plus">ديوان جديد</x-button>
                    @endauth
                </div>
            </div>
        </section>

    </div>
    </div>

    <x-selection-bar />
@endsection
