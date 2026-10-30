@props(['style' => 'guillemets'])

{{-- A small preview of a quote frame (config/quotes.php → marks), for the style picker. --}}
@switch($style)
    @case('curly')
        <svg viewBox="0 0 40 24" aria-hidden="true" {{ $attributes }} fill="currentColor">
            <path d="M7 20c-3.2 0-5-2.4-5-5.4C2 9.8 5.5 5 10.6 3.4l1 1.8C8.4 6.7 6.8 8.8 6.6 11c.3-.1.7-.1 1-.1 2.6 0 4.4 1.9 4.4 4.5S10 20 7 20Zm13 0c-3.2 0-5-2.4-5-5.4 0-4.8 3.5-9.6 8.6-11.2l1 1.8c-3.2 1.5-4.8 3.6-5 5.8.3-.1.7-.1 1-.1 2.6 0 4.4 1.9 4.4 4.5S23 20 20 20Z" />
        </svg>
        @break
    @case('ornament')
        <svg viewBox="0 0 40 24" aria-hidden="true" {{ $attributes }} fill="none" stroke="currentColor" stroke-width="1.4">
            <path d="M2 12h11M27 12h11" stroke-linecap="round" />
            <path d="M20 5l7 7-7 7-7-7Z" fill="currentColor" stroke="none" />
            <path d="M20 8.5 23.5 12 20 15.5 16.5 12Z" fill="var(--color-paper)" stroke="none" />
        </svg>
        @break
    @case('arch')
        <svg viewBox="0 0 40 24" aria-hidden="true" {{ $attributes }} fill="none" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round">
            <path d="M12 23V10a8 8 0 0 1 16 0v13" />
            <path d="M20 8.6l1.6 1.6-1.6 1.6-1.6-1.6Z" fill="currentColor" stroke="none" />
        </svg>
        @break
    @default
        <span aria-hidden="true" {{ $attributes->class('inline-grid place-items-center font-display text-2xl leading-none') }}>«»</span>
@endswitch
