@props(['variant' => 'primary', 'href' => null, 'icon' => null])

@php
    $classes = [
        'inline-flex h-11 items-center justify-center gap-2 rounded-full px-5 font-medium transition duration-200',
        'focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent disabled:opacity-50',
        match ($variant) {
            'secondary' => 'border border-ink text-ink hover:bg-ink hover:text-paper',
            'ghost' => 'text-ink hover:bg-accent-soft hover:text-accent',
            default => 'bg-accent text-paper shadow-sm hover:opacity-90',
        },
    ];
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->class($classes) }}>
        @if ($icon) <x-icon :name="$icon" /> @endif
        {{ $slot }}
    </a>
@else
    <button {{ $attributes->merge(['type' => 'button'])->class($classes) }}>
        @if ($icon) <x-icon :name="$icon" /> @endif
        {{ $slot }}
    </button>
@endif
