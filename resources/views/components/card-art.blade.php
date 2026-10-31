@props(['quote', 'uniform' => false, 'byline' => '', 'font', 'fontSize', 'aspect', 'base'])

{{--
    A designed card (مرسم الحروف). Every layer is in the markup and config/card.php's choices are
    data-* attributes, so the stylesheet (resources/css/card.css) shows what the design asks for —
    and resources/js/studio.js only flips the same attributes for the live preview, which is also what
    is saved as an image. Layers, bottom to top: colour or gradient, the reader's photo (preview and
    saved image only, never uploaded), texture, dim, frame, the text (in its box), the رقيم mark, the seal.
    The dedication (إلى / من / message) exists only in the studio's preview and the saved image.
--}}
@php
    $design = $quote->cardDesign();
    $gradient = config('card.gradients.'.$design['gradient']);
    $wax = config('card.seal_colors.'.$design['seal_color']);
    $palette = config('card.palettes.'.$design['palette']);
    $sealId = 'seal-'.\Illuminate\Support\Str::random(6);
    $highlightStyle = $quote->highlight_style === 'color' ? 'hl-color' : 'hl-mark';
    $initial = $quote->relationLoaded('user') ? mb_substr($quote->user->name, 0, 1) : mb_substr((string) auth()->user()?->name, 0, 1);
@endphp

