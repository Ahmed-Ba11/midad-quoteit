@props(['quote'])

<article id="quote-{{ $quote->id }}" {{ $attributes->class('flex scroll-mt-24 flex-col overflow-hidden rounded-3xl border border-line bg-surface shadow-sm transition duration-300 hover:-translate-y-0.5 hover:shadow-lg') }}>
    <a href="{{ route('quotes.show', $quote) }}" aria-label="افتح صفحة الاقتباس" class="block focus-visible:outline-2 focus-visible:-outline-offset-4 focus-visible:outline-accent">
        <x-quote-art :quote="$quote" />
    </a>

    <div class="flex items-center gap-3 px-5 pt-4">
        <span class="grid size-9 shrink-0 place-items-center rounded-full bg-accent-soft text-sm font-bold text-accent">
            {{ mb_substr($quote->user->name, 0, 1) }}
        </span>

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

    @if ($quote->book_id)
        <a href="{{ route('books.show', $quote->book_id) }}" class="mx-5 mt-3 inline-flex w-fit items-center gap-1 text-xs text-muted hover:text-accent">
            <x-icon name="book-open" class="size-4" /> صفحة الكتاب
        </a>
    @endif

    @if ($quote->story)
        <a href="{{ route('quotes.show', $quote) }}#story" class="mx-5 mt-3 inline-flex w-fit items-center gap-1 text-xs text-gold hover:underline">
            <x-icon name="book-open" class="size-4" /> له حكاية
        </a>
    @endif

    <footer class="mt-2 flex items-center border-t border-line px-3 py-1.5">
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
            <a href="{{ route('quotes.edit', $quote) }}" aria-label="تصميم" title="أعد تصميمه في رواق الحرف"
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
