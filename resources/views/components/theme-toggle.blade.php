{{--
    The theme switch everyone knows: a sun by day, a crescent by night. Switching morphs one into the
    other — the rays fold away, a shadow slides over the disc and carves the crescent — and a small star
    lights beside it like a lamp being lit for night reading. resources/js/app.js keeps aria-pressed and
    the label in step with the theme.
--}}
<button type="button" data-theme-toggle aria-pressed="false" aria-label="الوضع الليلي" title="الوضع الليلي"
    {{ $attributes->class('theme-toggle grid size-11 place-items-center rounded-full text-muted transition hover:bg-accent-soft hover:text-accent') }}>
    <svg viewBox="0 0 24 24" aria-hidden="true" class="size-6 overflow-visible">
        <defs>
            <mask id="theme-moon-cut">
                <rect width="24" height="24" fill="#fff" />
                <circle class="theme-cut" cx="15.8" cy="8.4" r="5.4" fill="#000" />
            </mask>
        </defs>
        <g class="theme-rays" stroke="currentColor" stroke-width="1.7" stroke-linecap="round">
            <path d="M12 2.6v2.3M12 19.1v2.3M2.6 12h2.3M19.1 12h2.3M5.35 5.35l1.6 1.6M17.05 17.05l1.6 1.6M5.35 18.65l1.6-1.6M17.05 6.95l1.6-1.6" />
        </g>
        <circle class="theme-disc" cx="12" cy="12" r="4.5" fill="currentColor" mask="url(#theme-moon-cut)" />
        <circle class="theme-star-glow" cx="18.4" cy="5.2" r="3.6" fill="var(--color-gold)" />
        <path class="theme-star" d="M18.4 2.6c.25 1.5.95 2.25 2.5 2.6-1.55.35-2.25 1.1-2.5 2.6-.25-1.5-.95-2.25-2.5-2.6 1.55-.35 2.25-1.1 2.5-2.6Z" fill="var(--color-gold)" />
    </svg>
</button>