<figure data-quote-art {{ $attributes->class('quote-art card-art relative m-0 w-full overflow-hidden') }}
    data-gradient="{{ $design['gradient'] }}" data-texture="{{ $design['texture'] }}" data-frame="{{ $design['frame'] }}"
    data-box="{{ $design['box'] ? 'on' : 'off' }}" data-box-style="{{ $design['box_style'] }}" data-padding="{{ $design['box_padding'] }}"
    data-shadow="{{ $design['box_shadow'] }}" data-outline="{{ $design['box_outline'] ? 'on' : 'off' }}" data-divider="{{ $design['divider'] }}"
    data-align="{{ $design['align'] }}" data-brand="{{ $design['brand'] ? 'on' : 'off' }}" data-marks="{{ $design['big_marks'] ? 'on' : 'off' }}"
    data-seal="{{ $design['seal'] ? 'on' : 'off' }}" data-seal-position="{{ $design['seal_position'] }}" data-seal-emblem="{{ $design['seal_emblem'] }}"
    data-photo="off" data-gift="off"
    style="aspect-ratio: {{ $aspect }}; --base: {{ $base }}; --fs: {{ $fontSize }}; --bg: {{ $quote->bg_color }}; --ink: {{ $quote->text_color }}; --hl: {{ $quote->highlight_color }}; --accent: {{ $palette['accent'] }}; --frame: {{ $design['frame_color'] }}; --box: {{ $design['box_color'] }}; --dim: {{ $design['dim'] / 100 }}; --grad-from: {{ $gradient['from'] ?? 'transparent' }}; --grad-to: {{ $gradient['to'] ?? 'transparent' }}; --wax-light: {{ $wax['light'] }}; --wax: {{ $wax['base'] }}; --wax-dark: {{ $wax['dark'] }};">
    <span class="card-bg" aria-hidden="true"></span>
    <img data-card-photo class="card-photo" alt="" hidden>
    <span class="card-texture" aria-hidden="true"></span>
    <span class="card-dim" aria-hidden="true"></span>

    {{-- Frames: only the one named by data-frame shows. --}}
    <span class="card-frame f-line" aria-hidden="true"></span>
    <span class="card-frame f-double" aria-hidden="true"></span>
    <span class="card-frame f-girih" aria-hidden="true"></span>
    @foreach (['corners', 'arabesque'] as $cornerStyle)
        @foreach (['tr', 'tl', 'br', 'bl'] as $corner)
            <svg class="card-frame f-{{ $cornerStyle }} corner corner-{{ $corner }}" viewBox="0 0 100 100" aria-hidden="true" fill="none" stroke="currentColor">
                @if ($cornerStyle === 'corners')
                    <path d="M8 70V8h62" stroke-width="2.2" /><path d="M18 52V18h34" stroke-width="1.1" />
                    <g transform="translate(8 8)"><rect x="-7" y="-7" width="14" height="14" stroke-width="1.6" /><rect x="-7" y="-7" width="14" height="14" transform="rotate(45)" stroke-width="1.6" /><circle r="2" fill="currentColor" stroke="none" /></g>
                    <path d="M70 8l4-4 4 4-4 4zM8 70l-4 4 4 4 4-4z" fill="currentColor" stroke="none" />
                @else
                    <path d="M8 66C8 34 34 8 66 8" stroke-width="2" /><path d="M20 66C20 41 41 20 66 20" stroke-width="1.1" />
                    <path d="M8 8c14 5 20 14 15 29M8 8c5 14 14 20 29 15" stroke-width="1.4" />
                    <circle cx="8" cy="8" r="3.2" fill="currentColor" stroke="none" />
                @endif
            </svg>
        @endforeach
    @endforeach
    <svg class="card-frame f-mihrab" viewBox="0 0 1000 1250" preserveAspectRatio="none" aria-hidden="true" fill="none" stroke="currentColor">
        <path d="M110 1190V520C110 380 290 290 500 110C710 290 890 380 890 520V1190Z" stroke-width="2.2" vector-effect="non-scaling-stroke" />
        <path d="M135 1165V528C135 400 305 318 500 145C695 318 865 400 865 528V1165Z" stroke-width="1" opacity=".6" vector-effect="non-scaling-stroke" />
    </svg>

    <div class="card-body">
        <div class="card-box">
            <span class="card-bigmark card-bigmark-open" aria-hidden="true">«</span>
            <p data-gift-to class="card-gift card-gift-to" hidden></p>

            <blockquote data-art-text @class(['quote-art-text', $highlightStyle, 'italic' => $font['italic'] ?? false])
                style="font-family: {{ $font['stack'] }};" @if (($font['lang'] ?? 'ar') === 'en') dir="auto" @endif>
                <x-quote-words :quote="$quote" />
            </blockquote>

            <span class="card-divider" aria-hidden="true">
                <svg viewBox="0 0 240 24" class="divider-star"><path d="M0 12H96M144 12H240" stroke="currentColor" /><g transform="translate(120 12)" fill="none" stroke="currentColor" stroke-width="1.4"><rect x="-7" y="-7" width="14" height="14" /><rect x="-7" y="-7" width="14" height="14" transform="rotate(45)" /></g></svg>
                <svg viewBox="0 0 240 24" class="divider-diamond"><path d="M30 12H104M136 12H210" stroke="currentColor" /><path d="M120 4l8 8-8 8-8-8z" fill="currentColor" /></svg>
                <svg viewBox="0 0 240 24" class="divider-dots"><circle cx="100" cy="12" r="3" fill="currentColor" /><circle cx="120" cy="12" r="3" fill="currentColor" /><circle cx="140" cy="12" r="3" fill="currentColor" /></svg>
            </span>

            <figcaption data-art-byline @class(['quote-art-byline', 'hidden' => $byline === ''])>— {{ $byline }}</figcaption>
            <p data-gift-message class="card-gift card-gift-message" hidden></p>
            <p data-gift-from class="card-gift card-gift-from" hidden></p>
            <span class="card-bigmark card-bigmark-close" aria-hidden="true">»</span>
        </div>
    </div>

    <span class="card-brand" aria-hidden="true"><x-mark-glyph />رقيم</span>

    {{-- The wax seal: an uneven blob of wax, a pressed ring and the emblem raised in it. Its filters live
         inside it, so the saved image draws it whole. --}}
    <svg class="card-seal" viewBox="0 0 200 200" aria-hidden="true">
        <defs>
            <radialGradient id="{{ $sealId }}-wax" cx="38%" cy="32%" r="75%">
                <stop offset="0" style="stop-color: var(--wax-light)" /><stop offset=".55" style="stop-color: var(--wax)" /><stop offset="1" style="stop-color: var(--wax-dark)" />
            </radialGradient>
            <filter id="{{ $sealId }}-blob" x="-20%" y="-20%" width="140%" height="140%">
                <feTurbulence type="fractalNoise" baseFrequency="0.035" numOctaves="2" seed="{{ $quote->id ?? 7 }}" />
                <feDisplacementMap in="SourceGraphic" scale="14" />
            </filter>
        </defs>
        <ellipse cx="104" cy="110" rx="84" ry="82" fill="#000" opacity=".22" filter="url(#{{ $sealId }}-blob)" />
        <circle cx="100" cy="100" r="84" fill="url(#{{ $sealId }}-wax)" filter="url(#{{ $sealId }}-blob)" />
        <circle cx="100" cy="100" r="58" fill="none" style="stroke: var(--wax-dark)" stroke-width="5" opacity=".55" />
        <circle cx="100" cy="100" r="54" style="fill: var(--wax)" />
        <g class="seal-emblem seal-nib" style="color: var(--wax-dark)" opacity=".85"><x-mark-glyph x="52" y="52" width="96" height="96" /></g>
        <g class="seal-emblem seal-star" fill="none" style="stroke: var(--wax-dark)" stroke-width="5" opacity=".85" transform="translate(100 100)"><rect x="-24" y="-24" width="48" height="48" /><rect x="-24" y="-24" width="48" height="48" transform="rotate(45)" /></g>
        <text class="seal-emblem seal-initial" data-seal-initial x="100" y="122" text-anchor="middle" font-size="64" font-family="Amiri, serif" font-weight="700" style="fill: var(--wax-dark)" opacity=".85">{{ $initial }}</text>
        <ellipse cx="72" cy="62" rx="22" ry="10" fill="#fff" opacity=".18" transform="rotate(-30 72 62)" />
    </svg>
</figure>
