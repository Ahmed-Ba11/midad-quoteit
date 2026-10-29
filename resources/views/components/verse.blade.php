@props(['sadr', 'ajuz', 'poet' => null, 'layout' => 'stacked'])

{{--
    A verse is always صدر / gold diamond / عجز.
    stacked — صدر above عجز (cards, panels)
    paired  — صدر and عجز side by side as in a printed diwan, stacking on small screens
--}}
<figure {{ $attributes->class('relative m-0') }}>
    @if ($layout === 'paired')
        <blockquote class="grid items-center gap-x-6 gap-y-1 font-text leading-[1.9] md:grid-cols-[1fr_auto_1fr]">
            <p class="md:text-start">{{ $sadr }}</p>
            <span aria-hidden="true" class="mx-auto block size-1.5 rotate-45 bg-gold"></span>
            <p class="md:text-end">{{ $ajuz }}</p>
        </blockquote>
    @else
        <blockquote class="text-center font-text leading-[2]">
            <p>{{ $sadr }}</p>
            <span aria-hidden="true" class="mx-auto my-3 block size-2 rotate-45 bg-[#C9A45C]"></span>
            <p>{{ $ajuz }}</p>
        </blockquote>
    @endif

    @if ($poet)
        <figcaption @class(['mt-6 text-sm opacity-70', 'text-center' => $layout !== 'paired'])>— {{ $poet }}</figcaption>
    @endif
</figure>
