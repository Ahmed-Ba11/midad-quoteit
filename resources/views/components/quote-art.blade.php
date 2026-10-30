@props(['quote', 'uniform' => false])

@php
    $font = config('quotes.fonts.'.$quote->font, config('quotes.fonts.amiri'));
    $ratio = config('quotes.ratios.'.$quote->ratio, config('quotes.ratios.wide'));
    // بقلمي: own writing carries the writer's chosen signature, or "بقلم <name>" by default.
    $writer = $quote->is_original && blank($quote->author) && $quote->relationLoaded('user') ? 'بقلم '.$quote->user->name : null;
    $byline = collect([$writer ?? $quote->author, $quote->book, $quote->page ? 'ص '.$quote->page : null])->filter()->implode(' · ');
    // In grids every card shares one shape (4:3), so rows line up; the quote page keeps the chosen ratio.
    // Long texts get a smaller size there, so up to 155 characters always fit the card.
    $length = mb_strlen($quote->content);
    $aspect = $uniform ? '4 / 3' : $ratio['aspect'];
    $base = $uniform ? 760 : $ratio['width'];
    $fontSize = $uniform ? min($quote->font_size, $length <= 60 ? 56 : ($length <= 100 ? 42 : 34)) : $quote->font_size;
    $textSize = $length <= 60 ? '6.4cqi' : ($length <= 110 ? '5.2cqi' : '4.4cqi');
@endphp

@if ($quote->presentation === 'text')
    {{-- نص مقتبس: no design, just the words between quotation marks, set like a page of a book. --}}
    <figure {{ $attributes->class('quote-text relative m-0 flex w-full flex-col items-center justify-center overflow-hidden bg-surface px-[9%] py-[8%] text-center') }}
        style="{{ $uniform ? 'aspect-ratio: 4 / 3;' : 'min-height: 16rem;' }}">
        <x-ornament name="star" class="quote-text-mark mb-3 size-4 text-gold" />
        <blockquote @class(['relative m-0 whitespace-pre-line font-text leading-[1.9] text-ink [text-wrap:balance]', 'text-2xl sm:text-3xl' => ! $uniform])
            @if ($uniform) style="font-size: {{ $textSize }}" @endif><span class="text-gold">«</span>{{ $quote->content }}<span class="text-gold">»</span></blockquote>
        @if ($byline !== '')
            <figcaption class="relative mt-4 text-sm text-muted">— {{ $byline }}</figcaption>
        @endif
    </figure>
@else
    {{-- The same markup is rebuilt by resources/js/studio.js for the live preview in مرسم الحروف. --}}
    <figure data-quote-art {{ $attributes->class('quote-art relative m-0 w-full overflow-hidden') }}
        style="aspect-ratio: {{ $aspect }}; --base: {{ $base }}; --fs: {{ $fontSize }}; --bg: {{ $quote->bg_color }}; --ink: {{ $quote->text_color }}; --hl: {{ $quote->highlight_color }};">
        <x-paper-texture data-art-texture @class(['hidden' => ! $quote->texture]) />

        <div class="relative flex h-full flex-col items-center justify-center px-[10%] py-[9%] text-center">
            <blockquote data-art-text @class(['quote-art-text', 'hl-'.$quote->highlight_style, 'italic' => $font['italic'] ?? false])
                style="font-family: {{ $font['stack'] }};"
                @if (($font['lang'] ?? 'ar') === 'en') dir="auto" @endif>
                @foreach ($quote->contentLines() as $line)
                    <span class="quote-art-line block">@foreach ($line as $piece)@if (! $loop->first){{ ' ' }}@endif{!! $piece['highlighted'] ? '<span class="hl">'.e($piece['text']).'</span>' : e($piece['text']) !!}@endforeach</span>
                @endforeach
            </blockquote>

            <figcaption data-art-byline @class(['quote-art-byline', 'hidden' => $byline === ''])>— {{ $byline }}</figcaption>
        </div>
    </figure>
@endif
