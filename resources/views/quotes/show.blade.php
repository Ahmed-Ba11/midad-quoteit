@extends('layouts.app')

@section('title', \Illuminate\Support\Str::limit(str_replace("\n", ' ', $quote->readableText()), 40))

@section('content')
    @php
        $ratioWidth = $quote->presentation === 'text' ? 760 : config('quotes.ratios.'.$quote->ratio.'.width', 760);
        $signature = $quote->is_original && blank($quote->author) ? 'بقلم '.$quote->user->name : $quote->author;
        $byline = collect([$signature, $quote->book])->filter()->implode(' · ');
    @endphp

    <div class="mx-auto grid max-w-6xl gap-10 px-4 py-10 lg:grid-cols-[1fr_300px]">
        <div class="min-w-0">
            <x-back :href="route('home')" label="الاقتباسات" />
            {{-- Big card --}}
            <article id="quote-{{ $quote->id }}" class="scroll-mt-24">
                @unless ($quote->is_public)
                    <p class="mx-auto mb-4 flex items-center gap-2 rounded-2xl border border-line bg-surface px-4 py-3 text-sm text-muted" style="max-width: {{ $ratioWidth }}px">
                        <x-icon name="lock" class="size-4 text-accent" /> اقتباس خاص: يظهر في ركنك وحدك، ولا يراه أحد غيرك.
                    </p>
                @endunless

                <div class="mx-auto overflow-hidden rounded-3xl shadow-xl" style="max-width: {{ $ratioWidth }}px">
                    <x-quote-art :quote="$quote" />
                </div>

                <div class="mx-auto mt-5 flex flex-wrap items-center gap-x-4 gap-y-3" style="max-width: {{ $ratioWidth }}px">
                    <a href="{{ route('profile.show', $quote->user) }}" class="flex min-w-0 flex-1 items-center gap-3 hover:text-accent">
                        <x-avatar :user="$quote->user" size="size-10" />
                        <span class="min-w-0 leading-tight">
                            <span class="block font-medium">{{ $quote->user->name }}</span>
                            <time datetime="{{ $quote->created_at->toDateString() }}" class="text-sm text-muted">
                                {{ $quote->created_at->translatedFormat('j F Y') }}
                            </time>
                        </span>
                    </a>

                    @if ($quote->category)
                        <x-chip>{{ $quote->category->name }}</x-chip>
                    @endif

                    <div class="flex w-full items-center gap-1 border-y border-line py-1 sm:w-auto sm:border-0 sm:py-0">
                        <x-quote-actions :quote="$quote" :show-replies="false" class="flex-1 sm:flex-none" />
                        <a href="{{ route('quotes.create', ['template' => $quote->id]) }}" title="صمّم اقتباسك بالخط والألوان نفسها"
                            class="inline-flex h-11 items-center gap-1.5 rounded-full px-3 text-sm text-muted transition hover:bg-accent-soft hover:text-accent">
                            <x-icon name="palette" class="size-[18px]" /> صمّم مثله
                        </a>
                        <x-quote-menu :quote="$quote" />
                    </div>
                </div>

                {{-- The source by its name; the link (if any) only lets the reader read on. --}}
                @if ($quote->sourceLabel() && ! $quote->isFromLibrary())
                    <div class="mx-auto mt-4 flex flex-wrap items-center gap-x-3 gap-y-2 rounded-2xl bg-surface px-4 py-3 text-sm" style="max-width: {{ $ratioWidth }}px">
                        <x-icon name="book-open" class="size-4 text-gold" />
                        <span class="text-muted">المصدر:</span>
                        <span class="font-medium">{{ $quote->sourceLabel() }}</span>
                        @if ($quote->sourceUrl())
                            {{-- A user-supplied link: no ranking credit, no referrer, no opener. --}}
                            <a href="{{ $quote->sourceUrl() }}" target="_blank" rel="nofollow ugc noopener noreferrer"
                                class="ms-auto inline-flex items-center gap-1 rounded-full border border-line px-3 py-1 text-xs text-accent transition hover:border-accent">
                                أكمل القراءة <x-icon name="external" class="size-3.5" /><span class="sr-only">(موقع خارجي)</span>
                            </a>
                        @endif
                    </div>
                @endif
            </article>

            {{-- حكاية الاقتباس: the sharer's note, set as a handwritten page with their name at the foot. --}}
            @if ($quote->story)
                <section id="story" class="manuscript-page relative mx-auto mt-8 scroll-mt-24 rounded-3xl px-8 py-10 sm:px-12" style="max-width: {{ $ratioWidth }}px">
                    <p class="flex items-center justify-center gap-2 text-sm font-medium text-[#A8873F]">
                        <x-ornament name="star" class="size-4" /> حكاية الاقتباس <x-ornament name="star" class="size-4" />
                    </p>
                    <p class="mt-4 whitespace-pre-line text-center font-text text-xl leading-loose">{{ $quote->story }}</p>
                    <p class="mt-5 text-center text-sm text-[#7A7068]">— {{ $quote->user->name }}</p>
                </section>
            @endif

            {{-- مختاراتي: one line that opens into my anthologies (toggle each), or a new one with this quote. --}}
            @auth
                @php $inMine = $myAnthologies->where('has_quote', true)->count(); @endphp
                <details id="anthologies" class="group mx-auto mt-6 scroll-mt-24 rounded-2xl border border-line bg-surface" style="max-width: {{ $ratioWidth }}px"
                    @if ($errors->hasAny(['title', 'description', 'palette'])) open @endif>
                    <summary class="flex min-h-12 cursor-pointer list-none items-center gap-2 px-4 text-sm [&::-webkit-details-marker]:hidden">
                        <x-icon name="layers" class="size-5 text-accent" />
                        <span class="flex-1 font-medium">أضف إلى مختاراتي</span>
                        @if ($inMine)
                            <span class="rounded-full bg-accent-soft px-2.5 py-0.5 text-xs text-accent">في {{ $inMine }}</span>
                        @endif
                        <x-icon name="chevron-left" class="size-4 text-muted transition group-open:-rotate-90" />
                    </summary>
                    <div class="flex flex-wrap gap-2 border-t border-line p-4">
                        @foreach ($myAnthologies as $anthology)
                            <form method="POST" action="{{ route('anthologies.toggle', [$anthology, $quote]) }}">
                                @csrf
                                <button type="submit" aria-pressed="{{ $anthology->has_quote ? 'true' : 'false' }}"
                                    @class([
                                        'inline-flex min-h-11 items-center gap-1.5 rounded-full border px-4 text-sm transition',
                                        'border-accent bg-accent-soft text-accent' => $anthology->has_quote,
                                        'border-line hover:border-accent hover:text-accent' => ! $anthology->has_quote,
                                    ])>
                                    <span aria-hidden="true" class="size-2.5 rounded-full" style="background: {{ $anthology->colors()['bg'] }}"></span>
                                    {{ $anthology->title }}
                                    @if ($anthology->has_quote)
                                        <x-icon name="check" class="size-4" />
                                    @endif
                                </button>
                            </form>
                        @endforeach
                        @if ($myAnthologies->isEmpty())
                            <p class="text-sm text-muted">اجمع ما يعجبك في مختارات باسمها: «أبيات الصبر»، «من رمضان»…</p>
                        @endif
                    </div>
                    <details class="border-t border-line" @if ($errors->hasAny(['title', 'description', 'palette'])) open @endif>
                        <summary class="flex min-h-12 cursor-pointer list-none items-center gap-2 px-4 text-sm text-accent [&::-webkit-details-marker]:hidden">
                            <x-icon name="plus" class="size-4" /> مختارات جديدة بهذا الاقتباس
                        </summary>
                        @include('anthologies.partials.form', ['anthology' => new \App\Models\Anthology, 'action' => route('anthologies.store'), 'quoteId' => $quote->id])
                    </details>
                </details>
            @endauth

            @if ($inAnthologies->isNotEmpty())
                <p class="mx-auto mt-4 flex flex-wrap items-center gap-x-2 gap-y-1 text-sm text-muted" style="max-width: {{ $ratioWidth }}px">
                    <x-icon name="layers" class="size-4" /> في مختارات:
                    @foreach ($inAnthologies as $anthology)
                        <a href="{{ route('anthologies.show', $anthology) }}" class="text-accent hover:underline">«{{ $anthology->title }}» لـ{{ $anthology->user->name }}</a>@unless ($loop->last)<span>،</span>@endunless
                    @endforeach
                </p>
            @endif

            {{-- Replies --}}
            <section id="replies" class="mx-auto mt-10 scroll-mt-24" style="max-width: {{ $ratioWidth }}px">
                <x-epigraph context="conversation" class="mb-3" />
                <h2 class="font-display text-2xl font-bold">الردود <span class="text-base font-normal text-muted">({{ $quote->replies->count() }})</span></h2>

                @auth
                    <form method="POST" action="{{ route('replies.store', $quote) }}" class="mt-5 space-y-3">
                        @csrf
                        <x-textarea name="body" label="أضف ردّك" hint="حتى ٣٠٠ حرف" rows="3" maxlength="300" required
                            placeholder="ماذا أثار فيك هذا الاقتباس؟" :value="old('body')" />
                        <x-button type="submit">أرسل الرد</x-button>
                    </form>
                @else
                    <p class="mt-4 rounded-2xl border border-dashed border-line p-5 text-center text-muted">
                        <a href="{{ route('login') }}" class="font-medium text-accent hover:underline">سجّل دخولك</a> لتشارك ردّك.
                    </p>
                @endauth

                <ol class="mt-6 space-y-4">
                    @forelse ($quote->replies as $reply)
                        <li class="flex gap-3 rounded-2xl border border-line bg-surface p-4">
                            <x-avatar :user="$reply->user" size="size-9" />
                            <div class="min-w-0 flex-1">
                                <p class="text-sm">
                                    <span class="font-medium">{{ $reply->user->name }}</span>
                                    <time datetime="{{ $reply->created_at->toIso8601String() }}" class="text-xs text-muted">· {{ $reply->created_at->gt(now()->subMinute()) ? 'الآن' : $reply->created_at->diffForHumans() }}</time>
                                </p>
                                <p class="mt-1 whitespace-pre-line leading-relaxed">{{ $reply->body }}</p>
                            </div>
                        </li>
                    @empty
                        <li class="text-center text-sm text-muted">لا ردود بعد. كن أول من يردّ.</li>
                    @endforelse
                </ol>
            </section>
        </div>

        {{-- Related --}}
        <aside aria-labelledby="related-title">
            <h2 id="related-title" class="font-display text-xl font-bold">قد يعجبـــك أيضاً</h2>

            @if ($related->isEmpty())
                <p class="mt-3 text-sm text-muted">لا اقتراحات بعد لهذا الاقتباس.</p>
            @else
                <ul class="mt-4 space-y-5">
                    @foreach ($related as $item)
                        <li>
                            <a href="{{ route('quotes.show', $item['quote']) }}" class="group block overflow-hidden rounded-2xl border border-line bg-surface transition hover:shadow-lg">
                                <x-quote-art :quote="$item['quote']" />
                                <div class="flex items-center justify-between gap-2 px-4 py-3 text-xs">
                                    <span class="truncate text-muted">{{ $item['quote']->user->name }}</span>
                                    <x-chip class="h-7 shrink-0 px-3 text-xs">{{ $item['reason'] }}</x-chip>
                                </div>
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </aside>
    </div>
@endsection
