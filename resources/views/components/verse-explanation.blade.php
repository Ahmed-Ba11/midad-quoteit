@props(['verse'])

{{--
    شرح البيت: a plain meaning and the difficult words (written for رقيم), with every word one click
    away from لسان العرب on Wikisource (CC BY-SA) so the reader can check it. Nothing is fetched at runtime.
--}}
@php
    $lisanSearch = fn (string $word) => 'https://ar.wikisource.org/w/index.php?'.http_build_query([
        'search' => $word.' prefix:لسان العرب/',
        'fulltext' => 1,
    ]);
@endphp

<details {{ $attributes->class('group rounded-2xl border border-line/80 bg-paper/60 text-start open:bg-paper') }}>
    <summary class="flex cursor-pointer list-none items-center justify-center gap-2 px-4 py-2.5 text-sm text-muted transition hover:text-accent [&::-webkit-details-marker]:hidden">
        <x-icon name="book-open" class="size-4" />
        <span class="group-open:hidden">شرح البيت ومعاني كلماته</span>
        <span class="hidden group-open:inline">إخفاء الشرح</span>
    </summary>

    <div class="space-y-5 border-t border-line/70 px-5 pb-5 pt-4 text-sm leading-loose">
        @if ($verse->explanation)
            <section>
                <h4 class="mb-1 font-medium text-ink">المعنى</h4>
                <p class="text-ink/85">{{ $verse->explanation }}</p>
                <p class="mt-1 text-xs text-muted">شرح مبسّط من رقيم؛ وللتوسّع ابحث عن أي كلمة في لسان العرب أدناه.</p>
            </section>
        @endif

        @if (! empty($verse->glossary))
            <section>
                <h4 class="mb-2 font-medium text-ink">غريب الألفاظ</h4>
                <dl class="grid gap-x-6 gap-y-2 sm:grid-cols-2">
                    @foreach ($verse->glossary as $entry)
                        <div class="flex items-baseline gap-2">
                            <dt class="shrink-0 font-text font-bold text-accent">{{ $entry['word'] }}</dt>
                            <dd class="text-ink/85">
                                {{ $entry['meaning'] }}
                                <x-external-link :href="$lisanSearch($entry['word'])" class="text-xs text-muted">لسان العرب</x-external-link>
                            </dd>
                        </div>
                    @endforeach
                </dl>
            </section>
        @endif

        <section>
            <h4 class="mb-2 font-medium text-ink">ابحث عن أي كلمة في لسان العرب</h4>
            <div class="flex flex-wrap gap-1.5">
                @foreach ($verse->lookupWords() as $word)
                    <a href="{{ $lisanSearch($word) }}" target="_blank" rel="noopener noreferrer external"
                        class="rounded-lg border border-line bg-surface px-2.5 py-1 font-text transition hover:border-accent hover:text-accent">
                        {{ $word }}<span class="sr-only"> (يفتح لسان العرب في موقع خارجي)</span>
                    </a>
                @endforeach
            </div>
        </section>
    </div>
</details>
