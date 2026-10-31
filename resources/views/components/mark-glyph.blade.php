{{--
    The رقيم mark (the pen-nib ر) drawn in place, with its own mask, for things that must carry it
    whole when they are saved as an image (a card's corner mark, the wax seal). Coloured by `currentColor`.
--}}
@php($cutId = 'nib-cut-'.\Illuminate\Support\Str::random(6))
<svg viewBox="-386 -683 1100 1100" aria-hidden="true" {{ $attributes }}>
    <defs>
        <mask id="{{ $cutId }}" maskUnits="userSpaceOnUse" x="-500" y="-900" width="1400" height="1400">
            <rect x="-500" y="-900" width="1400" height="1400" fill="#fff" />
            <path d="M142 -456H122L54 -294C105 -243 172 -182 214 -114C182 -4 35 68 -150 101V121L65 230C164 115 235 -36 235 -184C235 -200 234 -217 231 -234L263 -307Z" fill="#000" stroke="#000" stroke-width="40" stroke-linejoin="round" />
        </mask>
    </defs>
    <g fill="currentColor">
        <g mask="url(#{{ $cutId }})"><path transform="translate(215 -40)" d="M122 -456H142L263 -307L236 -246C222 -196 196 -150 152 -104C164 -150 168 -188 160 -222L54 -294Z" /></g>
        <path d="M142 -456H122L54 -294C105 -243 172 -182 214 -114C182 -4 35 68 -150 101V121L65 230C164 115 235 -36 235 -184C235 -200 234 -217 231 -234L263 -307Z" />
    </g>
</svg>
