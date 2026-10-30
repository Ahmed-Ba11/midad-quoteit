@props(['user', 'size' => 'size-9'])

{{-- A reader's face on the site: their badge (شارتي) in their corner's colours, or their initial. --}}
@php
    $colors = $user->accentColors();
    $badge = array_key_exists((string) $user->badge, config('quotes.badges')) ? $user->badge : null;
@endphp

<span aria-hidden="true" {{ $attributes->class([$size, 'grid shrink-0 place-items-center rounded-full font-bold ring-1 ring-black/5']) }}
    style="background: {{ $colors['bg'] }}; color: {{ $colors['highlight'] }}">
    @if ($badge)
        <x-badge :name="$badge" class="size-[62%]" />
    @else
        <span class="text-[0.95em] leading-none">{{ mb_substr($user->name, 0, 1) }}</span>
    @endif
</span>
