@props(['href'])

{{-- A link to another site: new tab, no referrer or opener, and it says so to screen readers. --}}
<a href="{{ $href }}" target="_blank" rel="noopener noreferrer external"
    {{ $attributes->class('inline-flex items-center gap-1 underline-offset-4 hover:underline') }}>
    {{ $slot }}
    <x-icon name="external" class="size-3.5" />
    <span class="sr-only">(يفتح في موقع خارجي)</span>
</a>
