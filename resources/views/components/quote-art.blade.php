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
    {{--
        نص مقتبس: the words in the reader's chosen font, framed by one of four marks (config/quotes.php →
        marks): «guillemets», a large ❝ mark, gold ornaments above and below, or the arch of the رقيم tablet.
    --}}
    @php $markStyle = array_key_exists($quote->mark_style, config('quotes.marks')) ? $quote->mark_style : 'guillemets'; @endphp
    <figure data-mark="{{ $markStyle }}" {{ $attributes->class(['quote-text relative m-0 flex w-full flex-col items-center justify-center overflow-hidden bg-surface text-center', $markStyle === 'arch' ? 'px-[17%] pb-[7%] pt-[11%]' : 'px-[10%] py-[8%]']) }}
        style="{{ $uniform ? 'aspect-ratio: 4 / 3;' : 'min-height: 16rem;' }}">
        @if ($markStyle === 'arch')
            <span aria-hidden="true" class="quote-text-arch"></span>
        @elseif ($markStyle === 'curly')
            <span aria-hidden="true" class="quote-text-curly">❝</span>
        @elseif ($markStyle === 'ornament')
            <x-quote-mark style="ornament" class="relative mb-[4%] h-[1.4em] text-gold" />
        @endif

        <blockquote @class(['relative m-0 whitespace-pre-line leading-[1.9] text-ink [text-wrap:balance]', 'text-2xl sm:text-3xl' => ! $uniform, 'italic' => $font['italic'] ?? false])
            style="font-family: {{ $font['stack'] }};@if ($uniform) font-size: {{ $textSize }};@endif">@if ($markStyle === 'guillemets')<span class="text-gold">«</span>@endif{{ $quote->content }}@if ($markStyle === 'guillemets')<span class="text-gold">»</span>@endif</blockquote>

        @if ($markStyle === 'ornament')
            <x-quote-mark style="ornament" class="relative mt-[4%] h-[1.4em] rotate-180 text-gold" />
        @endif
        @if ($byline !== '')
            <figcaption class="relative mt-3 text-sm text-muted">— {{ $byline }}</figcaption>
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
