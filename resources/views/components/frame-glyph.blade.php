@props(['frame'])

{{-- A tiny picture of each card frame (config/card.php → frames) for the studio's frame picker. --}}
<svg viewBox="0 0 40 40" aria-hidden="true" {{ $attributes }} fill="none" stroke="currentColor" stroke-width="1.5">
    <rect x="3" y="3" width="34" height="34" rx="3" stroke-width="1" opacity=".35" />
    @switch($frame)
        @case('line')
            <rect x="8" y="8" width="24" height="24" />
            @break
        @case('double')
            <rect x="7" y="7" width="26" height="26" stroke-width="2" /><rect x="10.5" y="10.5" width="19" height="19" stroke-width=".9" />
            @break
        @case('corners')
            <path d="M7 15V7h8M33 15V7h-8M7 25v8h8M33 25v8h-8" /><path d="M7 4.6l2.4 2.4L7 9.4 4.6 7Z" fill="currentColor" stroke="none" />
            @break
        @case('arabesque')
            <path d="M7 16C7 11 11 7 16 7M33 16c0-5-4-9-9-9M7 24c0 5 4 9 9 9M33 24c0 5-4 9-9 9" />
            <circle cx="7" cy="7" r="1.6" fill="currentColor" stroke="none" /><circle cx="33" cy="33" r="1.6" fill="currentColor" stroke="none" />
            @break
        @case('mihrab')
            <path d="M10 34V18c0-4 5-7 10-12 5 5 10 8 10 12v16Z" />
            @break
        @case('girih')
            <rect x="7" y="7" width="26" height="26" /><rect x="11" y="11" width="18" height="18" />
            <path d="M9 9l2 2M31 9l-2 2M9 31l2-2M31 31l-2-2M20 7v4M20 29v4M7 20h4M29 20h4" stroke-width="1" />
            @break
        @default
            <path d="M12 28 28 12" opacity=".6" />
    @endswitch
</svg>
