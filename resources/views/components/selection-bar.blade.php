{{-- Sticky bar for the library selection (up to 2 verses, or 1 saying). Filled by resources/js/library.js. --}}
<div data-selection-bar hidden
    class="fixed inset-x-3 bottom-[calc(5.5rem+env(safe-area-inset-bottom))] z-50 mx-auto max-w-2xl rounded-3xl md:bottom-4 bg-[#1C1A18] p-3 text-[#F2ECE2] shadow-2xl ring-1 ring-[#C9A45C]/40 sm:p-4">
    <div class="flex flex-wrap items-center gap-3">
        <div class="min-w-0 flex-1 basis-full sm:basis-auto">
            <p class="text-xs text-[#C9A45C]" data-selection-count></p>
            <p class="truncate font-text" data-selection-preview></p>
            <p class="hidden text-xs text-[#E3977D]" data-selection-warning>البيتان معاً أطول من ١٥٥ حرفاً؛ أبقِ بيتاً واحداً.</p>
        </div>
        <button type="button" data-selection-clear
            class="h-11 rounded-full px-4 text-sm text-[#F2ECE2]/70 transition hover:text-[#F2ECE2]">مسح</button>
        {{-- Two clear ways on, as in «شارك اقتباساً»: the words as text, or a designed card. --}}
        <div class="grid w-full grid-cols-2 gap-2 sm:w-auto">
        <a href="{{ route('quotes.create', ['as' => 'text']) }}" data-selection-go data-as="text"
            class="inline-flex h-11 items-center justify-center gap-2 rounded-full bg-[#C9A45C] px-4 font-medium text-[#1C1A18] transition hover:bg-[#E2C27E] aria-disabled:pointer-events-none aria-disabled:opacity-40">
            <x-icon name="type" class="size-[18px]" />
            انشره نصاً
        </a>
        <a href="{{ route('quotes.create', ['as' => 'card']) }}" data-selection-go data-as="card"
            class="inline-flex h-11 items-center justify-center gap-2 rounded-full border border-[#C9A45C]/60 px-4 font-medium text-[#F2ECE2] transition hover:bg-[#F2ECE2]/10 aria-disabled:pointer-events-none aria-disabled:opacity-40">
            <x-icon name="palette" class="size-[18px]" />
            صمّمه بطاقة
        </a>
        </div>
    </div>
</div>
