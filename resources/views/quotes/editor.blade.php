@extends('layouts.app')

@section('title', 'محرّر النص')

@section('content')
    @php
        $isEditing = $quote->exists;
        $isLibrary = $quote->relationLoaded('libraryVerses') && $quote->getRelation('libraryVerses')->isNotEmpty()
            || (! $isEditing && $quote->relationLoaded('saying') && $quote->saying) || $quote->isFromLibrary();
        $tabs = [
            'layout' => ['label' => 'التنسيق', 'icon' => 'align'],
            'font' => ['label' => 'الخط', 'icon' => 'font'],
            'emphasis' => ['label' => 'الإبراز', 'icon' => 'highlighter'],
            'mark' => ['label' => 'العلامة', 'icon' => 'quote'],
            'story' => ['label' => 'الحكاية', 'icon' => 'book-open'],
        ];
        $value = fn (string $field, $default) => old($field, $draft[$field] ?? $default);
        $highlightColor = strtoupper((string) $value('highlight_color', $quote->highlight_color));
        $isCustomColor = ! array_key_exists($highlightColor, config('quotes.highlight_colors'));
        $card = 'flex cursor-pointer flex-col gap-1 rounded-2xl border border-line bg-paper p-3 transition hover:border-accent has-[:checked]:border-accent has-[:checked]:bg-accent-soft has-[:focus-visible]:ring-3 has-[:focus-visible]:ring-accent/30';
        $sectionTitle = 'mb-2 text-sm font-medium';
        // To the studio with everything kept: a draft goes back through QuoteController@draft; a library
        // pick or a saved quote simply reopens as a card (their words are fixed or already stored).
        $toStudio = $isEditing ? route('quotes.edit', [$quote, 'as' => 'card']) : ($isLibrary ? request()->fullUrlWithQuery(['as' => 'card']) : null);
    @endphp

    <form method="POST" id="text-editor" novalidate
        action="{{ $isEditing ? route('edit_quote', $quote) : route('store_quote') }}"
        data-config="{{ json_encode(['fonts' => config('quotes.fonts'), 'marks' => config('quotes.marks'), 'writer' => auth()->user()->name]) }}"
        class="writing-desk relative">
        @csrf
        @if ($isEditing)
            @method('PUT')
        @endif
        <input type="hidden" name="_form" value="editor">
        <input type="hidden" name="presentation" value="text">

        <div class="relative mx-auto grid max-w-6xl gap-6 px-4 pb-32 pt-6 lg:grid-cols-[minmax(0,1fr)_minmax(0,1.15fr)] lg:items-start lg:pb-12">
            <header class="order-0 lg:col-span-2">
                <x-back :href="$isEditing ? route('quotes.show', $quote) : $backUrl" label="رجوع" class="!mb-3" />
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h1 class="font-display text-2xl font-bold sm:text-3xl">محرّر النـــص</h1>
                        <p class="mt-1 hidden text-sm text-muted sm:block">اختر الخط والتنسيق، وما تراه في المعاينة هو ما يُنشر.</p>
                    </div>

                    {{-- Text or card: the same quote, two ways to show it. --}}
                    <div role="group" aria-label="طريقة العرض" class="flex rounded-full border border-line bg-surface p-1 text-sm">
                        <span aria-current="true" class="inline-flex h-9 items-center gap-1.5 rounded-full bg-ink px-4 text-paper"><x-icon name="type" class="size-4" /> نص</span>
                        @if ($toStudio)
                            <a href="{{ $toStudio }}" class="inline-flex h-9 items-center gap-1.5 rounded-full px-4 text-muted transition hover:text-accent"><x-icon name="palette" class="size-4" /> بطاقة</a>
                        @else
                            <button type="submit" formaction="{{ route('quotes.draft') }}" name="presentation" value="card"
                                class="inline-flex h-9 items-center gap-1.5 rounded-full px-4 text-muted transition hover:text-accent"><x-icon name="palette" class="size-4" /> بطاقة</button>
                        @endif
                    </div>
                </div>
                <x-epigraph context="writing" class="mt-3 hidden !text-xs sm:block" />
            </header>

            {{-- Tools --}}
            <section class="order-2 min-w-0 space-y-5 lg:order-1">
                @include('quotes.partials.details', ['quote' => $quote, 'draft' => $draft ?? []])

                <div class="rounded-3xl border border-line bg-surface">
                    <div role="tablist" aria-label="أدوات التنسيق" class="grid grid-cols-5 border-b border-line px-1">
                        @foreach ($tabs as $key => $tab)
                            <button type="button" role="tab" id="tab-{{ $key }}" data-tab="{{ $key }}"
                                aria-controls="panel-{{ $key }}" aria-selected="{{ $loop->first ? 'true' : 'false' }}"
                                class="flex flex-col items-center gap-1 border-b-2 border-transparent py-3 text-xs text-muted transition hover:text-ink aria-selected:border-accent aria-selected:font-medium aria-selected:text-accent">
                                <x-icon :name="$tab['icon']" class="size-5" />
                                {{ $tab['label'] }}
                            </button>
                        @endforeach
                    </div>

                    <div class="p-5">
                        {{-- التنسيق: verse, prose, or let رقيم tell --}}
                        <div role="tabpanel" id="panel-layout" data-panel="layout" aria-labelledby="tab-layout" class="space-y-4">
                            <fieldset>
                                <legend class="{{ $sectionTitle }}">كيف يُكتب النص؟</legend>
                                <div class="grid grid-cols-3 gap-2">
                                    @foreach (config('quotes.layouts') as $key => $layout)
                                        <label class="{{ $card }} text-center">
                                            <input type="radio" name="layout" value="{{ $key }}" class="sr-only" @checked($value('layout', $quote->layout) === $key)>
                                            <x-layout-glyph :layout="$key" class="mx-auto h-8 w-14 text-accent" />
                                            <span class="text-sm font-medium">{{ $layout['label'] }}</span>
                                            <span class="text-xs leading-snug text-muted">{{ $layout['hint'] }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </fieldset>
                            <p data-layout-hint aria-live="polite" class="flex min-h-9 items-center gap-2 rounded-xl bg-paper px-3 text-sm text-muted"></p>
                            <p class="text-xs leading-relaxed text-muted">للبيت: اكتب الصدر في سطر والعجز في سطر، أو افصل بينهما بـ * أو … أو مسافتين؛ ويُرتَّب كما في الدواوين.</p>
                        </div>

                        {{-- الخط --}}
                        <div role="tabpanel" id="panel-font" data-panel="font" aria-labelledby="tab-font" class="hidden">
                            <x-font-picker :selected="$value('font', $quote->font)" />
                        </div>

                        {{-- الإبراز: shading or coloured letters, in one of twelve colours or your own --}}
                        <div role="tabpanel" id="panel-emphasis" data-panel="emphasis" aria-labelledby="tab-emphasis" class="hidden space-y-5">
                            <fieldset>
                                <legend class="{{ $sectionTitle }}">نوع الإبراز</legend>
                                <div class="grid grid-cols-2 gap-2">
                                    @foreach (['mark' => ['تظليل', 'خلفية ناعمة خلف الكلمات'], 'color' => ['تلوين', 'يتلوّن الحرف نفسه']] as $key => [$label, $hint])
                                        <label class="{{ $card }}">
                                            <input type="radio" name="highlight_style" value="{{ $key }}" class="sr-only" @checked($value('highlight_style', $quote->highlight_style) === $key)>
                                            <span class="text-sm font-medium">{{ $label }}</span>
                                            <span class="text-xs text-muted">{{ $hint }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </fieldset>

                            <fieldset>
                                <legend class="{{ $sectionTitle }}">اللون</legend>
                                <div class="flex flex-wrap gap-2.5">
                                    @foreach (config('quotes.highlight_colors') as $hex => $label)
                                        <label class="swatch" title="{{ $label }}" style="--swatch: {{ $hex }}">
                                            <input type="radio" name="highlight_color" value="{{ $hex }}" class="sr-only" @checked($highlightColor === $hex)>
                                            <span class="sr-only">{{ $label }}</span>
                                        </label>
                                    @endforeach
                                    <label class="swatch swatch-custom" title="لون من اختيارك">
                                        <input type="radio" name="highlight_color" value="{{ $isCustomColor ? $highlightColor : '#2B6CB0' }}" class="sr-only" data-custom-swatch @checked($isCustomColor)>
                                        <input type="color" data-custom-color value="{{ $isCustomColor ? $highlightColor : '#2B6CB0' }}" aria-label="لون من اختيارك" class="swatch-picker">
                                    </label>
                                </div>
                            </fieldset>

                            <div>
                                <p class="{{ $sectionTitle }}">الكلمات</p>
                                <div data-word-picker class="flex flex-wrap gap-1.5"></div>
                                <p class="mt-2 text-xs text-muted">اضغط كلمة لإبرازها؛ الكلمات المتجاورة تُبرز قطعة واحدة</p>
                                <div data-highlight-inputs>
                                    @foreach (old('highlights', $draft['highlights'] ?? $quote->highlights ?? []) as $index)
                                        <input type="hidden" name="highlights[]" value="{{ $index }}">
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        {{-- العلامة: the frame around the words, seen on your own text in the preview --}}
                        <div role="tabpanel" id="panel-mark" data-panel="mark" aria-labelledby="tab-mark" class="hidden">
                            <fieldset>
                                <legend class="{{ $sectionTitle }}">علامة الاقتباس</legend>
                                <div class="grid grid-cols-2 gap-2 sm:grid-cols-4">
                                    @foreach (config('quotes.marks') as $key => $mark)
                                        <label class="{{ $card }} items-center text-center">
                                            <input type="radio" name="mark_style" value="{{ $key }}" class="sr-only" @checked($value('mark_style', $quote->mark_style) === $key)>
                                            <x-quote-mark :style="$key" class="h-7 text-gold" />
                                            <span class="text-xs">{{ $mark['label'] }}</span>
                                        </label>
                                    @endforeach
                                </div>
                                <p class="mt-2 text-xs text-muted">جرّب كل علامة، وانظر إليها على نصّك في المعاينة.</p>
                            </fieldset>
                        </div>

                        {{-- الحكاية --}}
                        <div role="tabpanel" id="panel-story" data-panel="story" aria-labelledby="tab-story" class="hidden">
                            <x-textarea name="story" label="حكاية الاقتباس" hint="اختياري، حتى ٥٠٠ حرف: أين قرأته؟ ولماذا بقي معك؟"
                                rows="6" maxlength="500" :value="old('story', $draft['story'] ?? $quote->story)" />
                        </div>
                    </div>
                </div>
            </section>

            {{-- Live preview: on phones it stays at the top while the tools scroll under it. --}}
            <section aria-label="معاينة" class="sticky top-16 z-10 order-1 -mx-4 bg-paper/95 px-4 pb-3 pt-2 lg:top-20 lg:order-2 lg:mx-0 lg:bg-transparent lg:p-0">
                <div class="preview-stage">
                    <x-quote-art :quote="$quote" class="max-h-[38dvh] overflow-y-auto rounded-3xl shadow-xl lg:max-h-none" />
                </div>
                <p class="mt-2 hidden text-center text-xs text-muted lg:block">المعاينة تتغيّر مع كل اختيار.</p>
            </section>
        </div>

        {{-- Publish: always in reach (above the phone's tab bar). --}}
        <div class="fixed inset-x-0 bottom-16 z-30 border-t border-line bg-surface px-4 py-3 md:bottom-0 lg:static lg:border-0 lg:bg-transparent lg:pb-10 lg:pt-0">
            <div class="mx-auto flex max-w-6xl items-center gap-2">
                <x-button type="submit" icon="quill" class="h-12 flex-1 sm:flex-none sm:px-10">{{ $isEditing ? 'احفظ' : 'انشر الاقتباس' }}</x-button>
                <x-button variant="ghost" :href="$isEditing ? route('quotes.show', $quote) : route('home')" class="h-12">إلغاء</x-button>
            </div>
        </div>
    </form>
@endsection
