@props(['quote', 'marks' => null])

{{--
    A quote's words, set by Quote::typeset(): verse as صدر / عجز (stacked on cards, side by side on a
    wide text page), free verse line by line, prose as paragraphs. Highlighted words are .hl spans.
    `marks` wraps the whole text in an opening and closing mark («…»).
    resources/js/quote-words.js builds the same markup for the live previews.
--}}
@php
    $typeset = $quote->typeset();
    $render = fn (array $pieces) => collect($pieces)
        ->map(fn (array $piece) => $piece['highlighted'] ? '<span class="hl">'.e($piece['text']).'</span>' : e($piece['text']))
        ->implode(' ');
    [$open, $close] = $marks ? [e($marks[0]), e($marks[1])] : ['', ''];
    $openMark = $open !== '' ? '<span class="words-mark">'.$open.'</span>' : '';
    $closeMark = $close !== '' ? '<span class="words-mark">'.$close.'</span>' : '';
@endphp

<div {{ $attributes->class('quote-words') }} data-mode="{{ $typeset['mode'] }}">
    @if ($typeset['mode'] === 'verse')
        <div class="poem">
            @foreach ($typeset['verses'] as $verse)
                <div class="bayt">
                    <p class="hemi sadr">{!! $loop->first ? $openMark : '' !!}{!! $render($verse['sadr']) !!}@if ($verse['ajuz'] === []){!! $loop->last ? $closeMark : '' !!}@endif</p>
                    @if ($verse['ajuz'] !== [])
                        <span class="sep" aria-hidden="true"></span><span class="sr-only"> … </span>
                        <p class="hemi ajuz">{!! $render($verse['ajuz']) !!}{!! $loop->last ? $closeMark : '' !!}</p>
                    @endif
                </div>
            @endforeach
        </div>
    @elseif ($typeset['mode'] === 'lines')
        <div class="free-lines">
            @foreach ($typeset['lines'] as $line)
                <p class="line">{!! $loop->first ? $openMark : '' !!}{!! $render($line) !!}{!! $loop->last ? $closeMark : '' !!}</p>
            @endforeach
        </div>
    @else
        @foreach ($typeset['paragraphs'] as $paragraph)
            <p class="para">{!! $loop->first ? $openMark : '' !!}{!! collect($paragraph)->map($render)->implode('<br>') !!}{!! $loop->last ? $closeMark : '' !!}</p>
        @endforeach
    @endif

    @if ($typeset['attribution'] && blank($quote->author))
        <p class="words-attribution">— {{ $typeset['attribution'] }}</p>
    @endif
</div>
