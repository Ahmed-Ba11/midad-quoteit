{{--
    The theme switch: an oil lamp (سراج) resting on an open book. By day the lamp is out; at night
    it is lit — a small flame with a soft glow — as a reader lights a lamp to read. resources/js/app.js
    keeps aria-pressed and the label in step with the theme.
--}}
<button type="button" data-theme-toggle aria-pressed="false" aria-label="أشعل المصباح: الوضع الليلي" title="أشعل المصباح: الوضع الليلي"
    {{ $attributes->class('theme-lamp grid size-11 place-items-center rounded-full text-muted transition hover:bg-accent-soft hover:text-accent') }}>
    <svg viewBox="0 0 24 24" aria-hidden="true" class="size-6" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
        {{-- the glow and the flame: shown only when the lamp is lit (dark mode) --}}
        <circle class="lamp-glow" cx="17.6" cy="7.6" r="4.6" fill="var(--color-gold)" stroke="none" />
        <path class="lamp-flame" d="M17.6 9.6c-1.3-.9-1.3-2.6 0-4.3 1.3 1.7 1.3 3.4 0 4.3Z" fill="var(--color-gold)" stroke="none" />
        {{-- the lamp: bowl, spout with wick, and handle --}}
        <path d="M5.5 12.5h10a2.6 2.6 0 0 1-2.6 2.6H8.1a2.6 2.6 0 0 1-2.6-2.6Z" />
        <path d="M15.5 12.5l2.1-1.4v-.7" />
        <path d="M5.5 13.3c-1.3 0-2-.7-2-1.5s.7-1.3 1.5-1.2" />
        {{-- the open book underneath --}}
        <path d="M2.5 20c3.2-1.5 6.3-1.5 9.5 0 3.2-1.5 6.3-1.5 9.5 0" />
        <path d="M12 20v-2.4" />
    </svg>
</button>
