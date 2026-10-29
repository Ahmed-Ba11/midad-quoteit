@props(['book', 'size' => 'md'])

{{-- A generated cover (no uploaded images): the book's own colour, a double gold frame, title and author. --}}
@php
    $colors = $book->coverColors();
    $titleSize = ['sm' => 'text-sm', 'md' => 'text-lg', 'lg' => 'text-2xl sm:text-3xl'][$size] ?? 'text-lg';
@endphp

<div {{ $attributes->class('relative isolate flex aspect-[2/3] flex-col items-center justify-between overflow-hidden rounded-e-lg rounded-s-sm p-[9%] text-center shadow-lg') }}
    style="background-color: {{ $colors['bg'] }}; color: {{ $colors['text'] }};">
    <x-paper-texture />
    {{-- Spine shading on the binding side and a double gold frame. --}}
    <span aria-hidden="true" class="pointer-events-none absolute inset-y-0 start-0 w-[7%] bg-gradient-to-l from-transparent to-black/25"></span>
    <span aria-hidden="true" class="pointer-events-none absolute inset-[5%] rounded-sm border border-current/40"></span>
    <span aria-hidden="true" class="pointer-events-none absolute inset-[7%] rounded-sm border border-current/20"></span>

    <x-ornament name="star" class="relative mt-[6%] size-[12%] min-w-3" style="color: {{ $colors['highlight'] }}" />
    <p @class(['relative font-display font-bold leading-snug [overflow-wrap:anywhere]', $titleSize])>{{ $book->title }}</p>
    <p class="relative mb-[4%] text-[0.7em] opacity-80">{{ $book->author ?? '' }}</p>
</div>
