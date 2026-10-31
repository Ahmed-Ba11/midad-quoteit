@props(['sadr', 'ajuz', 'poet' => null, 'eyebrow' => 'من خزانة الأدب'])

{{--
    The tablet (لوح) beside the sign-in form: ink-dark under a lit pointed arch, the رقيم mark engraved
    large behind the verse. The صدر sits right and the عجز left as in a printed diwan, and each is
    written in, stroke by stroke, by resources/js/app.js ([data-write]); the full text is in the page
    from the start, so screen readers and reduced-motion users get it at once.
--}}
{{-- The verse is set in Ruqʿa (Aref Ruqaa, OFL): a hand-written line of ink, as if just written on the
     tablet. The whole family is loaded, never a `&text=` subset (that scrambles the letters). --}}
@once
    @push('head')
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Aref+Ruqaa:wght@400;700&display=block">
    @endpush
@endonce
<aside {{ $attributes->class('tablet') }} aria-label="بيت شعر">
    <svg class="tab-mark" aria-hidden="true"><use href="#mark" /></svg>
    <svg class="arch" viewBox="0 0 400 560" preserveAspectRatio="none" aria-hidden="true">
        <path class="o" d="M20 552 V210 C20 120 110 62 200 22 C290 62 380 120 380 210 V552 Z" vector-effect="non-scaling-stroke" />
        <path class="i" d="M32 540 V214 C32 130 116 76 200 38 C284 76 368 130 368 214 V540 Z" vector-effect="non-scaling-stroke" />
    </svg>
    <figure class="verse-box">
        <span class="eyebrow">{{ $eyebrow }}</span>
        <blockquote class="bayt" data-write>
            <p class="sh sadr"><span class="ink">{{ $sadr }}</span></p>
            <i class="sep" aria-hidden="true"></i>
            <p class="sh ajz"><span class="ink">{{ $ajuz }}</span></p>
        </blockquote>
        @if ($poet)
            <figcaption class="poet">{{ $poet }}</figcaption>
        @endif
    </figure>
</aside>
