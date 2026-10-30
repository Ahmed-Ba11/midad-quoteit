@extends('layouts.app')

@section('title', $isOwner ? 'ركني' : $user->name)

@section('content')
    @php
        $colors = $user->accentColors();
        // Only the tabs this viewer may open (ProfileController decides which).
        $tabItems = collect([
            'quotes' => ['label' => $isOwner ? 'اقتباساتي' : 'اقتباساته', 'icon' => 'type'],
            'original' => ['label' => $isOwner ? 'بقلمي' : 'بقلمه', 'icon' => 'feather'],
            'anthologies' => ['label' => $isOwner ? 'مختاراتي' : 'مختاراته', 'icon' => 'layers'],
            'poets' => ['label' => $isOwner ? 'شعرائي' : 'شعراؤه', 'icon' => 'user'],
            'diwans' => ['label' => $isOwner ? 'دواويني' : 'دواوينه', 'icon' => 'scroll'],
            'saved' => ['label' => 'المحفوظات', 'icon' => 'bookmark', 'private' => true],
            'books' => ['label' => 'كتبي', 'icon' => 'book-open', 'private' => true],
        ])->only($tabs);
    @endphp

    <div class="mx-auto max-w-6xl px-4 py-10">
        {{-- ركني: the reader's card in their own colour, beside the quote they pinned. --}}
        <div @class(['grid gap-6', 'lg:grid-cols-[minmax(0,5fr)_minmax(0,6fr)]' => $pinned || $isOwner])>
            <header class="relative isolate flex flex-col overflow-hidden rounded-3xl p-7 shadow-lg ring-1 ring-black/10 sm:p-9"
                style="background: {{ $colors['bg'] }}; color: {{ $colors['text'] }}">
                <x-ornament name="girih" class="girih absolute inset-0 -z-10 h-full w-full" style="color: {{ $colors['highlight'] }}" />

                <p class="flex items-center gap-2 text-xs opacity-75">
                    <x-ornament name="star" class="size-3.5" style="color: {{ $colors['highlight'] }}" />
                    {{ $isOwner ? 'ركنك في مِداد: ما كتبتَ، وما جمعتَ، وما بقي معك' : 'ركنٌ من أركان مِداد' }}
                </p>

                <div class="mt-6 flex items-center gap-5">
                    <span class="grid size-20 shrink-0 place-items-center rounded-full font-display text-4xl font-bold ring-2 ring-current/20"
                        style="background: {{ $colors['text'] }}; color: {{ $colors['bg'] }}">
                        {{ mb_substr($user->name, 0, 1) }}
                    </span>
                    <div class="min-w-0">
                        <h1 class="truncate font-display text-4xl font-black" style="color: {{ $colors['highlight'] }}">{{ $user->name }}</h1>
                        @if ($user->bio)
                            <p class="mt-1 font-text text-lg leading-relaxed opacity-90">{{ $user->bio }}</p>
                        @elseif ($isOwner)
                            <p class="mt-1 text-sm opacity-70">أضف نبذة تعرّف القرّاء بك.</p>
                        @endif
                    </div>
                </div>

                <div class="min-h-8 flex-1"></div>
                <dl class="grid grid-cols-4 gap-2 border-t pt-5 sm:gap-4" style="border-color: {{ $colors['text'] }}26">
                    @foreach ($stats as $label => $value)
                        <div class="flex flex-col-reverse text-center">
                            <dt class="text-xs opacity-75">{{ $label }}</dt>
                            <dd class="font-display text-3xl font-bold">{{ $value }}</dd>
                        </div>
                    @endforeach
                </dl>

                @if ($isOwner)
                    {{-- Customise: bio and the corner's colour. --}}
                    <details class="group mt-5 rounded-2xl bg-paper text-ink open:shadow-lg" @if ($errors->has('bio') || $errors->has('accent')) open @endif>
                        <summary class="flex cursor-pointer list-none items-center justify-center gap-2 px-4 py-2.5 text-sm font-medium [&::-webkit-details-marker]:hidden">
                            <x-icon name="palette" class="size-4" />
                            <span class="group-open:hidden">خصّص ركنك</span>
                            <span class="hidden group-open:inline">إغلاق</span>
                        </summary>
                        <form method="POST" action="{{ route('profile.bio') }}" class="space-y-5 border-t border-line p-5">
                            @csrf
                            @method('PATCH')
                            <x-input name="bio" label="نبذة عنك" hint="اختياري، حتى ١٦٠ حرفاً" maxlength="160"
                                :value="$user->bio" placeholder="قارئ يحب الشعر الجاهلي…" />
                            <x-palette-picker name="accent" label="لون ركنك" :selected="$user->accent ?? 'sand'"
                                hint="يظهر في بطاقتك هذه لكل من يزور ركنك" />
                            <input type="hidden" name="show_poets" value="0">
                            <label class="flex cursor-pointer items-start gap-3">
                                <input type="checkbox" name="show_poets" value="1" @checked($user->show_poets)
                                    class="mt-1 size-4 accent-[var(--color-accent)]" aria-describedby="show_poets-hint">
                                <span>
                                    <span class="text-sm font-medium">أظهر «شعرائي» لزوّار ركني</span>
                                    <span id="show_poets-hint" class="block text-xs text-muted">أزل العلامة لتبقى قائمة شعرائك لك وحدك.</span>
                                </span>
                            </label>
                            <p class="rounded-xl bg-surface p-3 text-xs leading-relaxed text-muted">
                                <x-icon name="lock" class="inline size-3.5" />
                                يرى زوّارك ما جعلته عاماً فقط: اقتباساتك العامة، ومختاراتك العامة، ودواوينك المنشورة. المحفوظات و«كتبي» وكل ما هو خاص لك وحدك، وبريدك لا يظهر لأحد.
                            </p>
                            <x-button type="submit" class="w-full">حفظ</x-button>
                        </form>
                    </details>
                @endif
            </header>

            @if ($pinned)
                <section aria-labelledby="pinned-title" class="flex flex-col">
                    <h2 id="pinned-title" class="mb-3 flex items-center gap-2 text-sm text-muted">
                        <x-icon name="pin" class="size-4 text-accent" /> {{ $isOwner ? 'مثبّت في ركني' : 'مثبّت في ركنه' }}
                    </h2>
                    <a href="{{ route('quotes.show', $pinned) }}" class="block overflow-hidden rounded-3xl shadow-xl transition hover:-translate-y-0.5">
                        <x-quote-art :quote="$pinned" />
                    </a>
                    @if ($isOwner)
                        <form method="POST" action="{{ route('profile.pin', $pinned) }}" class="mt-3">
                            @csrf
                            <button type="submit" class="text-sm text-muted transition hover:text-accent">إلغاء التثبيت</button>
                        </form>
                    @endif
                </section>
            @elseif ($isOwner)
                <section class="grid place-items-center rounded-3xl border-2 border-dashed border-line p-8 text-center">
                    <div>
                        <span class="mx-auto grid size-14 place-items-center rounded-full bg-accent-soft text-accent">
                            <x-icon name="pin" class="size-6" />
                        </span>
                        <h2 class="mt-4 font-display text-2xl font-bold">واجهة ركنك</h2>
                        <p class="mx-auto mt-2 max-w-sm text-muted">
                            ثبّت أحب اقتباساتك إليك ليراه كل من يزورك: اضغط
                            <x-icon name="pin" class="inline size-4 text-accent" /> أسفل أي اقتباس من اقتباساتك.
                        </p>
                    </div>
                </section>
            @endif
        </div>

        {{-- Tabs: scroll sideways on small screens. --}}
        <nav aria-label="أقسام الركن" class="-mx-4 mt-10 overflow-x-auto px-4">
            <div class="flex min-w-max border-b border-line">
                @foreach ($tabItems as $key => $item)
                    <a href="{{ route('profile.show', $key === 'quotes' ? $user : [$user, 'tab' => $key]) }}"
                        @if ($tab === $key) aria-current="page" @endif
                        @class([
                            'inline-flex h-12 items-center gap-1.5 border-b-2 px-4 text-sm transition',
                            'border-accent font-bold text-accent' => $tab === $key,
                            'border-transparent text-muted hover:text-ink' => $tab !== $key,
                        ])>
                        <x-icon :name="$item['icon']" class="size-4" />
                        {{ $item['label'] }}
                        @if ($item['private'] ?? false)
                            <x-icon name="lock" class="size-3 opacity-60" />
                            <span class="sr-only">(تراها أنت فقط)</span>
                        @endif
                    </a>
                @endforeach
            </div>
        </nav>

        <div class="mt-8">
            @switch($tab)
                @case('anthologies')
                    @if ($isOwner)
                        <details class="group mb-8 rounded-3xl border border-line bg-surface" @if ($errors->hasAny(['title', 'description', 'palette'])) open @endif>
                            <summary class="flex cursor-pointer list-none items-center gap-2 px-6 py-4 font-medium text-accent [&::-webkit-details-marker]:hidden">
                                <x-icon name="plus" class="size-5" /> مختارات جديدة
                                <span class="ms-2 text-sm font-normal text-muted">اجمع اقتباسات على فكرة واحدة: «أبيات الصبر»، «من رمضان»…</span>
                            </summary>
                            @include('anthologies.partials.form', ['anthology' => new \App\Models\Anthology, 'action' => route('anthologies.store')])
                        </details>
                    @endif

                    @if ($anthologies->isEmpty())
                        <x-empty-state title="لا مختارات بعد" icon="layers">
                            {{ $isOwner ? 'أنشئ مختاراتك الأولى، ثم أضف إليها من صفحة أي اقتباس.' : 'لم ينشر مختارات بعد.' }}
                        </x-empty-state>
                    @else
                        <ul class="grid gap-x-6 gap-y-10 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach ($anthologies as $anthology)
                                <li><x-anthology-tile :anthology="$anthology" /></li>
                            @endforeach
                        </ul>
                    @endif
                    @break

                @case('poets')
                    @if ($poets->isEmpty())
                        <x-empty-state title="{{ $isOwner ? 'لم تختر شعراءك بعد' : 'لا شعراء بعد' }}" icon="user">
                            @if ($isOwner)
                                زر <a href="{{ route('library.index') }}#poets" class="text-accent hover:underline">رواق الشعراء</a> واضغط «أضف إلى شعرائي» في صفحة من تحب.
                            @else
                                لم يختر شعراءه بعد.
                            @endif
                        </x-empty-state>
                    @else
                        <ul class="flex flex-wrap justify-center gap-5 sm:justify-start">
                            @foreach ($poets as $poet)
                                <li>
                                    <a href="{{ route('library.poet', $poet) }}" aria-pressed="true" style="--poet: {{ $poet->color }}"
                                        class="arch relative flex flex-col items-center border border-transparent px-3 pb-6 pt-10 text-center">
                                        <x-ornament name="star" class="size-8 text-[#E2C27E]" />
                                        <span class="mt-auto font-display text-lg font-bold leading-snug">{{ $poet->name }}</span>
                                        <span class="mt-1 text-xs opacity-75">{{ $poet->verses_count }} بيتاً</span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                    @break

                @case('diwans')
                    @if ($isOwner)
                        <div class="mb-8 flex flex-col items-start justify-between gap-4 rounded-3xl border border-line bg-surface p-6 sm:flex-row sm:items-center">
                            <div>
                                <h2 class="font-display text-xl font-bold">دفترك للشعر الذي تحبه</h2>
                                <p class="mt-1 text-sm text-muted">انقل أبيات شاعر ليس في الخزانة مع مصدرها. يبقى خاصاً بك، ويُنشر للجميع فقط إن تُوفّي الشاعر قبل أكثر من ٥٠ سنة.</p>
                            </div>
                            <x-button :href="route('diwans.create')" icon="plus" class="shrink-0">ديوان جديد</x-button>
                        </div>
                    @endif

                    @if ($diwans->isEmpty())
                        <x-empty-state title="لا دواوين بعد" icon="scroll">
                            {{ $isOwner ? 'ابدأ ديوانك الأول من الزر أعلاه.' : 'لم ينشر دواوين بعد.' }}
                        </x-empty-state>
                    @else
                        <ul class="grid grid-cols-2 gap-x-5 gap-y-8 sm:grid-cols-4 lg:grid-cols-5">
                            @foreach ($diwans as $diwan)
                                <li><x-diwan-tile :diwan="$diwan" /></li>
                            @endforeach
                        </ul>
                    @endif
                    @break

                @case('books')
                    {{-- كتبي: visible to its owner only. --}}
                    @foreach (config('quotes.reading_statuses') as $status => $label)
                        @if ($shelf->has($status))
                            <section class="mb-10" aria-label="{{ $label }}">
                                <h2 class="font-display text-xl font-bold">{{ $label }} <span class="text-base font-normal text-muted">({{ $shelf[$status]->count() }})</span></h2>
                                <ul class="mt-4 grid grid-cols-2 gap-x-5 gap-y-8 sm:grid-cols-4 lg:grid-cols-6">
                                    @foreach ($shelf[$status] as $book)
                                        <li><x-book-tile :book="$book" /></li>
                                    @endforeach
                                </ul>
                            </section>
                        @endif
                    @endforeach

                    @if ($shelf->isEmpty())
                        <x-empty-state title="رفّك فارغ" icon="book-open">
                            افتح صفحة أي كتاب في <a href="{{ route('books.index') }}" class="text-accent hover:underline">رفوف القرّاء</a> واختر: أقرؤه الآن، أو قرأته، أو أريد قراءته.
                        </x-empty-state>
                    @endif
                    @break

                @default
                    @if ($quotes->isEmpty())
                        @php
                            $empty = match ($tab) {
                                'saved' => ['لا محفوظات بعد', 'bookmark', 'احفظ ما يعجبك من الاقتباسات لتجده هنا.'],
                                'original' => ['لا كتابات بعد', 'feather', $isOwner ? 'اكتب نصاً من تأليفك في مرسم الحروف، وفعّل «النص من تأليفي».' : 'لم ينشر شيئاً من تأليفه بعد.'],
                                default => ['لا اقتباسات بعد', 'feather', $isOwner ? 'شارك أول اقتباس لك من زر «شارك اقتباساً».' : 'لم يشارك اقتباسات بعد.'],
                            };
                        @endphp
                        <x-empty-state :title="$empty[0]" :icon="$empty[1]">
                            {{ $empty[2] }}
                            @if ($isOwner && $tab === 'original')
                                <a href="{{ route('quotes.create') }}" class="mt-3 block text-accent hover:underline">افتح مرسم الحروف</a>
                            @endif
                        </x-empty-state>
                    @else
                        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach ($quotes as $quote)
                                <x-quote-card :quote="$quote" />
                            @endforeach
                        </div>

                        <x-pagination :paginator="$quotes" class="mt-10" />
                    @endif
            @endswitch
        </div>
    </div>
@endsection
