{{--
    A short confirmation after an action ("رُقِم اقتباسك"), from session('toast'). It floats above the
    phone's tab bar, is announced to screen readers, and fades away by itself (resources/js/app.js).
--}}
@if (session('toast'))
    <div data-toast role="status" aria-live="polite"
        class="toast fixed inset-x-4 bottom-24 z-50 mx-auto flex max-w-sm items-center gap-3 rounded-2xl border border-gold/40 bg-ink px-4 py-3 text-paper shadow-2xl md:bottom-8">
        <x-ornament name="star" class="size-5 shrink-0 text-gold" />
        <p class="flex-1 text-sm leading-relaxed">{{ session('toast') }}</p>
        <button type="button" data-toast-close aria-label="إغلاق" class="grid size-9 shrink-0 place-items-center rounded-full opacity-70 transition hover:opacity-100">
            <x-icon name="x" class="size-4" />
        </button>
    </div>
@endif
