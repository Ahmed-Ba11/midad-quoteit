@props(['sadr', 'ajuz', 'poet' => null, 'layout' => 'stacked'])

{{--
    A verse is always صدر / gold diamond / عجز.
    stacked — صدر above عجز (cards, panels)
    paired  — صدر and عجز side by side as in a printed diwan, stacking on small screens
--}}
<figure {{ $attributes->class('relative m-0') }}>
    @if ($layout === 'paired')
        {{-- Wide: two equal hemistich columns, each centred, like web diwans.
             Narrow: صدر to the right, عجز to the left — never a lone word left hanging. --}}
        <blockquote class="verse-paired mx-auto grid max-w-3xl items-center gap-x-6 gap-y-1 font-text leading-[1.9] md:grid-cols-[1fr_auto_1fr]">
            <p class="hemistich text-start">{{ $sadr }}</p>
            <span aria-hidden="true" class="mx-auto my-0.5 block size-1.5 rotate-45 bg-gold md:my-0"></span>
            <p class="hemistich text-end">{{ $ajuz }}</p>
        </blockquote>
    @else
        <blockquote class="text-center font-text leading-[2]">
            <p class="hemistich">{{ $sadr }}</p>
            <span aria-hidden="true" class="mx-auto my-3 block size-2 rotate-45 bg-[#C9A45C]"></span>
            <p class="hemistich">{{ $ajuz }}</p>
        </blockquote>
    @endif

    @if ($poet)
        <figcaption @class(['mt-6 text-sm opacity-70', 'text-center' => $layout !== 'paired'])>— {{ $poet }}</figcaption>
    @endif
</figure>
