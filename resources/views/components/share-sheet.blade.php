{{--
    "شارك اقتباساً": step one — the words and where they come from, typed once. Two clear ways on,
    both through QuoteController@draft, which checks the details here so mistakes show in this window:
      • انشره نصاً   — «محرّر النص»: font, verse/prose format, emphasis and quote marks, with a live preview;
      • صمّمه بطاقة  — مرسم الحروف: a designed card.
    Every choice there has a ready default, so a first quote still takes well under a minute (NFR).
    On phones it rises from the bottom like a sheet; on wide screens it sits in the middle.
--}}
@php
    $useOld = old('_form') === 'share';
    $myAnthologies = auth()->user()->anthologies()->latest('updated_at')->get(['id', 'title']);
@endphp

<div id="quote-popup" popover
    class="share-sheet m-0 mt-auto max-h-[92dvh] w-full overflow-y-auto overscroll-contain rounded-t-3xl border border-line bg-surface p-0 text-ink shadow-2xl backdrop:bg-black/50 backdrop:backdrop-blur-sm md:m-auto md:w-[min(94vw,36rem)] md:rounded-3xl">
    <form method="POST" action="{{ route('quotes.draft') }}" class="space-y-5 px-5 pb-[max(1.25rem,env(safe-area-inset-bottom))] pt-3 sm:px-8 sm:pb-8">
        @csrf
        <input type="hidden" name="_form" value="share">

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
            'anthologies' => $myAnthologies,
        ])

        {{-- Two ways on, always in reach. Both carry the details just typed — nothing is asked twice:
             «انشره نصاً» opens «محرّر النص» (font, format, emphasis, marks); «صمّمه بطاقة» opens مرسم الحروف. --}}
        <div class="sticky bottom-0 z-10 -mx-5 border-t border-line bg-surface/95 px-5 pb-3 pt-3 sm:-mx-8 sm:px-8">
            <div class="grid grid-cols-2 gap-3">
                <x-button type="submit" name="presentation" value="text" icon="type" class="h-12 w-full">انشره نصاً</x-button>
                <x-button type="submit" name="presentation" value="card" variant="secondary" icon="palette" class="h-12 w-full">صمّمه بطاقة</x-button>
            </div>
            <p class="mt-2 text-center text-xs text-muted">الخطوة التالية: تختار الخط والتنسيق وترى المعاينة قبل النشر.</p>
        </div>
    </form>
</div>

@if ($useOld && $errors->any())
    <script>
        document.getElementById('quote-popup').showPopover();
    </script>
@endif
