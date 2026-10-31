{{--
    رفّ الدواوين and رواق الشعراء: the same for every reader, so library/index caches their rendered
    HTML (keyed on the library's data, see LibraryController@index). Nothing here may depend on who is looking.
--}}
        {{-- رفّ الدواوين: the seven muallaqat and the great diwans, in an arched walnut niche. --}}
        <section id="shelf" aria-labelledby="shelf-title" class="paint-later scroll-mt-20 py-16">
            <header class="mx-auto max-w-2xl text-center">
                <h2 id="shelf-title" class="font-display text-4xl font-bold">رفّ الدواويـــن</h2>
                <p class="mt-3 text-muted">اختر كتاباً من الرف ليُفتح بين يديك على عيون أبياته.</p>
            </header>

            <div class="library-niche mt-10 px-3 pb-8 pt-16 sm:px-8 sm:pt-24" data-shelf>
                @foreach (['المعلقات السبع' => $muallaqat, 'دواوين العرب' => $diwans] as $plaque => $books)
                    <div @class(['mt-12' => ! $loop->first])>
                        <div class="shelf-row flex items-end gap-1.5 overflow-x-auto px-6 pt-10 sm:justify-center" data-shelf-row>
                            <span aria-hidden="true" class="mb-0 h-28 w-2.5 shrink-0 rounded-t-sm bg-gradient-to-b from-[#c9a45c] to-[#7d6229]"></span>
                            @foreach ($books as $book)
                                @php
                                    $width = [54, 46, 60, 50, 44, 58, 48, 52][$loop->index % 8];
                                    $height = [250, 232, 266, 242, 226, 258, 238, 246][$loop->index % 8];
                                @endphp
                                <button type="button" data-book="{{ $book->slug }}" aria-pressed="false"
                                    aria-label="{{ $book->title }}، {{ $book->poet->name }}"
                                    @class(['book group flex flex-col items-center justify-between px-1 pb-7 pt-8 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-[#C9A45C]', 'book-leaning' => $loop->last && $books->count() > 8])
                                    style="width: {{ $width }}px; height: {{ $height }}px; --leather: {{ $book->color }}; --tooling: {{ $book->ink }};">
                                    <x-ornament name="star" class="size-4 opacity-90" />
                                    <span class="book-title font-display text-[15px] font-bold">{{ $book->type === 'muallaqa' ? $book->poet->name : $book->title }}</span>
                                    <span aria-hidden="true" class="block size-1.5 rotate-45 bg-current opacity-70"></span>
                                </button>
                            @endforeach
                            <span aria-hidden="true" class="mb-0 h-28 w-2.5 shrink-0 rounded-t-sm bg-gradient-to-b from-[#c9a45c] to-[#7d6229]"></span>
                        </div>
                        <div class="shelf-plank mx-2"></div>
                        <p class="shelf-label mx-auto mt-3 flex w-fit items-center gap-3 font-display text-base font-bold text-[#E2C27E]">
                            <span aria-hidden="true" class="h-px w-10 bg-gradient-to-l from-[#C9A45C] to-transparent sm:w-16"></span>
                            <x-ornament name="star" class="size-3" />{{ $plaque }}<x-ornament name="star" class="size-3" />
                            <span aria-hidden="true" class="h-px w-10 bg-gradient-to-r from-[#C9A45C] to-transparent sm:w-16"></span>
                        </p>
                    </div>
                @endforeach
            </div>

            {{-- The open book (one spread per work; library.js shows the chosen one) --}}
            <div class="open-book mx-auto mt-12 max-w-4xl" data-open-book data-opening>
                @foreach ($muallaqat->concat($diwans) as $book)
                    <article data-book-panel="{{ $book->slug }}" @if (! $loop->first) hidden @endif
                        aria-label="{{ $book->title }}" class="grid gap-3 md:grid-cols-2 md:gap-0">
                        <div class="manuscript-page manuscript-page-start flex flex-col items-center justify-center px-10 py-14 text-center">
                            <x-ornament name="shamsa" class="size-24" style="color: {{ $book->color }}" />
                            <p class="mt-5 text-sm text-[#7A7068]">{{ $book->typeLabel() }}</p>
                            <h3 class="mt-1 font-display text-3xl font-bold">{{ $book->title }}</h3>
                            <a href="{{ route('library.poet', $book->poet) }}" class="mt-2 text-[#8A3324] hover:underline">{{ $book->poet->name }}</a>
                            <p class="mt-1 text-sm text-[#7A7068]">{{ $book->poet->years }}</p>
                            <a href="{{ route('library.work', $book) }}"
                                class="mt-7 inline-flex h-11 items-center gap-2 rounded-full bg-[#2B2520] px-5 text-sm font-medium text-[#F7F0E1] transition hover:bg-[#8A3324]">
                                عيون أبياته في المكتبة ({{ $book->verses_count }})
                            </a>
                            <p class="mt-4 flex flex-wrap items-center justify-center gap-x-3 gap-y-1 text-sm text-[#7A7068]">
                                {{ $book->type === 'muallaqa' ? 'اقرأ المعلقة كاملة في' : 'أكمل قراءة الديوان في' }}
                                <x-external-link :href="\App\Support\Aldiwan::poet($book->poet)" class="inline-flex min-h-11 items-center text-[#8A3324]">الديوان</x-external-link>
                            </p>
                        </div>
                        <div class="manuscript-page manuscript-page-end px-8 py-12 sm:px-12">
                            <h4 class="text-center font-display text-lg font-bold text-[#A8873F]">من عيون أبياته</h4>
                            <ul class="mt-6 space-y-6">
                                @foreach ($book->verses->take(3) as $verse)
                                    <li class="text-center">
                                        <x-verse :sadr="$verse->sadr" :ajuz="$verse->ajuz" class="text-lg [&_blockquote_span]:my-1.5" />
                                        <x-pick-button :id="$verse->id" :text="$verse->sadr.' / '.$verse->ajuz" class="mt-2 h-9 text-[#8A3324]" />
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>

        {{-- رواق الشعراء: arches grouped on an era timeline. --}}
        <section id="poets" aria-labelledby="poets-title" class="paint-later scroll-mt-20 border-t border-line py-16">
            <header class="mx-auto max-w-2xl text-center">
                <h2 id="poets-title" class="font-display text-4xl font-bold">رواق الشعـــراء</h2>
                <p class="mt-3 text-muted">أشهر الأصوات أولاً، من الجاهلية إلى العصر العباسي؛ اختر شاعراً لتقرأ سيرته وعيون أبياته.</p>
            </header>

            <div class="no-scrollbar -mx-4 mt-10 flex gap-4 overflow-x-auto px-4 pb-6 pt-4 sm:justify-center" data-arcade>
                @foreach ($poets as $poet)
                    <button type="button" data-poet="{{ $poet->slug }}" aria-pressed="{{ $loop->first ? 'true' : 'false' }}"
                        style="--poet: {{ $poet->color }}"
                        class="arch relative flex shrink-0 flex-col items-center border border-line bg-surface px-3 pb-6 pt-10 text-center focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-accent">
                        <x-ornament name="star" class="size-9 text-gold" />
                        <span class="mt-auto font-display text-lg font-bold leading-snug">{{ $poet->name }}</span>
                        <span class="mt-1 text-xs opacity-75">{{ $poet->eraLabel() }}</span>
                        @if ($poet->is_peninsula)
                            <span class="mt-3 rounded-full border border-current/30 px-2.5 py-0.5 text-xs opacity-80">{{ $poet->region }}</span>
                        @endif
                    </button>
                @endforeach
            </div>

            @foreach ($poets as $poet)
                <article data-poet-panel="{{ $poet->slug }}" @if (! $loop->first) hidden @endif
                    class="mt-6 grid gap-8 rounded-3xl border border-line bg-surface p-6 sm:p-10 lg:grid-cols-[1fr_1.3fr]">
                    <div>
                        <p class="text-sm text-gold">{{ $poet->eraLabel() }} · {{ $poet->years }}</p>
                        <h3 class="mt-1 font-display text-3xl font-bold">{{ $poet->name }}</h3>
                        <p class="mt-4 font-text text-lg leading-loose text-muted">{{ $poet->bio }}</p>
                        <div class="mt-6 flex flex-wrap gap-2">
                            @if ($poet->works->first())
                                <x-button :href="route('library.work', $poet->works->first())" icon="book-open">افتح ديوانه</x-button>
                            @endif
                            <x-button variant="secondary" :href="route('library.poet', $poet)">كل أبياته ({{ $poet->verses_count }})</x-button>
                        </div>
                        <p class="mt-4 flex flex-wrap gap-x-3 gap-y-1 text-sm text-muted">
                            شعره كاملاً في
                            <x-external-link :href="\App\Support\Aldiwan::poet($poet)" class="inline-flex min-h-11 items-center text-accent">الديوان</x-external-link>
                        </p>
                    </div>
                    <ul class="space-y-4">
                        @foreach ($poet->works->flatMap->verses->take(3) as $verse)
                            <li class="rounded-2xl bg-paper p-4">
                                <x-verse :sadr="$verse->sadr" :ajuz="$verse->ajuz" class="[&_blockquote_span]:my-1.5" />
                                <x-pick-button :id="$verse->id" :text="$verse->sadr.' / '.$verse->ajuz" class="mx-auto mt-2 flex w-fit text-accent" />
                            </li>
                        @endforeach
                    </ul>
                </article>
            @endforeach
        </section>
