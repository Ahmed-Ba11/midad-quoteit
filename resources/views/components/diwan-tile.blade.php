@props(['diwan', 'showOwner' => false])

{{-- A reader's diwan as a small bound volume: the palette is the leather, a shamsa stamped in the middle. --}}
@php $colors = $diwan->colors(); @endphp

<a href="{{ route('diwans.show', $diwan) }}" {{ $attributes->class('group block focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-accent') }}>
    <span class="relative flex aspect-[3/4] flex-col items-center justify-between overflow-hidden rounded-e-2xl rounded-s-md px-4 py-6 text-center shadow-md ring-1 ring-black/15 transition duration-300 group-hover:-translate-y-1 group-hover:shadow-xl"
        style="background: linear-gradient(90deg, rgb(0 0 0 / 0.28), rgb(255 255 255 / 0.06) 9%, transparent 14%), {{ $colors['bg'] }}; color: {{ $colors['text'] }}">
        <span aria-hidden="true" class="pointer-events-none absolute inset-2.5 rounded-e-xl rounded-s-sm border" style="border-color: {{ $colors['highlight'] }}55"></span>

        <span class="relative text-[11px] opacity-75">{{ $diwan->is_public ? 'ديوان قارئ' : 'دفتر خاص' }}</span>
        <x-ornament name="shamsa" class="relative size-12" style="color: {{ $colors['highlight'] }}" />
        <span class="relative">
            <span class="block font-display text-lg font-bold leading-snug" style="color: {{ $colors['highlight'] }}">{{ $diwan->title }}</span>
            <span class="mt-1 block text-sm opacity-90">{{ $diwan->poet_name }}</span>
        </span>
    </span>

    <span class="mt-2 flex items-center justify-between gap-2 px-1 text-xs text-muted">
        <span>{{ $diwan->verses_count ?? $diwan->verses->count() }} بيت</span>
        @if ($showOwner)
            <span class="truncate">أضافه {{ $diwan->user->name }}</span>
        @elseif (! $diwan->is_public)
            <span class="inline-flex items-center gap-1"><x-icon name="lock" class="size-3.5" /> خاص بك</span>
        @endif
    </span>
</a>
