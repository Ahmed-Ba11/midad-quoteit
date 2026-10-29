@props(['paginator'])

@if ($paginator->hasPages())
    <nav aria-label="التنقل بين الصفحات" {{ $attributes->class('flex items-center justify-center gap-1.5') }}>
        @if ($paginator->onFirstPage())
            <span class="grid size-11 place-items-center rounded-full text-muted/50"><x-icon name="chevron-right" /></span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="الصفحة السابقة"
                class="grid size-11 place-items-center rounded-full transition hover:bg-accent-soft hover:text-accent">
                <x-icon name="chevron-right" />
            </a>
        @endif

        @foreach ($paginator->getUrlRange(1, $paginator->lastPage()) as $page => $url)
            @if ($page === $paginator->currentPage())
                <span aria-current="page" class="grid size-11 place-items-center rounded-full bg-ink font-medium text-paper">{{ $page }}</span>
            @else
                <a href="{{ $url }}" class="grid size-11 place-items-center rounded-full transition hover:bg-accent-soft hover:text-accent">{{ $page }}</a>
            @endif
        @endforeach

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="الصفحة التالية"
                class="grid size-11 place-items-center rounded-full transition hover:bg-accent-soft hover:text-accent">
                <x-icon name="chevron-left" />
            </a>
        @else
            <span class="grid size-11 place-items-center rounded-full text-muted/50"><x-icon name="chevron-left" /></span>
        @endif
    </nav>
@endif
