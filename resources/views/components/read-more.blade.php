@props(['poet', 'work' => null])

{{--
    "أكمل القراءة": the library holds selected verses; the full diwan lives on other sites.
    Wikisource is where our text comes from (CC BY-SA); الديوان is linked only, never copied.
--}}
@php
    $wikisourceUrl = $work?->wikisource_url ?? $poet->wikisource_url;
@endphp

@if ($wikisourceUrl || $poet->aldiwan_url)
    <div {{ $attributes->class('rounded-2xl border border-gold/40 bg-paper p-5') }}>
        <p class="flex items-center gap-2 font-display text-lg font-bold">
            <x-icon name="book-open" class="size-5 text-gold" />
            {{ $work?->type === 'muallaqa' ? 'اقرأ المعلقة كاملة' : 'أكمل قراءة الديوان' }}
        </p>
        <p class="mt-1 text-sm text-muted">
            في المكتبة عيون الأبيات فقط؛ والنص الكامل لدى المصادر التالية، ويُفتح في موقع خارجي.
        </p>
        <div class="mt-4 flex flex-wrap gap-2">
            @if ($wikisourceUrl)
                <x-external-link :href="$wikisourceUrl"
                    class="h-10 rounded-full border border-line bg-surface px-4 text-sm no-underline hover:border-accent hover:text-accent hover:no-underline">
                    ويكي مصدر
                </x-external-link>
            @endif
            @if ($poet->aldiwan_url)
                <x-external-link :href="$poet->aldiwan_url"
                    class="h-10 rounded-full border border-line bg-surface px-4 text-sm no-underline hover:border-accent hover:text-accent hover:no-underline">
                    شعر {{ $poet->name }} في الديوان
                </x-external-link>
            @endif
        </div>
    </div>
@endif
