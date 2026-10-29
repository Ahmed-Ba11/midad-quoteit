@extends('layouts.app')

@section('title', 'رواق الحرف')

@section('content')
    @php
        $isEditing = $quote->exists;
        $libraryVerses = $quote->relationLoaded('libraryVerses') ? $quote->getRelation('libraryVerses') : collect();
        $librarySaying = ! $isEditing && $quote->relationLoaded('saying') ? $quote->saying : null;
        $isLibrary = $libraryVerses->isNotEmpty() || $librarySaying || $quote->isFromLibrary();
        $fonts = config('quotes.fonts');
        $ratioWidth = config('quotes.ratios.'.$quote->ratio.'.width', 760);
        $tabs = [
            'words' => ['label' => 'الكلمات', 'icon' => 'type'],
            'font' => ['label' => 'الخط', 'icon' => 'font'],
            'color' => ['label' => 'اللون', 'icon' => 'palette'],
            'highlight' => ['label' => 'الإبراز', 'icon' => 'highlighter'],
            'story' => ['label' => 'الحكاية', 'icon' => 'book-open'],
        ];
        $optionCard = 'flex cursor-pointer items-center justify-center rounded-xl border border-line bg-paper p-3 text-center transition hover:border-accent has-[:checked]:border-accent has-[:checked]:bg-accent-soft has-[:checked]:text-accent has-[:focus-visible]:ring-3 has-[:focus-visible]:ring-accent/30';
        $sectionTitle = 'mb-3 text-sm font-medium';
    @endphp

    <form method="POST" id="studio" novalidate
        action="{{ $isEditing ? route('edit_quote', $quote) : route('store_quote') }}"
        data-config="{{ json_encode(['fonts' => $fonts, 'ratios' => config('quotes.ratios'), 'palettes' => config('quotes.palettes')]) }}"
        class="mx-auto grid max-w-7xl gap-6 px-4 py-6 lg:h-[calc(100dvh-4rem)] lg:grid-cols-[440px_1fr]">
        @csrf
        @if ($isEditing)
            @method('PUT')
        @endif
        <input type="hidden" name="_form" value="studio">

        {{-- Tools panel --}}
        <section class="flex flex-col overflow-hidden rounded-3xl border border-line bg-surface lg:min-h-0">
            <header class="px-6 pt-5">
                <h1 class="font-display text-2xl font-bold">رواق الحـــرف</h1>
                <p class="mt-1 text-sm text-muted">{{ $isEditing ? 'أعد تصميم اقتباسك.' : 'اكتب، ثم اختر الخط واللون، وانشر.' }}</p>
                <p class="mt-2 font-text text-xs leading-relaxed text-gold">وجلا السيولُ عن الطلولِ كأنَّها ◆ زُبُرٌ تُجِدُّ متونَها أقلامُها — لبيد</p>
            </header>

            <div role="tablist" aria-label="أدوات التصميم" class="mt-4 grid grid-cols-5 border-b border-line px-2">
                @foreach ($tabs as $key => $tab)
                    <button type="button" role="tab" id="tab-{{ $key }}" data-tab="{{ $key }}"
                        aria-controls="panel-{{ $key }}" aria-selected="{{ $loop->first ? 'true' : 'false' }}"
                        class="flex flex-col items-center gap-1 border-b-2 border-transparent py-2.5 text-xs text-muted transition hover:text-ink aria-selected:border-accent aria-selected:text-accent">
                        <x-icon :name="$tab['icon']" />
                        {{ $tab['label'] }}
                    </button>
                @endforeach
            </div>

            <div class="flex-1 space-y-6 overflow-y-auto p-6">
                @if ($errors->any())
                    <div role="alert" class="rounded-xl border border-accent/40 bg-accent-soft p-3 text-sm text-accent">
                        راجع الحقول: {{ $errors->first() }}
                    </div>
                @endif

                {{-- الكلمات --}}
                <div role="tabpanel" id="panel-words" data-panel="words" aria-labelledby="tab-words" class="space-y-5">
                    @if ($isLibrary)
                        {{-- Library text is locked: it is published exactly as it is in the diwan. --}}
                        <div class="rounded-2xl border border-gold/40 bg-paper p-5">
                            <p class="flex items-center gap-2 text-xs font-medium text-gold">
                                <x-ornament name="star" class="size-3.5" /> من المكتبة · يُنشر كما هو
                            </p>
                            <p class="mt-3 whitespace-pre-line text-center font-text text-lg leading-loose">{{ $quote->content }}</p>
                            <p class="mt-2 text-center text-sm text-muted">— {{ collect([$quote->author, $quote->book])->filter()->implode('، ') }}</p>

                            @if (! $isEditing)
                                <div class="mt-4 flex flex-wrap justify-center gap-2 text-sm">
                                    <a href="{{ route('library.index') }}#shelf" class="rounded-full border border-line px-4 py-2 transition hover:border-accent hover:text-accent">اختر بيتاً آخر</a>
                                    <a href="{{ route('quotes.create') }}" class="rounded-full border border-line px-4 py-2 transition hover:border-accent hover:text-accent">اكتب نصك الخاص</a>
                                </div>
                            @endif
                        </div>

                        <textarea name="content" hidden readonly>{{ $quote->content }}</textarea>
                        <input type="hidden" name="author" value="{{ $quote->author }}">
                        <input type="hidden" name="book" value="{{ $quote->book }}">
                        @foreach ($libraryVerses as $verse)
                            <input type="hidden" name="verses[]" value="{{ $verse->id }}">
                        @endforeach
                        @if ($librarySaying)
                            <input type="hidden" name="saying" value="{{ $librarySaying->id }}">
                        @endif
                    @else
                        <x-textarea name="content" label="نص الاقتباس" hint="من ٣ إلى ١٥٥ حرفاً" rows="4" minlength="3"
                            maxlength="155" required :value="old('content', $quote->content)" />

                        <x-input name="author" label="القائل" hint="اختياري، حتى ١٠٠ حرف" maxlength="100"
                            :value="$quote->author" />

                        <x-input name="book" label="الكتاب أو الديوان" hint="اختياري، حتى ١٥٠ حرفاً" maxlength="150"
                            :value="$quote->book" />
                    @endif

                    <div>
                        <label for="category_id" class="mb-1.5 block text-sm font-medium">التصنيف</label>
                        <select id="category_id" name="category_id"
                            class="block h-12 w-full rounded-xl border border-line bg-white px-4 text-ink focus:border-accent focus:outline-none focus:ring-3 focus:ring-accent/20 dark:bg-surface">
                            <option value="">بدون تصنيف</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" @selected((int) old('category_id', $quote->category_id) === $category->id)>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        <p class="mt-1.5 text-xs text-muted">اختياري، يساعد القرّاء على التصفية</p>
                    </div>
                </div>

                {{-- الخط --}}
                <div role="tabpanel" id="panel-font" data-panel="font" aria-labelledby="tab-font" class="hidden space-y-6">
                    @foreach (['ar' => 'خطوط عربية', 'en' => 'خطوط إنجليزية'] as $lang => $title)
                        <fieldset>
                            <legend class="{{ $sectionTitle }}">{{ $title }}</legend>
                            <div class="grid grid-cols-2 gap-2 sm:grid-cols-3">
                                @foreach (collect($fonts)->where('lang', $lang) as $key => $font)
                                    <label class="{{ $optionCard }} flex-col gap-1">
                                        <input type="radio" name="font" value="{{ $key }}" class="sr-only"
                                            @checked(old('font', $quote->font) === $key)>
                                        <span class="text-xl" style="font-family: {{ $font['stack'] }}; {{ ($font['italic'] ?? false) ? 'font-style: italic;' : '' }}">{{ $lang === 'ar' ? 'حِبر' : 'Ink' }}</span>
                                        <span class="text-xs text-muted">{{ $font['label'] }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </fieldset>
                    @endforeach
                    <p class="-mt-3 text-xs text-muted">خط واحد للاقتباس كله؛ الخطوط الإنجليزية للنصوص الإنجليزية</p>

                    <div>
                        <label for="font_size" class="{{ $sectionTitle }} flex justify-between">
                            حجم الخط <output data-output="font_size" class="text-muted">{{ old('font_size', $quote->font_size) }}</output>
                        </label>
                        <input type="range" id="font_size" name="font_size" class="w-full accent-[var(--color-accent)]"
                            min="{{ config('quotes.font_size.min') }}" max="{{ config('quotes.font_size.max') }}"
                            value="{{ old('font_size', $quote->font_size) }}">
                        <p class="mt-1.5 text-xs text-muted">من ٢٠ إلى ٥٦</p>
                    </div>

                    <fieldset>
                        <legend class="{{ $sectionTitle }}">المقاس</legend>
                        <div class="grid grid-cols-3 gap-2">
                            @foreach (config('quotes.ratios') as $key => $ratio)
                                <label class="{{ $optionCard }} flex-col gap-2">
                                    <input type="radio" name="ratio" value="{{ $key }}" class="sr-only"
                                        @checked(old('ratio', $quote->ratio) === $key)>
                                    <span class="w-8 rounded border-2 border-current" style="aspect-ratio: {{ $ratio['aspect'] }}"></span>
                                    <span class="text-xs">{{ $ratio['label'] }}</span>
                                </label>
                            @endforeach
                        </div>
                        <p class="mt-1.5 text-xs text-muted">عريض ١٦:٩، مربّع ١:١، طولي ٤:٥</p>
                    </fieldset>
                </div>

                {{-- اللون --}}
                <div role="tabpanel" id="panel-color" data-panel="color" aria-labelledby="tab-color" class="hidden space-y-6">
                    <fieldset>
                        <legend class="{{ $sectionTitle }}">لوحات جاهزة</legend>
                        <div class="grid grid-cols-3 gap-2">
                            @foreach (config('quotes.palettes') as $key => $palette)
                                <button type="button" data-palette="{{ $key }}"
                                    class="flex flex-col items-center gap-2 rounded-xl border border-line bg-paper p-3 transition hover:border-accent">
                                    <span class="flex h-8 w-full items-center justify-center rounded-lg text-sm font-bold"
                                        style="background: {{ $palette['bg'] }}; color: {{ $palette['text'] }}">
                                        أ<span style="color: {{ $palette['highlight'] }}">ب</span>
                                    </span>
                                    <span class="text-xs">{{ $palette['name'] }}</span>
                                </button>
                            @endforeach
                        </div>
                    </fieldset>

                    <fieldset>
                        <legend class="{{ $sectionTitle }}">ألوان مخصّصة</legend>
                        <div class="grid grid-cols-3 gap-3">
                            @foreach (['bg_color' => 'البطاقة', 'text_color' => 'النص', 'highlight_color' => 'الإبراز'] as $field => $label)
                                <label class="flex flex-col items-center gap-1.5 text-xs">
                                    <input type="color" name="{{ $field }}" value="{{ old($field, $quote->$field) }}"
                                        class="h-11 w-full cursor-pointer rounded-xl border border-line bg-paper p-1">
                                    {{ $label }}
                                </label>
                            @endforeach
                        </div>
                        <p class="mt-1.5 text-xs text-muted">اختر لوحة جاهزة أو عدّل كل لون على حدة</p>
                    </fieldset>

                    <label class="flex cursor-pointer items-center justify-between rounded-xl border border-line bg-paper p-4">
                        <span>
                            <span class="block text-sm font-medium">ملمس الورق</span>
                            <span class="text-xs text-muted">حبيبات خفيفة كورق قديم</span>
                        </span>
                        <input type="hidden" name="texture" value="0">
                        <input type="checkbox" name="texture" value="1" class="size-5 accent-[var(--color-accent)]"
                            @checked(old('texture', $quote->texture))>
                    </label>
                </div>

                {{-- الإبراز --}}
                <div role="tabpanel" id="panel-highlight" data-panel="highlight" aria-labelledby="tab-highlight" class="hidden space-y-6">
                    <fieldset>
                        <legend class="{{ $sectionTitle }}">نوع الإبراز</legend>
                        <div class="grid grid-cols-2 gap-2">
                            @foreach (['mark' => 'تظليل', 'color' => 'تلوين'] as $key => $label)
                                <label class="{{ $optionCard }}">
                                    <input type="radio" name="highlight_style" value="{{ $key }}" class="sr-only"
                                        @checked(old('highlight_style', $quote->highlight_style) === $key)>
                                    {{ $label }}
                                </label>
                            @endforeach
                        </div>
                        <p class="mt-1.5 text-xs text-muted">التظليل يلوّن خلفية الكلمة، والتلوين يغيّر لون حروفها</p>
                    </fieldset>

                    <div>
                        <p class="{{ $sectionTitle }}">اختر الكلمات</p>
                        <div data-word-picker class="flex flex-wrap gap-1.5"></div>
                        <p class="mt-2 text-xs text-muted">اضغط كلمة لإبرازها؛ الكلمات المتجاورة تُبرز كقطعة واحدة</p>
                        <div data-highlight-inputs>
                            @foreach (old('highlights', $quote->highlights ?? []) as $index)
                                <input type="hidden" name="highlights[]" value="{{ $index }}">
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- الحكاية --}}
                <div role="tabpanel" id="panel-story" data-panel="story" aria-labelledby="tab-story" class="hidden">
                    <x-textarea name="story" label="حكاية الاقتباس" hint="اختياري، حتى ٥٠٠ حرف: أين قرأته؟ ولماذا بقي معك؟"
                        rows="8" maxlength="500" :value="old('story', $quote->story)" />
                </div>
            </div>

            <footer class="flex gap-2 border-t border-line p-4">
                <x-button type="submit" class="flex-1">{{ $isEditing ? 'احفظ التصميم' : 'انشر الاقتباس' }}</x-button>
                <x-button variant="ghost" :href="route('home')">إلغاء</x-button>
            </footer>
        </section>

        {{-- Live preview --}}
        <section aria-label="معاينة" class="flex flex-col items-center gap-6 overflow-y-auto rounded-3xl border border-dashed border-line p-4 sm:p-8 lg:min-h-0 lg:justify-center">
            <div data-preview-frame class="w-full shadow-xl" style="max-width: {{ $ratioWidth }}px">
                <x-quote-art :quote="$quote" class="rounded-2xl" />
            </div>

            <div data-story-preview @class(['w-full max-w-xl rounded-2xl border border-line bg-surface p-6', 'hidden' => blank($quote->story)])>
                <h2 class="font-display text-lg font-bold">حكايـــة الاقتباس</h2>
                <p data-story-text class="mt-2 whitespace-pre-line font-text leading-loose text-muted">{{ $quote->story }}</p>
            </div>
        </section>
    </form>
@endsection
