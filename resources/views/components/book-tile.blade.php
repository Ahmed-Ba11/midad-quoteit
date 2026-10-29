@props(['book'])

<a href="{{ route('books.show', $book) }}" class="group block focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-accent">
    <x-book-cover :book="$book" size="sm" class="transition duration-300 group-hover:-translate-y-1.5 group-hover:shadow-xl" />
    <p class="mt-3 line-clamp-2 text-sm font-medium leading-snug group-hover:text-accent">{{ $book->title }}</p>
    @if ($book->author)
        <p class="truncate text-xs text-muted">{{ $book->author }}</p>
    @endif
    <p class="mt-1 text-xs text-muted">
        {{ $book->quotes_count ?? 0 }} اقتباس
        @if (($book->readers_count ?? 0) > 0)
            · {{ $book->readers_count }} قارئ
        @endif
    </p>
</a>
