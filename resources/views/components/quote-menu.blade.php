@props(['quote'])

{{--
    "⋯" on a quote card: everything beyond like / reply / save, in one tidy menu —
    copy the words, reuse the design for a new quote, and (for its owner) edit, redesign, pin, delete.
--}}
@php
    $signature = $quote->is_original && blank($quote->author) ? 'بقلم '.$quote->user->name : $quote->author;
    $copyText = $quote->readableText().($signature ? ' — '.$signature : '').($quote->sourceLabel() && ! $quote->is_original ? ' ('.$quote->sourceLabel().')' : '');
    $item = 'flex min-h-11 w-full items-center gap-3 rounded-xl px-3 text-start text-sm transition hover:bg-accent-soft hover:text-accent';
@endphp

<details data-menu {{ $attributes->class('relative') }}>
    <summary aria-label="المزيد" title="المزيد"
        class="grid size-11 cursor-pointer list-none place-items-center rounded-full text-muted transition hover:bg-accent-soft hover:text-accent [&::-webkit-details-marker]:hidden">
        <svg viewBox="0 0 24 24" aria-hidden="true" class="size-5" fill="currentColor"><circle cx="5" cy="12" r="1.8" /><circle cx="12" cy="12" r="1.8" /><circle cx="19" cy="12" r="1.8" /></svg>
    </summary>

    <div class="absolute bottom-full end-0 z-30 mb-2 w-60 rounded-2xl border border-line bg-surface p-1.5 shadow-xl">
        <button type="button" data-copy="{{ $copyText }}" class="{{ $item }}">
            <x-icon name="copy" class="size-[18px]" data-copy-icon />
            <x-icon name="check" class="hidden size-[18px]" data-copied-icon />
            <span data-copy-label>انسخ النص</span>
        </button>
        <a href="{{ route('quotes.create', ['template' => $quote->id]) }}" class="{{ $item }}">
            <x-icon name="palette" class="size-[18px]" /> استخدم التصميم لنصّك
        </a>

        @can('update', $quote)
            <div class="my-1 border-t border-line"></div>
            @unless ($quote->isFromLibrary())
                <a href="{{ request()->routeIs('home') ? request()->fullUrlWithQuery(['edit' => $quote->id]) : route('quotes.edit', [$quote, 'as' => 'text']) }}" class="{{ $item }}">
                    <x-icon name="pencil" class="size-[18px]" /> عدّل النص
                </a>
            @endunless
            <a href="{{ route('quotes.edit', $quote) }}" class="{{ $item }}">
                <x-icon name="feather" class="size-[18px]" /> أعد تصميمه
            </a>
            @php $isPinned = auth()->user()->pinned_quote_id === $quote->id; @endphp
            <form method="POST" action="{{ route('profile.pin', $quote) }}">
                @csrf
                <button type="submit" class="{{ $item }}">
                    <x-icon name="pin" @class(['size-[18px]', 'fill-current text-accent' => $isPinned]) />
                    {{ $isPinned ? 'ألغِ التثبيت من ركني' : 'ثبّته في ركني' }}
                </button>
            </form>
            <form method="POST" action="{{ route('delete_quote', $quote) }}" data-confirm="يُحذف الاقتباس نهائياً. متأكد؟">
                @csrf
                @method('DELETE')
                <button type="submit" class="{{ $item }} text-accent">
                    <x-icon name="trash" class="size-[18px]" /> احذفه
                </button>
            </form>
        @endcan
    </div>
</details>
