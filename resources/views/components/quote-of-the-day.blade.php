@props(['quote'])

@php
    $font = config('quotes.fonts.'.$quote->font, config('quotes.fonts.amiri'));
    $signature = $quote->is_original && blank($quote->author) ? 'بقلم '.$quote->user->name : $quote->author;
    $byline = collect([$signature, $quote->book])->filter()->implode(' · ');
    $length = mb_strlen($quote->content);
@endphp

{{-- اقتباس اليوم: a manuscript leaf lying on the dark hero — double gold rule, a shamsa, the day's date. --}}
<section aria-labelledby="quote-of-the-day" {{ $attributes->class('quote-leaf manuscript-page relative rounded-[28px] px-7 pb-8 pt-10 text-center sm:px-12') }}>
    <x-ornament name="shamsa" class="absolute -top-7 left-1/2 size-14 -translate-x-1/2 rounded-full bg-[#F7F0E1] p-1.5 text-[#A8873F] shadow-md" />

    <h2 id="quote-of-the-day" class="text-xs font-medium tracking-wide text-[#A8873F]">
        اقتبـــاس اليوم · {{ today()->translatedFormat('l j F') }}
    </h2>

    <blockquote @class([
        'mx-auto mt-5 max-w-xl whitespace-pre-line leading-[1.9] text-[#2B2520] [text-wrap:balance]',
        'text-3xl sm:text-4xl' => $length <= 60,
        'text-2xl sm:text-3xl' => $length > 60 && $length <= 110,
        'text-xl sm:text-2xl' => $length > 110,
        'italic' => $font['italic'] ?? false,
    ]) style="font-family: {{ $font['stack'] }};">{{ $quote->content }}</blockquote>

    <span aria-hidden="true" class="mx-auto mt-6 block h-px w-24 bg-gradient-to-r from-transparent via-[#A8873F] to-transparent"></span>

    @if ($byline)
        <p class="mt-4 font-text text-lg text-[#2B2520]">{{ $byline }}</p>
    @endif

    <div class="mt-5 flex flex-wrap items-center justify-center gap-x-4 gap-y-2 text-sm text-[#7A7068]">
        <a href="{{ route('profile.show', $quote->user) }}" class="hover:text-[#8A3324]">شاركه {{ $quote->user->name }}</a>
        <a href="{{ route('quotes.show', $quote) }}" class="inline-flex items-center gap-1 font-medium text-[#8A3324] hover:underline">
            افتح الاقتباس <x-icon name="chevron-left" class="size-4" />
        </a>
    </div>
</section>
