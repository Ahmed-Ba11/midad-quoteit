@props(['name' => 'star'])

{{--
    Heritage ornaments drawn as SVG (no image files):
    star   — the eight-pointed star (two overlapping squares) of Islamic geometry
    shamsa — the "little sun" medallion stamped on Arabic book covers and title pages
    girih  — a repeating lattice of eight-pointed stars, used as a faint background
--}}
@if ($name === 'girih')
    <svg aria-hidden="true" {{ $attributes->class('pointer-events-none') }} xmlns="http://www.w3.org/2000/svg">
        <defs>
            <pattern id="girih-tile" width="80" height="80" patternUnits="userSpaceOnUse">
                <g fill="none" stroke="currentColor" stroke-width="1">
                    <path d="M40 12 68 40 40 68 12 40Z" />
                    <path d="M20 20h40v40H20Z" />
                    <circle cx="40" cy="40" r="7" />
                    <path d="M0 40h12M68 40h12M40 0v12M40 68v12M0 0l20 20M80 0 60 20M0 80l20-20M80 80 60 60" />
                </g>
            </pattern>
        </defs>
        <rect width="100%" height="100%" fill="url(#girih-tile)" />
    </svg>
@elseif ($name === 'shamsa')
    <svg aria-hidden="true" viewBox="0 0 120 120" {{ $attributes->class('shrink-0') }} xmlns="http://www.w3.org/2000/svg">
        <g fill="none" stroke="currentColor">
            <circle cx="60" cy="60" r="56" stroke-width="1" />
            <circle cx="60" cy="60" r="50" stroke-width="2.5" stroke-dasharray="1 5" stroke-linecap="round" />
            <circle cx="60" cy="60" r="42" stroke-width="1" />
        </g>
        <g fill="currentColor">
            <path d="M60 24 85.5 34.5 96 60 85.5 85.5 60 96 34.5 85.5 24 60 34.5 34.5Z" opacity="0.18" />
            <path d="M60 30l7 15 16-4-4 16 15 7-15 7 4 16-16-4-7 15-7-15-16 4 4-16-15-7 15-7-4-16 16 4Z" opacity="0.9" />
        </g>
        <circle cx="60" cy="60" r="9" fill="none" stroke="#f7f0e1" stroke-width="1.5" />
    </svg>
@else
    <svg aria-hidden="true" viewBox="0 0 80 80" {{ $attributes->class('shrink-0') }} xmlns="http://www.w3.org/2000/svg">
        <path fill="currentColor" d="M40 4 50.6 15 66 14 65 29.4 76 40 65 50.6 66 66 50.6 65 40 76 29.4 65 14 66 15 50.6 4 40 15 29.4 14 14 29.4 15Z" />
    </svg>
@endif
