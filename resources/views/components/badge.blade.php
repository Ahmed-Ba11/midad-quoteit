@props(['name'])

{{-- One emblem from config/quotes.php → badges, drawn on a 24-unit grid in the current colour. --}}
<svg viewBox="0 0 24 24" aria-hidden="true" {{ $attributes }} fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
    @switch($name)
        @case('star8')
            <path d="M6.5 6.5h11v11h-11Z" />
            <path d="M12 4.2 19.8 12 12 19.8 4.2 12Z" />
            <circle cx="12" cy="12" r="1.6" fill="currentColor" stroke="none" />
            @break
        @case('shamsa')
            <circle cx="12" cy="12" r="4" />
            <circle cx="12" cy="12" r="1.5" fill="currentColor" stroke="none" />
            <path d="M12 2.8v3M12 18.2v3M2.8 12h3M18.2 12h3M5.5 5.5l2.1 2.1M16.4 16.4l2.1 2.1M5.5 18.5l2.1-2.1M16.4 7.6l2.1-2.1" />
            @break
        @case('girih')
            <path d="M9 3.6h6l5.4 5.4v6L15 20.4H9L3.6 15V9Z" />
            <path d="M12 7.6 16.4 12 12 16.4 7.6 12Z" fill="currentColor" stroke="none" opacity=".85" />
            @break
        @case('dots')
            <g fill="currentColor" stroke="none">
                <path d="M12 4.5 14.6 7.1 12 9.7 9.4 7.1Z" />
                <path d="M7.4 11.4 10 14 7.4 16.6 4.8 14Z" />
                <path d="M16.6 11.4 19.2 14 16.6 16.6 14 14Z" />
            </g>
            <path d="M6 20.5h12" opacity=".5" />
            @break
        @case('arch')
            <path d="M6 21V11a6 6 0 0 1 12 0v10Z" />
            <path d="M12 8.6 13.6 10.2 12 11.8 10.4 10.2Z" fill="currentColor" stroke="none" />
            <path d="M9 17.5h6" opacity=".55" />
            @break
        @case('crescent')
            <path d="M15.8 4.2a8.2 8.2 0 1 0 4.4 13 6.4 6.4 0 1 1-4.4-13Z" fill="currentColor" stroke="none" />
            @break
        @case('quill')
            <path d="M20 4c-6 .5-10.5 4-12.6 10.2L6 19" />
            <path d="M20 4c-.4 5.6-3.6 9.6-9.6 11.2" />
            <path d="M4 21h7" />
            @break
        @case('inkwell')
            <path d="M6.5 14h11v4.5a2.5 2.5 0 0 1-2.5 2.5H9a2.5 2.5 0 0 1-2.5-2.5Z" />
            <path d="M9.5 14v-2.2h5V14" />
            <path d="M12.5 11.8 19.5 2.8" />
            <path d="M17.2 3.3l2.7 2.1" />
            @break
        @case('book')
            <path d="M3 6.2c3-1.4 6-1.4 9 0v13c-3-1.4-6-1.4-9 0Z" />
            <path d="M21 6.2c-3-1.4-6-1.4-9 0v13c3-1.4 6-1.4 9 0Z" />
            @break
        @case('lamp')
            <path d="M17.6 9.4c-1.3-.9-1.3-2.6 0-4.3 1.3 1.7 1.3 3.4 0 4.3Z" fill="currentColor" stroke="none" />
            <path d="M5 13.5h10.5a2.7 2.7 0 0 1-2.7 2.7H7.7A2.7 2.7 0 0 1 5 13.5Z" />
            <path d="M15.5 13.5l2.1-1.6V11" />
            <path d="M5 14.3c-1.3 0-2-.7-2-1.5s.7-1.3 1.5-1.2" />
            <path d="M6 20h11" />
            @break
        @case('palm')
            <path d="M12 21v-9" />
            <path d="M12 12c-1.6-3-4.7-4-8.2-3M12 12c1.6-3 4.7-4 8.2-3M12 12c-2.2-1-3.6-3.6-3-6.8M12 12c2.2-1 3.6-3.6 3-6.8" />
            <path d="M8.5 21h7" />
            @break
        @case('dad')
            <text x="12" y="17.5" text-anchor="middle" font-size="15" font-family="Amiri, 'Noto Naskh Arabic', serif" fill="currentColor" stroke="none">ض</text>
            @break
    @endswitch
</svg>
