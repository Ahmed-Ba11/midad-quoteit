@extends('layouts.app')

@section('title', \Illuminate\Support\Str::limit($quote->content, 40))

@section('content')
    @php
        $ratioWidth = config('quotes.ratios.'.$quote->ratio.'.width', 760);
        $byline = collect([$quote->author, $quote->book])->filter()->implode(' · ');
    @endphp

    <div class="mx-auto grid max-w-6xl gap-10 px-4 py-10 lg:grid-cols-[1fr_300px]">
        <div class="min-w-0">
            {{-- Big card --}}
            <article id="quote-{{ $quote->id }}" class="scroll-mt-24">
                <div class="mx-auto shadow-xl" style="max-width: {{ $ratioWidth }}px">
                    <x-quote-art :quote="$quote" class="rounded-3xl" />
                </div>

                <div class="mx-auto mt-5 flex flex-wrap items-center gap-x-4 gap-y-3" style="max-width: {{ $ratioWidth }}px">
                    <span class="grid size-10 shrink-0 place-items-center rounded-full bg-accent-soft font-bold text-accent">
                        {{ mb_substr($quote->user->name, 0, 1) }}
                    </span>
                    <div class="min-w-0 flex-1 leading-tight">
                        <p class="font-medium">{{ $quote->user->name }}</p>
                        <time datetime="{{ $quote->created_at->toDateString() }}" class="text-sm text-muted">
                            {{ $quote->created_at->translatedFormat('j F Y') }}
                        </time>
                    </div>

                    @if ($quote->category)
                        <x-chip>{{ $quote->category->name }}</x-chip>
                    @endif

                    <div class="flex w-full items-center gap-1 border-y border-line py-1 sm:w-auto sm:border-0 sm:py-0">
                        <x-quote-actions :quote="$quote" :show-replies="false" />

                        <button type="button" data-copy="{{ $quote->content }}{{ $byline ? ' — '.$byline : '' }}{{ $quote->source ? ' (المصدر: '.$quote->source.')' : '' }}"
                            aria-label="انسخ الاقتباس" title="انسخ الاقتباس"
                            class="inline-flex h-11 items-center gap-1.5 rounded-full px-3 text-sm text-muted transition hover:bg-accent-soft hover:text-accent">
                            <x-icon name="copy" class="size-[18px]" data-copy-icon />
                            <x-icon name="check" class="hidden size-[18px]" data-copied-icon />
                            <span data-copy-label>نسخ</span>
                        </button>

                        @can('update', $quote)
                            <x-button variant="ghost" :href="route('quotes.edit', $quote)" icon="palette">تصميم</x-button>
                            <form method="POST" action="{{ route('profile.pin', $quote) }}">
                                @csrf
                                <x-button type="submit" variant="ghost" icon="pin">
                                    {{ auth()->user()->pinned_quote_id === $quote->id ? 'مثبّت في ركني' : 'ثبّت في ركني' }}
                                </x-button>
                            </form>
                        @endcan
                    </div>
                </div>

                @if ($quote->source)
                    <p class="mx-auto mt-4 text-sm text-muted" style="max-width: {{ $ratioWidth }}px">
                        المصدر:
                        @if ($quote->sourceUrl())
                            {{-- A user-supplied link: no ranking credit, no referrer, no opener. --}}
                            <a href="{{ $quote->sourceUrl() }}" target="_blank" rel="nofollow ugc noopener noreferrer"
                                class="inline-flex items-center gap-1 text-accent hover:underline" dir="ltr">{{ \Illuminate\Support\Str::limit(rawurldecode(preg_replace('#^https?://(www\.)?#i', '', $quote->source)), 60) }}
                                <x-icon name="external" class="size-3.5" /></a>
                        @else
                            {{ $quote->source }}
                        @endif
                    </p>
                @endif
            </article>

            {{-- مختاراتي: add this quote to one of my anthologies, or start a new one with it. --}}
            @auth
                <section id="anthologies" class="mx-auto mt-8 scroll-mt-24 rounded-3xl border border-line bg-surface" style="max-width: {{ $ratioWidth }}px">
                    <div class="flex flex-wrap items-center gap-2 p-5">
                        <h2 class="me-2 flex items-center gap-2 font-medium"><x-icon name="layers" class="size-5 text-accent" /> أضف إلى مختاراتي</h2>
                        @foreach ($myAnthologies as $anthology)
                            <form method="POST" action="{{ route('anthologies.toggle', [$anthology, $quote]) }}">
                                @csrf
                                <button type="submit" aria-pressed="{{ $anthology->has_quote ? 'true' : 'false' }}"
                                    @class([
                                        'inline-flex h-9 items-center gap-1.5 rounded-full border px-4 text-sm transition',
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

                    <details class="group border-t border-line" @if ($errors->hasAny(['title', 'description', 'palette'])) open @endif>
                        <summary class="flex cursor-pointer list-none items-center gap-2 px-5 py-3 text-sm text-accent [&::-webkit-details-marker]:hidden">
                            <x-icon name="plus" class="size-4" /> مختارات جديدة بهذا الاقتباس
                        </summary>
                        @include('anthologies.partials.form', ['anthology' => new \App\Models\Anthology, 'action' => route('anthologies.store'), 'quoteId' => $quote->id])
                    </details>
                </section>
            @endauth

            @if ($inAnthologies->isNotEmpty())
                <p class="mx-auto mt-4 flex flex-wrap items-center gap-x-2 gap-y-1 text-sm text-muted" style="max-width: {{ $ratioWidth }}px">
                    <x-icon name="layers" class="size-4" /> في مختارات:
                    @foreach ($inAnthologies as $anthology)
                        <a href="{{ route('anthologies.show', $anthology) }}" class="text-accent hover:underline">«{{ $anthology->title }}» لـ{{ $anthology->user->name }}</a>@unless ($loop->last)<span>،</span>@endunless
                    @endforeach
                </p>
            @endif

            {{-- Story --}}
            @if ($quote->story)
                <section id="story" class="mx-auto mt-10 scroll-mt-24 rounded-3xl border border-line bg-surface p-6 sm:p-8" style="max-width: {{ $ratioWidth }}px">
                    <h2 class="font-display text-2xl font-bold">حكايـــة الاقتباس</h2>
                    <p class="mt-3 whitespace-pre-line font-text text-lg leading-loose">{{ $quote->story }}</p>
                </section>
            @endif

            {{-- Replies --}}
            <section id="replies" class="mx-auto mt-10 scroll-mt-24" style="max-width: {{ $ratioWidth }}px">
                <p class="mb-2 font-text text-sm text-gold">«إنَّ من البيانِ لسحراً»</p>
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
                            <span class="grid size-9 shrink-0 place-items-center rounded-full bg-accent-soft text-sm font-bold text-accent">
                                {{ mb_substr($reply->user->name, 0, 1) }}
                            </span>
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
