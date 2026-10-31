@props(['quote', 'uniform' => false, 'byline' => '', 'font', 'fontSize', 'aspect', 'base'])

{{--
    A designed card (مرسم الحروف): a background colour with its texture and dim, the words (in an
    optional box), a small divider and the byline. The design's choices are data-* attributes and CSS
    variables, so resources/css/card.css draws them — and resources/js/studio.js only flips the same
    attributes for the live preview, so the preview is exactly what is published.
--}}
@php
    $design = $quote->cardDesign();
    $palette = config('card.palettes.'.$design['palette']);
    $highlightStyle = $quote->highlight_style === 'color' ? 'hl-color' : 'hl-mark';
@endphp

<figure data-quote-art {{ $attributes->class('quote-art card-art relative m-0 w-full overflow-hidden') }}
    data-texture="{{ $design['texture'] }}" data-divider="{{ $design['divider'] }}" data-align="{{ $design['align'] }}"
    data-box="{{ $design['box'] ? 'on' : 'off' }}" data-padding="{{ $design['box_padding'] }}"
    data-shadow="{{ $design['box_shadow'] }}" data-outline="{{ $design['box_outline'] ? 'on' : 'off' }}"
    style="aspect-ratio: {{ $aspect }}; --base: {{ $base }}; --fs: {{ $fontSize }}; --bg: {{ $quote->bg_color }}; --ink: {{ $quote->text_color }}; --hl: {{ $quote->highlight_color }}; --accent: {{ $palette['accent'] }}; --box: {{ $design['box_color'] }}; --dim: {{ $design['dim'] / 100 }};">
    <span class="card-texture" aria-hidden="true"></span>
    <span class="card-dim" aria-hidden="true"></span>

    <div class="card-body">
        <div class="card-box">
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
        </div>
    </div>
</figure>
