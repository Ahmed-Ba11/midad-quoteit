@props(['sadr', 'ajuz', 'poet' => null])

<figure {{ $attributes->class('relative text-center') }}>
    <blockquote class="font-text leading-[2]">
        <p>{{ $sadr }}</p>
        <span aria-hidden="true" class="mx-auto my-3 block size-2 rotate-45 bg-[#C9A45C]"></span>
        <p>{{ $ajuz }}</p>
    </blockquote>

    @if ($poet)
        <figcaption class="mt-6 text-sm opacity-70">— {{ $poet }}</figcaption>
    @endif
</figure>
