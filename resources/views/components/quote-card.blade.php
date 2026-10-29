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
            <p class="truncate text-sm font-medium">{{ $quote->user->name }}</p>
            <time datetime="{{ $quote->created_at->toDateString() }}" class="text-xs text-muted">
                {{ $quote->created_at->translatedFormat('j F Y') }}
            </time>
        </div>

        @if ($quote->category)
            <x-chip class="h-7 px-3 text-xs">{{ $quote->category->name }}</x-chip>
        @endif
    </div>

    @if ($quote->story)
        <a href="{{ route('quotes.show', $quote) }}#story" class="mx-5 mt-3 inline-flex w-fit items-center gap-1 text-xs text-gold hover:underline">
            <x-icon name="book-open" class="size-4" /> له حكاية
        </a>
    @endif

    <footer class="mt-2 flex items-center border-t border-line px-3 py-1.5">
        <x-quote-actions :quote="$quote" class="flex-1" />

        @can('update', $quote)
            <a href="{{ request()->fullUrlWithQuery(['edit' => $quote->id]) }}" aria-label="تعديل" title="تعديل النص"
                class="grid size-11 place-items-center rounded-full text-muted transition hover:bg-accent-soft hover:text-accent">
                <x-icon name="pencil" class="size-[18px]" />
            </a>
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
