@props(['anthology'])

{{-- مختارات: a cover in its palette, with up to three of its quotes fanned out behind it like cards in a folder. --}}
@php
    $colors = $anthology->colors();
    $previews = $anthology->relationLoaded('quotes') ? $anthology->quotes->take(3) : collect();
@endphp

<a href="{{ route('anthologies.show', $anthology) }}" {{ $attributes->class('group relative block pt-6 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-accent') }}>
    @foreach ($previews->reverse()->values() as $preview)
        <span aria-hidden="true" class="absolute inset-x-6 top-0 h-24 rounded-2xl shadow-sm ring-1 ring-black/5 transition duration-300 group-hover:-translate-y-1"
            style="background: {{ $preview->bg_color }}; transform: rotate({{ [-4, 3, -1][$loop->index] }}deg); top: {{ $loop->index * 6 }}px"></span>
    @endforeach

    <span class="relative flex aspect-[4/3] flex-col justify-between overflow-hidden rounded-3xl p-5 shadow-md ring-1 ring-black/10 transition duration-300 group-hover:-translate-y-1 group-hover:shadow-xl"
        style="background: {{ $colors['bg'] }}; color: {{ $colors['text'] }}">
        <x-ornament name="girih" class="girih pointer-events-none absolute inset-0 h-full w-full" style="color: {{ $colors['highlight'] }}" />

        <span class="relative flex items-center justify-between text-xs opacity-80">
            <span class="inline-flex items-center gap-1"><x-icon name="layers" class="size-4" /> مختارات</span>
            @unless ($anthology->is_public)
                <span class="inline-flex items-center gap-1"><x-icon name="lock" class="size-3.5" /> خاصة</span>
            @endunless
        </span>

        <span class="relative">
            <span class="block font-display text-2xl font-bold leading-snug" style="color: {{ $colors['highlight'] }}">{{ $anthology->title }}</span>
            @if ($anthology->description)
                <span class="mt-1 line-clamp-2 block text-sm opacity-85">{{ $anthology->description }}</span>
            @endif
            <span class="mt-2 block text-xs opacity-70">{{ $anthology->quotes_count ?? $anthology->quotes->count() }} اقتباس</span>
        </span>
    </span>
</a>
