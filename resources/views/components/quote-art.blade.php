@props(['quote'])

@php
    $font = config('quotes.fonts.'.$quote->font, config('quotes.fonts.amiri'));
    $ratio = config('quotes.ratios.'.$quote->ratio, config('quotes.ratios.wide'));
    $byline = collect([$quote->author, $quote->book])->filter()->implode(' · ');
@endphp

{{-- The same markup is rebuilt by resources/js/studio.js for the live preview in رواق الحرف. --}}
<figure data-quote-art {{ $attributes->class('quote-art relative m-0 w-full overflow-hidden') }}
    style="aspect-ratio: {{ $ratio['aspect'] }}; --base: {{ $ratio['width'] }}; --fs: {{ $quote->font_size }}; --bg: {{ $quote->bg_color }}; --ink: {{ $quote->text_color }}; --hl: {{ $quote->highlight_color }};">
    <x-paper-texture data-art-texture @class(['hidden' => ! $quote->texture]) />

    <div class="relative flex h-full flex-col items-center justify-center px-[10%] py-[9%] text-center">
        <blockquote data-art-text @class(['quote-art-text', 'hl-'.$quote->highlight_style, 'italic' => $font['italic'] ?? false])
            style="font-family: {{ $font['stack'] }};"
            @if (($font['lang'] ?? 'ar') === 'en') dir="auto" @endif>
            @foreach ($quote->contentPieces() as $piece)
                @if (! $loop->first){{ ' ' }}@endif{!! $piece['highlighted'] ? '<span class="hl">'.e($piece['text']).'</span>' : e($piece['text']) !!}
            @endforeach
        </blockquote>

        <figcaption data-art-byline @class(['quote-art-byline', 'hidden' => $byline === ''])>— {{ $byline }}</figcaption>
    </div>
</figure>
