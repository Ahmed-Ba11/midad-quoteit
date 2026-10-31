@extends('layouts.app')

@section('title', 'مرسم الحروف')

@section('content')
    @php
        $isEditing = $quote->exists;
        $draft ??= [];
        $value = fn (string $field, $default) => old($field, $draft[$field] ?? $default);
        $design = \App\Support\CardDesign::normalize(old('design', $draft['design'] ?? $quote->cardDesign()));
        $tabs = [
            'background' => ['label' => 'الخلفية', 'icon' => 'drop'],
            'font' => ['label' => 'الخط', 'icon' => 'font'],
            'box' => ['label' => 'الصندوق', 'icon' => 'square'],
        ];
        $chip = 'inline-flex h-10 shrink-0 cursor-pointer items-center gap-1.5 rounded-full border border-line bg-paper px-4 text-sm transition hover:border-accent has-[:checked]:border-ink has-[:checked]:bg-ink has-[:checked]:text-paper has-[:focus-visible]:ring-3 has-[:focus-visible]:ring-accent/30';
        $row = 'flex flex-wrap gap-2';
        $label = 'mb-2 block text-xs font-medium text-muted';
        $toggle = 'flex cursor-pointer items-center justify-between gap-3 rounded-2xl border border-line bg-paper px-4 py-3 has-[:focus-visible]:ring-3 has-[:focus-visible]:ring-accent/30';
    @endphp

    <form method="POST" id="studio" novalidate
        action="{{ $isEditing ? route('edit_quote', $quote) : route('store_quote') }}"
        data-config="{{ json_encode([
            'fonts' => config('quotes.fonts'), 'ratios' => config('quotes.ratios'), 'palettes' => config('card.palettes'),
            'fontSize' => config('quotes.font_size'), 'writer' => auth()->user()->name,
        ]) }}"
        class="writing-desk relative">
        @csrf
        @if ($isEditing)
            @method('PUT')
        @endif
        <input type="hidden" name="_form" value="studio">
        <input type="hidden" name="presentation" value="card">
        <input type="hidden" name="font_size" value="{{ (int) $value('font_size', $quote->font_size) }}" data-font-size>
        <input type="hidden" name="texture" value="{{ $design['texture'] === 'none' ? 0 : 1 }}" data-legacy-texture>

        <div class="relative mx-auto grid max-w-6xl gap-5 px-4 pb-6 pt-3 lg:grid-cols-[minmax(0,1fr)_minmax(0,1.1fr)] lg:items-start">
            @include('quotes.partials.workbench-header', ['quote' => $quote, 'current' => 'card', 'title' => 'مرسم الحروف', 'backUrl' => $isEditing ? route('quotes.show', $quote) : $backUrl])

            {{-- The card. Its size follows the chosen shape but always fits the screen, so the tools stay in reach. --}}
            <section aria-label="معاينة البطاقة" class="lg:sticky lg:top-36 lg:order-2">
                <fieldset class="mb-3 flex items-center justify-center gap-1.5">
                    <legend class="sr-only">المقاس</legend>
                    @foreach (config('quotes.ratios') as $key => $ratio)
                        <label class="inline-flex h-9 cursor-pointer items-center gap-1.5 rounded-full border border-line bg-surface px-3 text-xs transition hover:border-accent has-[:checked]:border-ink has-[:checked]:bg-ink has-[:checked]:text-paper">
                            <input type="radio" name="ratio" value="{{ $key }}" class="sr-only" @checked($value('ratio', $quote->ratio) === $key)>
                            <span aria-hidden="true" class="inline-block w-2.5 rounded-[2px] border border-current" style="aspect-ratio: {{ $ratio['aspect'] }}"></span>
                            {{ $ratio['label'] }}
                        </label>
                    @endforeach
                </fieldset>
                <div data-preview-stage class="flex justify-center">
                    <div data-preview-frame class="preview-stage w-full">
                        <x-quote-art :quote="$quote" class="rounded-2xl shadow-xl" />
                    </div>
                </div>
            </section>

            {{-- Tools --}}
            <section class="min-w-0 space-y-4 lg:order-1">
                @include('quotes.partials.details', ['quote' => $quote, 'draft' => $draft, 'withStory' => true])

                <div class="rounded-3xl border border-line bg-surface">
                    <div role="tablist" aria-label="أدوات التصميم" class="grid grid-cols-3 border-b border-line px-1">
                        @foreach ($tabs as $key => $tab)
                            <button type="button" role="tab" id="tab-{{ $key }}" data-tab="{{ $key }}"
                                aria-controls="panel-{{ $key }}" aria-selected="{{ $loop->first ? 'true' : 'false' }}"
                                class="flex flex-col items-center gap-1 border-b-2 border-transparent py-3 text-xs text-muted transition hover:text-ink aria-selected:border-accent aria-selected:font-medium aria-selected:text-accent">
                                <x-icon :name="$tab['icon']" class="size-5" />
                                {{ $tab['label'] }}
                            </button>
                        @endforeach
                    </div>

                    <div class="space-y-5 p-4 sm:p-5">
                        {{-- الخلفية: colour, texture, dim, and the little divider under the words --}}
                        <div role="tabpanel" id="panel-background" data-panel="background" aria-labelledby="tab-background" class="space-y-5">
                            <fieldset>
                                <legend class="{{ $label }}">اللون</legend>
                                <div class="{{ $row }} items-center">
                                    @foreach (config('card.palettes') as $key => $palette)
                                        <label class="palette-dot" title="{{ $palette['name'] }}" style="--p-bg: {{ $palette['bg'] }}; --p-ink: {{ $palette['text'] }};">
                                            <input type="radio" name="design[palette]" value="{{ $key }}" class="sr-only" @checked($design['palette'] === $key)>
                                            <span class="sr-only">{{ $palette['name'] }}</span>
                                        </label>
                                    @endforeach
                                    <label class="swatch swatch-custom" title="لون من اختيارك">
                                        <input type="color" name="bg_color" value="{{ $value('bg_color', $quote->bg_color) }}" aria-label="لون الخلفية من اختيارك" class="swatch-picker">
                                    </label>
                                </div>
                                <p class="mt-1.5 text-xs text-muted">كل لون يأتي مع لون نصٍّ يُقرأ عليه بوضوح</p>
                            </fieldset>

                            <fieldset>
                                <legend class="{{ $label }}">الملمس</legend>
                                <div class="{{ $row }}">
                                    @foreach (config('card.textures') as $key => $texture)
                                        <label class="{{ $chip }}"><input type="radio" name="design[texture]" value="{{ $key }}" class="sr-only" @checked($design['texture'] === $key)>{{ $texture }}</label>
                                    @endforeach
                                </div>
                            </fieldset>

                            <div>
                                <label for="design-dim" class="{{ $label }} flex justify-between">التعتيم <output data-dim-output>{{ $design['dim'] }}٪</output></label>
                                <input type="range" id="design-dim" name="design[dim]" min="0" max="70" step="5" value="{{ $design['dim'] }}" class="w-full accent-[var(--color-accent)]">
                                <p class="mt-1 text-xs text-muted">يُظلّل الخلفية قليلاً</p>
                            </div>

                            <fieldset>
                                <legend class="{{ $label }}">فاصل تحت النص</legend>
                                <div class="{{ $row }}">
                                    @foreach (config('card.dividers') as $key => $divider)
                                        <label class="{{ $chip }}"><input type="radio" name="design[divider]" value="{{ $key }}" class="sr-only" @checked($design['divider'] === $key)>{{ $divider }}</label>
                                    @endforeach
                                </div>
                            </fieldset>
                        </div>

                        {{-- الخط: the pen, its size, colour and alignment, and emphasis --}}
                        <div role="tabpanel" id="panel-font" data-panel="font" aria-labelledby="tab-font" class="hidden space-y-5">
                            <x-font-picker :selected="$value('font', $quote->font)" />

                            <div class="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <span class="{{ $label }}">الحجم</span>
                                    <div role="group" aria-label="حجم الخط" class="flex gap-1.5">
                                        <button type="button" data-size-step="-1" class="{{ $chip }}" aria-label="أصغر">أصغر</button>
                                        <button type="button" data-size-auto class="{{ $chip }}">تلقائي</button>
                                        <button type="button" data-size-step="1" class="{{ $chip }}" aria-label="أكبر">أكبر</button>
                                    </div>
                                </div>
                                <fieldset>
                                    <legend class="{{ $label }}">المحاذاة</legend>
                                    <div class="flex gap-1.5">
                                        @foreach (config('card.aligns') as $key => $align)
                                            <label class="{{ $chip }}"><input type="radio" name="design[align]" value="{{ $key }}" class="sr-only" @checked($design['align'] === $key)>{{ $align }}</label>
                                        @endforeach
                                    </div>
                                </fieldset>
                            </div>

                            <div>
                                <span class="{{ $label }}">لون النص</span>
                                <div class="{{ $row }} items-center">
                                    @foreach (['#1E1A17' => 'حبر', '#F5EFE4' => 'ورقي', '#FFFFFF' => 'أبيض', '#8A3324' => 'قاني', '#A8873F' => 'ذهبي'] as $hex => $name)
                                        <button type="button" data-set="text_color" data-value="{{ $hex }}" class="swatch" style="--swatch: {{ $hex }}" title="{{ $name }}"><span class="sr-only">{{ $name }}</span></button>
                                    @endforeach
                                    <label class="swatch swatch-custom" title="لون من اختيارك">
                                        <input type="color" name="text_color" value="{{ $value('text_color', $quote->text_color) }}" aria-label="لون النص من اختيارك" class="swatch-picker">
                                    </label>
                                </div>
                            </div>

                            <fieldset>
                                <legend class="{{ $label }}">التنسيق</legend>
                                <div class="flex gap-1.5">
                                    @foreach (config('quotes.layouts') as $key => $layout)
                                        <label class="{{ $chip }}"><input type="radio" name="layout" value="{{ $key }}" class="sr-only" @checked($value('layout', $quote->layout) === $key)>{{ $layout['label'] }}</label>
                                    @endforeach
                                </div>
                            </fieldset>

                            <div class="space-y-3 rounded-2xl border border-line bg-paper p-3">
                                <p class="flex items-center gap-2 text-sm font-medium"><x-icon name="highlighter" class="size-4 text-accent" /> إبراز كلمات</p>
                                <div class="flex gap-1.5">
                                    @foreach (['mark' => 'تظليل', 'color' => 'تلوين'] as $key => $styleLabel)
                                        <label class="{{ $chip }}"><input type="radio" name="highlight_style" value="{{ $key }}" class="sr-only" @checked($value('highlight_style', $quote->highlight_style) === $key)>{{ $styleLabel }}</label>
                                    @endforeach
                                </div>
                                <div class="{{ $row }} items-center">
                                    @foreach (config('quotes.highlight_colors') as $hex => $name)
                                        <button type="button" data-set="highlight_color" data-value="{{ $hex }}" class="swatch" style="--swatch: {{ $hex }}" title="{{ $name }}"><span class="sr-only">{{ $name }}</span></button>
                                    @endforeach
                                    <label class="swatch swatch-custom" title="لون من اختيارك">
                                        <input type="color" name="highlight_color" value="{{ $value('highlight_color', $quote->highlight_color) }}" aria-label="لون الإبراز" class="swatch-picker">
                                    </label>
                                </div>
                                <div data-word-picker class="flex flex-wrap gap-1.5"></div>
                                <p class="text-xs text-muted">اضغط كلمة لإبرازها؛ الكلمات المتجاورة تُبرز قطعة واحدة</p>
                                <div data-highlight-inputs>
                                    @foreach (old('highlights', $draft['highlights'] ?? $quote->highlights ?? []) as $index)
                                        <input type="hidden" name="highlights[]" value="{{ $index }}">
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        {{-- الصندوق --}}
                        <div role="tabpanel" id="panel-box" data-panel="box" aria-labelledby="tab-box" class="hidden space-y-5">
                            <label class="{{ $toggle }}">
                                <span><span class="block text-sm font-medium">صندوق للنص</span><span class="text-xs text-muted">مربّع داخل البطاقة يحمل الكلمات</span></span>
                                <input type="hidden" name="design[box]" value="0">
                                <input type="checkbox" name="design[box]" value="1" class="size-5 accent-[var(--color-accent)]" @checked($design['box'])>
                            </label>
                            <div data-box-options class="space-y-5">
                                <div>
                                    <span class="{{ $label }}">لونه</span>
                                    <div class="{{ $row }} items-center">
                                        @foreach (['#FBF7EF' => 'ورقي', '#FFFFFF' => 'أبيض', '#C9A97A' => 'كرافت', '#B9A994' => 'توب', '#1E1A17' => 'حبر', '#23305A' => 'نيلي'] as $hex => $name)
                                            <button type="button" data-set="design[box_color]" data-value="{{ $hex }}" class="swatch" style="--swatch: {{ $hex }}" title="{{ $name }}"><span class="sr-only">{{ $name }}</span></button>
                                        @endforeach
                                        <label class="swatch swatch-custom" title="لون من اختيارك">
                                            <input type="color" name="design[box_color]" value="{{ $design['box_color'] }}" aria-label="لون الصندوق" class="swatch-picker">
                                        </label>
                                    </div>
                                </div>
                                <fieldset>
                                    <legend class="{{ $label }}">الحشوة</legend>
                                    <div class="flex gap-1.5">
                                        @foreach (config('card.box_paddings') as $key => $paddingLabel)
                                            <label class="{{ $chip }}"><input type="radio" name="design[box_padding]" value="{{ $key }}" class="sr-only" @checked($design['box_padding'] === $key)>{{ $paddingLabel }}</label>
                                        @endforeach
                                    </div>
                                </fieldset>
                                <fieldset>
                                    <legend class="{{ $label }}">الظل</legend>
                                    <div class="flex gap-1.5">
                                        @foreach (config('card.box_shadows') as $key => $shadowLabel)
                                            <label class="{{ $chip }}"><input type="radio" name="design[box_shadow]" value="{{ $key }}" class="sr-only" @checked($design['box_shadow'] === $key)>{{ $shadowLabel }}</label>
                                        @endforeach
                                    </div>
                                </fieldset>
                                <label class="{{ $toggle }}">
                                    <span class="text-sm font-medium">خط حول الصندوق</span>
                                    <input type="hidden" name="design[box_outline]" value="0">
                                    <input type="checkbox" name="design[box_outline]" value="1" class="size-5 accent-[var(--color-accent)]" @checked($design['box_outline'])>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        {{-- Publish: stays at the bottom of the screen while the form is in view (above the phone's tab bar). --}}
        <div class="sticky bottom-0 z-30 border-t border-line bg-surface/95 px-4 pb-[max(0.75rem,env(safe-area-inset-bottom))] pt-3">
            <div class="mx-auto flex max-w-6xl items-center gap-2">
                <x-button type="submit" icon="quill" class="h-12 flex-1 sm:flex-none sm:px-10">{{ $isEditing ? 'احفظ' : 'انشر البطاقة' }}</x-button>
                <x-button variant="ghost" :href="$isEditing ? route('quotes.show', $quote) : route('home')" class="h-12">إلغاء</x-button>
            </div>
        </div>
    </form>
@endsection
