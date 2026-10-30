@props(['wordmark' => true, 'tagline' => false])

{{--
    رقيم: "الرقيم: الكتاب" (مقاييس اللغة) — a tablet with an arched top, as the inscribed tablet of the
    Cave; and "الرَّقْم: تعجيم الكتاب" (لسان العرب) — three gold i'jam dots set on it like the dots of ث.
    The same gold diamonds separate the two halves of every verse on the site.
--}}
<span {{ $attributes->class('inline-flex items-center gap-2') }}>
    <svg viewBox="0 0 32 40" aria-hidden="true" class="h-[1.6em] w-auto shrink-0" fill="none">
        <path d="M5 37.5V16a11 11 0 0 1 22 0v21.5Z" stroke="currentColor" stroke-width="2.2" stroke-linejoin="round" />
        <path d="M9.5 31.5h13" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" opacity=".55" />
        <g class="text-gold" fill="currentColor">
            <rect x="14.1" y="12.6" width="3.8" height="3.8" rx=".5" transform="rotate(45 16 14.5)" />
            <rect x="9.6" y="19.6" width="3.8" height="3.8" rx=".5" transform="rotate(45 11.5 21.5)" />
            <rect x="18.6" y="19.6" width="3.8" height="3.8" rx=".5" transform="rotate(45 20.5 21.5)" />
        </g>
    </svg>
    @if ($wordmark)
        <span class="leading-none">
            <span class="logo block font-display font-black">رقيــم</span>
            @if ($tagline)
                <span class="mt-1 block font-sans text-[0.32em] font-normal tracking-wide opacity-70">لوحٌ لما بقي معك</span>
            @endif
        </span>
    @endif
</span>
