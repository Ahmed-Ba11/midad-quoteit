{{--
    The brand mark (”ر, the pen-nib ر) defined once per page as a symbol, for the decorative layers:
    window shadows, the engraved tablet, the footer watermark. Draw it with <svg><use href="#mark"/></svg>;
    it takes its colour from `color`.
--}}
<svg width="0" height="0" style="position:absolute" aria-hidden="true" focusable="false">
    <defs>
        <mask id="mark-nib-cut" maskUnits="userSpaceOnUse" x="-500" y="-900" width="1400" height="1400">
            <rect x="-500" y="-900" width="1400" height="1400" fill="#fff" />
            <path d="M142.0 -456.0H122.0L54.0 -294.0C105.0 -243.0 172.0 -182.0 214.0 -114.0C182.0 -4.0 35.0 68.0 -150.0 101.0V121.0L65.0 230.0C164.0 115.0 235.0 -36.0 235.0 -184.0C235.0 -200.0 234.0 -217.0 231.0 -234.0L263.0 -307.0Z" fill="#000" stroke="#000" stroke-width="40" stroke-linejoin="round" />
        </mask>
        <symbol id="mark" viewBox="-386 -683 1100 1100">
            <g fill="currentColor">
                <g mask="url(#mark-nib-cut)">
                    <path transform="translate(215 -40)" d="M122 -456 H142 L263 -307 L236 -246 C222 -196 196 -150 152 -104 C164 -150 168 -188 160 -222 L54 -294 Z" />
                </g>
                <path d="M142.0 -456.0H122.0L54.0 -294.0C105.0 -243.0 172.0 -182.0 214.0 -114.0C182.0 -4.0 35.0 68.0 -150.0 101.0V121.0L65.0 230.0C164.0 115.0 235.0 -36.0 235.0 -184.0C235.0 -200.0 234.0 -217.0 231.0 -234.0L263.0 -307.0Z" />
            </g>
        </symbol>
    </defs>
</svg>
