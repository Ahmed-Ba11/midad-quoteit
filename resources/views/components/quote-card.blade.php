@props(['quote'])

<article {{ $attributes->class('flex flex-col overflow-hidden rounded-3xl border border-line bg-surface shadow-sm transition duration-300 hover:-translate-y-0.5 hover:shadow-lg') }}>
    <div class="flex min-h-52 flex-1 items-center justify-center px-[10%] py-10 text-center"
        style="background-color: #E8E1D5; color: #1E1A17;">
        <blockquote class="font-text text-xl leading-[1.8]">
            <p>{{ $quote->content }}</p>
        </blockquote>
    </div>

    <footer class="flex items-center gap-3 px-5 py-3">
        <span class="grid size-9 shrink-0 place-items-center rounded-full bg-accent-soft text-sm font-bold text-accent">
            {{ mb_substr($quote->user->name, 0, 1) }}
        </span>

        <div class="min-w-0 flex-1 leading-tight">
            <p class="truncate text-sm font-medium">{{ $quote->user->name }}</p>
            <time datetime="{{ $quote->created_at->toDateString() }}" class="text-xs text-muted">
                {{ $quote->created_at->translatedFormat('j F Y') }}
            </time>
        </div>

        @can('update', $quote)
            <a href="{{ request()->fullUrlWithQuery(['edit' => $quote->id]) }}" aria-label="تعديل" title="تعديل"
                class="grid size-11 place-items-center rounded-full text-muted transition hover:bg-accent-soft hover:text-accent">
                <x-icon name="pencil" class="size-[18px]" />
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
