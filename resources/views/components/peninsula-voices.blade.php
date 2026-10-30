{{--
    أصوات من الجزيرة: one or two famous lines from modern Saudi poets (config/library.php → peninsula_voices),
    quoted for citation with name and poem, and a door to read the whole poem on its own site — then come
    back and quote. Their rights stay with them; nothing more than these lines is reproduced.
--}}
<section aria-labelledby="voices-title" {{ $attributes->class('rounded-3xl border border-line bg-surface p-6 sm:p-10') }}>
    <header class="text-center">
        <p class="text-sm text-gold">من الجزيرة العربية اليوم</p>
        <h3 id="voices-title" class="mt-1 font-display text-3xl font-bold">أصواتٌ من الجزيرة</h3>
        <p class="mx-auto mt-2 max-w-xl text-sm text-muted">بيتٌ مشهور لكل شاعر، وطريقٌ إلى قصيدته كاملةً في موقعها؛ اقرأها هناك، ثم عُد لتقتبس ما بقي معك.</p>
    </header>

    <ul class="mt-8 grid gap-5 md:grid-cols-2">
        @foreach (config('library.peninsula_voices') as $key => $voice)
            <li class="flex flex-col rounded-2xl border border-line bg-paper p-5 sm:p-6">
                <div class="flex items-baseline justify-between gap-3">
                    <p class="font-display text-xl font-bold">{{ $voice['poet'] }}</p>
                    <p class="shrink-0 text-xs text-muted">{{ $voice['years'] }}</p>
                </div>
                <p class="mt-1 text-sm text-muted">{{ $voice['about'] }}</p>

                <div class="mt-5 space-y-5">
                    @foreach ($voice['verses'] as $index => [$sadr, $ajuz])
                        <div>
                            <x-verse :sadr="$sadr" :ajuz="$ajuz" class="text-lg [&_blockquote_span]:my-1.5" />
                            @auth
                                <a href="{{ route('quotes.create', ['voice' => $key, 'line' => $index]) }}"
                                    class="mx-auto mt-2 flex min-h-11 w-fit items-center gap-1.5 rounded-full px-3 text-sm text-accent transition hover:bg-accent-soft">
                                    <x-icon name="quill" class="size-4" /> اقتبس هذا البيت
                                </a>
                            @endauth
                        </div>
                    @endforeach
                </div>

                <div class="min-h-4 flex-1"></div>
                <div class="mt-4 flex flex-wrap items-center justify-between gap-2 border-t border-line pt-4">
                    <span class="text-xs text-muted">من «{{ $voice['poem'] }}»</span>
                    <a href="{{ $voice['read_more'] }}" target="_blank" rel="noopener noreferrer external"
                        class="inline-flex min-h-11 items-center gap-1.5 rounded-full bg-accent px-4 text-sm font-medium text-paper transition hover:opacity-90">
                        أكمل قراءتك في الديوان <x-icon name="external" class="size-3.5" /><span class="sr-only">(موقع خارجي)</span>
                    </a>
                </div>
            </li>
        @endforeach
    </ul>

    <p class="mt-6 text-center text-xs leading-relaxed text-muted">
        أبيات مقتبسة للاستشهاد مع ذكر قائلها ومصدرها (نظام حماية حقوق المؤلف، المادة ١٥)؛ وكل الحقوق محفوظة لأصحابها.
        طابقنا كل بيت حرفياً في مصدرين مستقلّين على الأقل.
    </p>
</section>
