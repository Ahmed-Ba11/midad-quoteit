@props(['quote' => null, 'verse' => null])

{{--
    The day's leaf on the dark hero: a manuscript page — warm paper with a fine grain, a double gold
    rule, the رقيم mark pressed faintly into its corner, a shamsa on top — holding either a reader's
    quote of the day, or (until readers have shared enough) a verified verse from خزانة الأدب.
--}}
@php
    if ($quote) {
        $font = config('quotes.fonts.'.$quote->font, config('quotes.fonts.amiri'));
        $signature = $quote->is_original && blank($quote->author) ? 'بقلم '.$quote->user->name : $quote->author;
        $byline = collect([$signature, $quote->book])->filter()->implode(' · ');
        $length = mb_strlen($quote->content);
    }
@endphp

<section aria-labelledby="day-leaf-title" {{ $attributes->class('day-leaf manuscript-page relative isolate rounded-[28px] px-7 pb-8 pt-10 text-center sm:px-12') }}>
    <span class="day-leaf-mark" aria-hidden="true"></span>
    <x-ornament name="shamsa" class="absolute -top-7 left-1/2 size-14 -translate-x-1/2 rounded-full bg-[#F7F0E1] p-1.5 text-[#A8873F] shadow-md" />

    <h2 id="day-leaf-title" class="text-xs font-medium tracking-wide text-[#A8873F]">
        {{ $quote ? 'اقتبـــاس اليوم' : 'بيتُ اليوم' }} · {{ today()->translatedFormat('l j F') }}
    </h2>

    @if ($quote)
        <blockquote @class([
            'mx-auto mt-5 max-w-xl whitespace-pre-line leading-[1.9] text-[#2B2520] [text-wrap:balance]',
            'text-3xl sm:text-4xl' => $length <= 60,
            'text-2xl sm:text-3xl' => $length > 60 && $length <= 110,
            'text-xl sm:text-2xl' => $length > 110,
            'italic' => $font['italic'] ?? false,
        ]) style="font-family: {{ $font['stack'] }};">{{ $quote->readableText() }}</blockquote>
    @else
        <x-verse :sadr="$verse->sadr" :ajuz="$verse->ajuz" class="mx-auto mt-5 max-w-xl text-2xl text-[#2B2520] sm:text-3xl" />
    @endif

    <span aria-hidden="true" class="mx-auto mt-6 block h-px w-24 bg-gradient-to-r from-transparent via-[#A8873F] to-transparent"></span>

    @if ($quote)
        @if ($byline)
            <p class="mt-4 font-text text-lg text-[#2B2520]">{{ $byline }}</p>
        @endif
        <div class="mt-5 flex flex-wrap items-center justify-center gap-x-4 gap-y-2 text-sm text-[#7A7068]">
            <a href="{{ route('profile.show', $quote->user) }}" class="hover:text-[#8A3324]">شاركه {{ $quote->user->name }}</a>
            <a href="{{ route('quotes.show', $quote) }}" class="inline-flex items-center gap-1 font-medium text-[#8A3324] hover:underline">
                افتح الاقتباس <x-icon name="chevron-left" class="size-4" />
            </a>
        </div>
    @else
        <p class="mt-4 font-text text-lg text-[#2B2520]">{{ $verse->work->poet->name }}</p>
        <div class="mt-5 flex flex-wrap items-center justify-center gap-x-4 gap-y-2 text-sm text-[#7A7068]">
            <a href="{{ route('library.work', $verse->work) }}" class="hover:text-[#8A3324]">{{ $verse->work->title }}</a>
            <a href="{{ route('quotes.create', ['verses' => [$verse->id], 'as' => 'card']) }}" class="inline-flex items-center gap-1 font-medium text-[#8A3324] hover:underline">
                اقتبسه وصمّمه <x-icon name="chevron-left" class="size-4" />
            </a>
        </div>
    @endif
</section>
