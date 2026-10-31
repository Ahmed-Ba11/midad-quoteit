@props(['active' => false, 'href' => null])

@php
    $classes = [
        // A link chip is a tap target: 44px tall on a phone; a plain label stays small.
        'inline-flex items-center gap-1.5 rounded-full border px-4 text-sm transition',
        'h-11 sm:h-9' => $href,
        'h-9' => ! $href,
        'border-ink bg-ink text-paper' => $active,
        'border-line bg-surface text-ink hover:border-accent hover:text-accent' => ! $active,
    ];
@endphp

@if ($href)
    <a href="{{ $href }}" @if ($active) aria-current="true" @endif {{ $attributes->class($classes) }}>{{ $slot }}</a>
@else
    <span {{ $attributes->class($classes) }}>{{ $slot }}</span>
@endif
