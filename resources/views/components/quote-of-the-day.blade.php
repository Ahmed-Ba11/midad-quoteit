@props(['quote'])

@php
    $font = config('quotes.fonts.'.$quote->font, config('quotes.fonts.amiri'));
    $byline = collect([$quote->author, $quote->book])->filter()->implode(' · ');
    $diamond = 'absolute size-3 rotate-45 border border-gold bg-paper';
@endphp

{{-- Manuscript frame: thin double gold border, corner diamonds, faint stains. --}}
<section aria-labelledby="quote-of-the-day" {{ $attributes->class('relative overflow-hidden rounded-3xl bg-surface px-6 py-12 sm:px-16 sm:py-16') }}
    style="background-image: radial-gradient(ellipse at 15% 20%, rgb(168 135 63 / 0.08), transparent 45%), radial-gradient(ellipse at 85% 80%, rgb(138 51 36 / 0.06), transparent 40%);">
    <div aria-hidden="true" class="pointer-events-none absolute inset-3 rounded-2xl border border-gold/50"></div>
    <div aria-hidden="true" class="pointer-events-none absolute inset-5 rounded-xl border border-gold/25"></div>
    <span aria-hidden="true" class="{{ $diamond }} start-1.5 top-1.5"></span>
    <span aria-hidden="true" class="{{ $diamond }} end-1.5 top-1.5"></span>
    <span aria-hidden="true" class="{{ $diamond }} bottom-1.5 start-1.5"></span>
    <span aria-hidden="true" class="{{ $diamond }} bottom-1.5 end-1.5"></span>

    <div class="relative text-center">
        <h2 id="quote-of-the-day" class="font-display text-lg font-bold text-gold">اقتبـــاس اليوم</h2>

        <blockquote class="mx-auto mt-5 max-w-3xl text-2xl leading-[1.9] sm:text-3xl @if ($font['italic'] ?? false) italic @endif"
            style="font-family: {{ $font['stack'] }};">
            {{ $quote->content }}
        </blockquote>

        <p class="mt-5 text-sm text-muted">
            @if ($byline)
                — {{ $byline }} ·
            @endif
            شاركه {{ $quote->user->name }}
        </p>

        <a href="{{ route('quotes.show', $quote) }}" class="mt-6 inline-flex items-center gap-1 text-sm font-medium text-accent hover:underline">
            افتح الاقتباس <x-icon name="chevron-left" class="size-4" />
        </a>
    </div>
</section>
