@extends('layouts.app')

@section('title', $book->title)

@section('content')
    <x-page-wall />
    @php
        $statuses = config('quotes.reading_statuses');
    @endphp

    <div class="mx-auto max-w-6xl px-4 py-10">
        <x-back :href="route('books.index')" label="رفوف القرّاء" />

        <header class="grid gap-10 md:grid-cols-[220px_1fr] md:items-start">
            <x-book-cover :book="$book" size="lg" class="mx-auto w-48 md:w-full" />

            <div class="text-center md:text-start">
                @if ($book->genreLabel())
                    <x-chip>{{ $book->genreLabel() }}</x-chip>
                @endif
                <h1 class="mt-3 font-display text-4xl font-bold sm:text-5xl">{{ $book->title }}</h1>
                @if ($book->author)
                    <p class="mt-2 text-xl text-muted">{{ $book->author }}</p>
                @endif

                <dl class="mt-6 flex flex-wrap justify-center gap-6 md:justify-start">
                    <div>
                        <dt class="text-xs text-muted">اقتباس</dt>
                        <dd class="font-display text-3xl font-bold">{{ $book->quotes_count }}</dd>
                    </div>
                    @foreach ($statuses as $key => $label)
                        <div>
                            <dt class="text-xs text-muted">{{ $label }}</dt>
                            <dd class="font-display text-3xl font-bold">{{ $shelfCounts[$key] ?? 0 }}</dd>
                        </div>
                    @endforeach
                </dl>

                {{-- The reader's own shelf status for this book. --}}
                <section id="shelf" class="mt-8 scroll-mt-24">
                    @auth
                        <form method="POST" action="{{ route('books.shelf', $book) }}" class="flex flex-wrap justify-center gap-2 md:justify-start">
                            @csrf
                            <span class="me-1 self-center text-sm text-muted">في رفّي:</span>
                            @foreach ($statuses as $key => $label)
                                <button type="submit" name="status" value="{{ $key }}" aria-pressed="{{ $myStatus === $key ? 'true' : 'false' }}"
                                    class="h-10 rounded-full border border-line bg-surface px-4 text-sm transition hover:border-accent hover:text-accent aria-pressed:border-ink aria-pressed:bg-ink aria-pressed:text-paper">
                                    {{ $label }}
                                </button>
                            @endforeach
                            @if ($myStatus)
                                <button type="submit" name="status" value="" class="h-10 rounded-full px-3 text-sm text-muted hover:text-accent">إزالة من رفّي</button>
                            @endif
                        </form>
                        <p class="mt-2 text-xs text-muted">رفّك يراه أنت فقط في صفحتك الشخصية.</p>
                    @else
                        <p class="text-sm text-muted"><a href="{{ route('login') }}" class="text-accent hover:underline">سجّل دخولك</a> لتضيف الكتاب إلى رفّك.</p>
                    @endauth
                </section>

                @auth
                    <x-button :href="route('quotes.create', array_filter(['book' => $book->title, 'author' => $book->author]))"
                        icon="feather" class="mt-6">أضف اقتباساً من هذا الكتاب</x-button>
                @endauth
            </div>
        </header>

        <section class="mt-14" aria-labelledby="book-quotes">
            <h2 id="book-quotes" class="font-display text-2xl font-bold">أجمل ما اقتبسه القرّاء</h2>
            <p class="mt-1 text-sm text-muted">مرتّبة حسب الإعجاب. كل اقتباس قصير ومنسوب لكتابه ومؤلفه.</p>

            @if ($quotes->isEmpty())
                <x-empty-state title="لا اقتباسات بعد" icon="feather" class="mt-6">كن أول من يقتبس من هذا الكتاب.</x-empty-state>
            @else
                <div class="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($quotes as $quote)
                        <x-quote-card :quote="$quote" />
                    @endforeach
                </div>
                <x-pagination :paginator="$quotes" class="mt-10" />
            @endif
        </section>
    </div>
@endsection
