{{--
    "شارك اقتباساً": one window, two clear ways out.
      • انشره نصاً   — publish now as words in a text card (choose its font and quote marks if you like);
      • صمّمه بطاقة  — carry the words into مرسم الحروف to design a card (QuoteController@draft).
    Everything optional stays folded away, so a first quote takes seconds (NFR: under one minute).
    On phones it rises from the bottom like a sheet; on wide screens it sits in the middle.
--}}
@php
    $useOld = old('_form') === 'share';
    $myAnthologies = auth()->user()->anthologies()->latest('updated_at')->get(['id', 'title']);
    $fonts = collect(config('quotes.text_fonts'))->mapWithKeys(fn (string $key) => [$key => config("quotes.fonts.$key")]);
    $chip = 'flex cursor-pointer items-center justify-center rounded-xl border border-line bg-paper px-3 py-2 text-center transition hover:border-accent has-[:checked]:border-accent has-[:checked]:bg-accent-soft has-[:checked]:text-accent has-[:focus-visible]:ring-3 has-[:focus-visible]:ring-accent/30';
@endphp

<div id="quote-popup" popover
    class="share-sheet m-0 mt-auto max-h-[92dvh] w-full overflow-y-auto overscroll-contain rounded-t-3xl border border-line bg-surface p-0 text-ink shadow-2xl backdrop:bg-black/50 backdrop:backdrop-blur-sm md:m-auto md:w-[min(94vw,36rem)] md:rounded-3xl">
    <form method="POST" action="{{ route('store_quote') }}" class="space-y-5 px-5 pb-[max(1.25rem,env(safe-area-inset-bottom))] pt-3 sm:px-8 sm:pb-8">
        @csrf
        <input type="hidden" name="_form" value="share">
        <input type="hidden" name="presentation" value="text">

        {{-- Grab handle (phones) and title: they stay at the top while the form scrolls. --}}
        <div class="sticky top-0 z-10 -mx-5 -mt-3 bg-surface/95 px-5 pb-2 pt-3 backdrop-blur sm:-mx-8 sm:px-8">
        <span aria-hidden="true" class="mx-auto mb-2 block h-1.5 w-12 rounded-full bg-line md:hidden"></span>
        <div class="flex items-center justify-between">
            <h2 class="flex items-center gap-2 font-display text-2xl font-bold">
                <x-icon name="quill" class="size-6 text-accent" /> شارك اقتباساً
            </h2>
            <button type="button" popovertarget="quote-popup" popovertargetaction="hide" aria-label="إغلاق"
                class="grid size-11 place-items-center rounded-full text-muted transition hover:bg-accent-soft hover:text-accent">
                <x-icon name="x" />
            </button>
        </div>
        </div>

        <div>
            <x-textarea name="content" id="share-content" label="النص" hint="من ٣ إلى ١٥٥ حرفاً" rows="4" minlength="3"
                maxlength="155" required placeholder="اكتب جملةً بقيت معك…" data-count-target="share-count"
                :value="$useOld ? old('content') : ''" :show-error="$useOld" />
            <p class="mt-1 text-end text-xs text-muted" aria-hidden="true"><span id="share-count">0</span> / ١٥٥</p>
        </div>

        @include('quotes.partials.text-origin', [
            'quote' => new \App\Models\Quote, 'prefix' => 'share-', 'useOld' => $useOld,
            'withPresentation' => false, 'anthologies' => $myAnthologies,
        ])

        {{-- Optional touches for the text card: its font and its quotation marks. --}}
        <details class="group rounded-2xl border border-line" @if ($useOld && ($errors->has('font') || $errors->has('mark_style'))) open @endif>
            <summary class="flex cursor-pointer list-none items-center justify-between gap-2 px-4 py-3 text-sm font-medium [&::-webkit-details-marker]:hidden">
                <span class="flex items-center gap-2"><x-icon name="type" class="size-4 text-accent" /> خط النص وعلامة الاقتباس</span>
                <x-icon name="chevron-left" class="size-4 text-muted transition group-open:-rotate-90" />
            </summary>
            <div class="space-y-4 border-t border-line p-4">
                <fieldset>
                    <legend class="mb-2 text-xs text-muted">الخط</legend>
                    <div class="grid grid-cols-3 gap-2 sm:grid-cols-5">
                        @foreach ($fonts as $key => $font)
                            <label class="{{ $chip }}">
                                <input type="radio" name="font" value="{{ $key }}" class="sr-only" @checked(($useOld ? old('font', 'amiri') : 'amiri') === $key)>
                                <span class="text-lg leading-tight" style="font-family: {{ $font['stack'] }}">أبجد</span>
                                <span class="sr-only">{{ $font['label'] }}</span>
                            </label>
                        @endforeach
                    </div>
                </fieldset>
                <fieldset>
                    <legend class="mb-2 text-xs text-muted">علامة الاقتباس</legend>
                    <div class="grid grid-cols-2 gap-2 sm:grid-cols-4">
                        @foreach (config('quotes.marks') as $key => $mark)
                            <label class="{{ $chip }} flex-col gap-1 py-2.5">
                                <input type="radio" name="mark_style" value="{{ $key }}" class="sr-only" @checked(($useOld ? old('mark_style', 'guillemets') : 'guillemets') === $key)>
                                <x-quote-mark :style="$key" class="h-6 text-gold" />
                                <span class="text-xs text-muted">{{ $mark['label'] }}</span>
                            </label>
                        @endforeach
                    </div>
                </fieldset>
            </div>
        </details>

        <p class="text-center text-xs text-muted">«صمّمه بطاقة» يأخذ نصّك إلى مرسم الحروف لتختار الخط واللون.</p>

        {{-- Two ways out, always in reach: publish the words now, or take them to the studio to design a card. --}}
        <div class="sticky bottom-0 z-10 -mx-5 grid grid-cols-2 gap-3 border-t border-line bg-surface/95 px-5 py-3 backdrop-blur sm:-mx-8 sm:px-8">
            <x-button type="submit" icon="quill" class="h-12 w-full">انشره نصاً</x-button>
            <x-button type="submit" variant="secondary" icon="palette" class="h-12 w-full"
                formaction="{{ route('quotes.draft') }}" formnovalidate>صمّمه بطاقة</x-button>
        </div>
    </form>
</div>

@if ($useOld && $errors->any())
    <script>
        document.getElementById('quote-popup').showPopover();
    </script>
@endif
