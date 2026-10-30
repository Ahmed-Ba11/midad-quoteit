@props(['quote'])

@php
    $sharer = $quote->user->accentColors();
    // Where the words come from, in one quiet line: the library, the writer, or the named source.
    $origin = match (true) {
        $quote->isFromLibrary() => ['icon' => 'book-open', 'text' => 'من خزانة الأدب'.($quote->book ? ' · '.$quote->book : '')],
        $quote->is_original => ['icon' => 'feather', 'text' => 'من تأليف '.$quote->user->name],
        filled($quote->sourceLabel()) => ['icon' => 'book-open', 'text' => 'المصدر: '.$quote->sourceLabel()],
        default => null,
    };
@endphp

<article id="quote-{{ $quote->id }}" {{ $attributes->class('group/card flex scroll-mt-24 flex-col overflow-hidden rounded-3xl border border-line bg-surface shadow-sm transition duration-300 hover:-translate-y-0.5 hover:shadow-lg') }}>
    <a href="{{ route('quotes.show', $quote) }}" aria-label="افتح صفحة الاقتباس" class="relative block focus-visible:outline-2 focus-visible:-outline-offset-4 focus-visible:outline-accent">
        <x-quote-art :quote="$quote" uniform />
        @unless ($quote->is_public)
            <span class="absolute start-3 top-3 inline-flex items-center gap-1 rounded-full bg-black/55 px-2.5 py-1 text-[11px] text-white backdrop-blur">
                <x-icon name="lock" class="size-3" /> خاص بك
            </span>
        @endunless
    </a>

    <div class="flex items-center gap-3 px-5 pt-4">
        <a href="{{ route('profile.show', $quote->user) }}" aria-hidden="true" tabindex="-1"
            class="grid size-9 shrink-0 place-items-center rounded-full text-sm font-bold ring-2 ring-surface transition group-hover/card:ring-line"
            style="background: {{ $sharer['bg'] }}; color: {{ $sharer['highlight'] }}">
            {{ mb_substr($quote->user->name, 0, 1) }}
        </a>

        <div class="min-w-0 flex-1 leading-tight">
            <a href="{{ route('profile.show', $quote->user) }}" class="block truncate text-sm font-medium hover:text-accent">{{ $quote->user->name }}</a>
            <time datetime="{{ $quote->created_at->toDateString() }}" class="text-xs text-muted">
                {{ $quote->created_at->translatedFormat('j F Y') }}
            </time>
        </div>

        @if ($quote->category)
            <x-chip class="h-7 px-3 text-xs">{{ $quote->category->name }}</x-chip>
        @endif
    </div>

    @if ($origin)
        <p class="mx-5 mt-3 flex min-w-0 items-center gap-1.5 text-xs text-muted">
            <x-icon :name="$origin['icon']" class="size-3.5 shrink-0" />
            @if ($quote->book_id && ! $quote->isFromLibrary())
                <a href="{{ route('books.show', $quote->book_id) }}" class="truncate hover:text-accent">{{ $origin['text'] }}</a>
            @else
                <span class="truncate">{{ $origin['text'] }}</span>
            @endif
            @if ($quote->sourceUrl())
                <a href="{{ $quote->sourceUrl() }}" target="_blank" rel="nofollow ugc noopener noreferrer"
                    class="shrink-0 text-accent hover:underline" title="أكمل القراءة في المصدر">
                    <x-icon name="external" class="size-3.5" /><span class="sr-only">أكمل القراءة في المصدر (موقع خارجي)</span>
                </a>
            @endif
        </p>
    @endif

    {{-- حكاية الاقتباس: a two-line taste of why it mattered to the sharer. --}}
    @if ($quote->story)
        <a href="{{ route('quotes.show', $quote) }}#story"
            class="mx-5 mt-3 block rounded-2xl border border-gold/30 bg-paper px-4 py-3 transition hover:border-gold">
            <span class="flex items-center gap-1.5 text-[11px] font-medium text-gold">
                <x-ornament name="star" class="size-3" /> حكاية الاقتباس
            </span>
            <span class="mt-1 line-clamp-2 block font-text text-sm leading-relaxed text-ink/80">{{ $quote->story }}</span>
        </a>
    @endif

    {{-- Pushes the actions to the bottom, so every card in a row ends on the same line. --}}
    <div class="min-h-3 flex-1"></div>

    <footer class="flex items-center border-t border-line px-3 py-1.5">
        <x-quote-actions :quote="$quote" class="flex-1" />

        @can('update', $quote)
            @php $isPinned = auth()->user()->pinned_quote_id === $quote->id; @endphp
            {{-- Pinning lives in ركني (and on the quote page), to keep the card footer light elsewhere. --}}
            <form method="POST" action="{{ route('profile.pin', $quote) }}" @class(['hidden' => ! request()->routeIs('profile.show')])>
                @csrf
                <button type="submit" aria-pressed="{{ $isPinned ? 'true' : 'false' }}"
                    aria-label="{{ $isPinned ? 'إلغاء التثبيت من ركني' : 'ثبّت في ركني' }}" title="{{ $isPinned ? 'مثبّت في ركنك' : 'ثبّت في ركني' }}"
                    @class(['grid size-11 place-items-center rounded-full transition hover:bg-accent-soft hover:text-accent', 'text-accent' => $isPinned, 'text-muted' => ! $isPinned])>
                    <x-icon name="pin" @class(['size-[18px]', 'fill-current' => $isPinned]) />
                </button>
            </form>
            @unless ($quote->isFromLibrary())
                <a href="{{ request()->fullUrlWithQuery(['edit' => $quote->id]) }}" aria-label="تعديل" title="تعديل النص"
                    class="grid size-11 place-items-center rounded-full text-muted transition hover:bg-accent-soft hover:text-accent">
                    <x-icon name="pencil" class="size-[18px]" />
                </a>
            @endunless
            <a href="{{ route('quotes.edit', $quote) }}" aria-label="تصميم" title="أعد تصميمه في مرسم الحروف"
                class="grid size-11 place-items-center rounded-full text-muted transition hover:bg-accent-soft hover:text-accent">
                <x-icon name="palette" class="size-[18px]" />
            </a>
        @endcan

        @can('delete', $quote)
            <form method="POST" action="{{ route('delete_quote', $quote) }}">
                @csrf
                @method('DELETE')
                <button type="submit" aria-label="حذف" title="حذف"
                    class="grid size-11 place-items-center rounded-full text-muted transition hover:bg-accent-soft hover:text-accent">
                    <x-icon name="trash" class="size-[18px]" />
                </button>
            </form>
        @endcan
    </footer>
</article>
