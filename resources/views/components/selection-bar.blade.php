{{-- Sticky bar for the library selection (up to 2 verses, or 1 saying). Filled by resources/js/library.js. --}}
<div data-selection-bar hidden
    class="fixed inset-x-3 bottom-4 z-30 mx-auto max-w-2xl rounded-3xl bg-[#1C1A18] p-3 text-[#F2ECE2] shadow-2xl ring-1 ring-[#C9A45C]/40 sm:p-4">
    <div class="flex flex-wrap items-center gap-3">
        <div class="min-w-0 flex-1">
            <p class="text-xs text-[#C9A45C]" data-selection-count></p>
            <p class="truncate font-text" data-selection-preview></p>
            <p class="hidden text-xs text-[#E3977D]" data-selection-warning>البيتان معاً أطول من ١٥٥ حرفاً؛ أبقِ بيتاً واحداً.</p>
        </div>
        <button type="button" data-selection-clear
            class="h-11 rounded-full px-4 text-sm text-[#F2ECE2]/70 transition hover:text-[#F2ECE2]">مسح</button>
        <a href="{{ route('quotes.create') }}" data-selection-go
            class="inline-flex h-11 items-center gap-2 rounded-full bg-[#C9A45C] px-5 font-medium text-[#1C1A18] transition hover:bg-[#E2C27E] aria-disabled:pointer-events-none aria-disabled:opacity-40">
            <x-icon name="feather" class="size-[18px]" />
            صمّمه في مرسم الحروف
        </a>
    </div>
</div>
