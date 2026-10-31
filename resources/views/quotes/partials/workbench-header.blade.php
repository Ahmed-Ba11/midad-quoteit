{{--
    The top of «محرّر النص» and مرسم الحروف: back, the page's name, and «نص / بطاقة» to show the same
    quote the other way. It stays in view while the tools scroll.
    Switching keeps everything not saved yet: the form goes through QuoteController@draft (a new quote
    or a library pick) or @redraft (a saved quote) with what was chosen so far.
    Needs: $quote, $current ('text' | 'card'), $title, $backUrl.
--}}
@php
    $switchAction = $quote->exists ? route('quotes.redraft', $quote) : route('quotes.draft');
    $option = 'inline-flex h-10 flex-1 items-center justify-center gap-1.5 rounded-full px-4 text-sm transition';
    $labels = ['text' => ['type', 'نص'], 'card' => ['palette', 'بطاقة']];
@endphp

<header class="sticky top-16 z-20 -mx-4 border-b border-line bg-paper/95 px-4 py-2 lg:col-span-2 lg:mx-0 lg:rounded-2xl lg:border lg:px-3">
    <div class="flex items-center gap-2">
        <a href="{{ $backUrl }}" data-back class="grid size-10 shrink-0 place-items-center rounded-full border border-line bg-surface text-muted transition hover:border-accent hover:text-accent" aria-label="رجوع" title="رجوع">
            <x-icon name="back" class="size-5" />
        </a>
        <h1 class="min-w-0 flex-1 truncate font-display text-xl font-bold sm:text-2xl">{{ $title }}</h1>

        @if (request()->boolean('draft'))
            <input type="hidden" name="from_draft" value="1">
        @endif
        <div role="group" aria-label="طريقة العرض" class="flex w-44 shrink-0 rounded-full border border-line bg-surface p-1">
            @foreach (['text', 'card'] as $way)
                @if ($way === $current)
                    <span aria-current="true" class="{{ $option }} bg-ink font-medium text-paper"><x-icon :name="$labels[$way][0]" class="size-4" /> {{ $labels[$way][1] }}</span>
                @else
                    <button type="submit" formaction="{{ $switchAction }}" formnovalidate name="switch_to" value="{{ $way }}"
                        class="{{ $option }} text-muted hover:bg-accent-soft hover:text-accent"><x-icon :name="$labels[$way][0]" class="size-4" /> {{ $labels[$way][1] }}</button>
                @endif
            @endforeach
        </div>
    </div>
</header>
