@extends('layouts.app')

@section('title', 'مرسم الحروف')

@section('content')
    @php
        $isEditing = $quote->exists;
        $isLibrary = $quote->relationLoaded('libraryVerses') && $quote->getRelation('libraryVerses')->isNotEmpty()
            || (! $isEditing && $quote->relationLoaded('saying') && $quote->saying) || $quote->isFromLibrary();
        $draft ??= [];
        $value = fn (string $field, $default) => old($field, $draft[$field] ?? $default);
        $design = \App\Support\CardDesign::normalize(old('design', $draft['design'] ?? $quote->cardDesign()));
        $designValue = fn (string $key) => $design[$key];
        $tabs = [
            'templates' => ['label' => 'قوالب', 'icon' => 'sparkle'],
            'background' => ['label' => 'الخلفية', 'icon' => 'image'],
            'font' => ['label' => 'الخط', 'icon' => 'font'],
            'box' => ['label' => 'الصندوق', 'icon' => 'square'],
            'frame' => ['label' => 'الإطار', 'icon' => 'frame'],
            'gift' => ['label' => 'إهداء', 'icon' => 'gift'],
        ];
        $chip = 'inline-flex h-10 shrink-0 cursor-pointer items-center gap-1.5 rounded-full border border-line bg-paper px-4 text-sm transition hover:border-accent has-[:checked]:border-ink has-[:checked]:bg-ink has-[:checked]:text-paper has-[:focus-visible]:ring-3 has-[:focus-visible]:ring-accent/30';
        $row = 'no-scrollbar -mx-1 flex gap-2 overflow-x-auto px-1 pb-1 lg:flex-wrap';
        $label = 'mb-2 block text-xs font-medium text-muted';
        $toggle = 'flex cursor-pointer items-center justify-between gap-3 rounded-2xl border border-line bg-paper px-4 py-3 has-[:focus-visible]:ring-3 has-[:focus-visible]:ring-accent/30';
        $toEditor = $isEditing ? route('quotes.edit', [$quote, 'as' => 'text']) : ($isLibrary ? request()->fullUrlWithQuery(['as' => 'text']) : null);
        $fontSize = (int) $value('font_size', $quote->font_size);
    @endphp

    <form method="POST" id="studio" novalidate
        action="{{ $isEditing ? route('edit_quote', $quote) : route('store_quote') }}"
        data-config="{{ json_encode([
            'fonts' => config('quotes.fonts'), 'ratios' => config('quotes.ratios'), 'palettes' => config('card.palettes'),
            'gradients' => config('card.gradients'), 'seals' => config('card.seal_colors'), 'templates' => config('card.templates'),
            'defaults' => config('card.defaults'), 'fontSize' => config('quotes.font_size'), 'writer' => auth()->user()->name,
        ]) }}"
        class="writing-desk relative">
        @csrf
        @if ($isEditing)
            @method('PUT')
        @endif
        <input type="hidden" name="_form" value="studio">
        <input type="hidden" name="presentation" value="card">
        <input type="hidden" name="font_size" value="{{ $fontSize }}" data-font-size>
        <input type="hidden" name="texture" value="{{ $design['texture'] === 'none' ? 0 : 1 }}" data-legacy-texture>

        <div class="relative mx-auto grid max-w-6xl gap-5 px-4 pb-36 pt-6 lg:grid-cols-[minmax(0,1fr)_minmax(0,1.1fr)] lg:items-start lg:pb-12">
            <header class="lg:col-span-2">
                <x-back :href="$isEditing ? route('quotes.show', $quote) : $backUrl" label="رجوع" class="!mb-3" />
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h1 class="font-display text-2xl font-bold sm:text-3xl">مرسم الحـــروف</h1>
                        <p class="mt-1 hidden text-sm text-muted sm:block">اختر قالباً بلمسة، ثم عدّل ما شئت؛ واحفظ بطاقتك صورةً أو انشرها.</p>
                    </div>
                    <div role="group" aria-label="طريقة العرض" class="flex rounded-full border border-line bg-surface p-1 text-sm">
                        @if ($toEditor)
                            <a href="{{ $toEditor }}" class="inline-flex h-9 items-center gap-1.5 rounded-full px-4 text-muted transition hover:text-accent"><x-icon name="type" class="size-4" /> نص</a>
                        @else
                            <button type="submit" formaction="{{ route('quotes.draft') }}" name="presentation" value="text"
                                class="inline-flex h-9 items-center gap-1.5 rounded-full px-4 text-muted transition hover:text-accent"><x-icon name="type" class="size-4" /> نص</button>
                        @endif
                        <span aria-current="true" class="inline-flex h-9 items-center gap-1.5 rounded-full bg-ink px-4 text-paper"><x-icon name="palette" class="size-4" /> بطاقة</span>
                    </div>
                </div>
            </header>

            {{-- The card, always in view (on phones it stays at the top while the tools scroll). --}}
            <section aria-label="معاينة البطاقة" class="sticky top-16 z-10 -mx-4 bg-paper/95 px-4 pb-3 pt-1 lg:top-20 lg:order-2 lg:mx-0 lg:bg-transparent lg:p-0">
                <fieldset class="mb-2 flex items-center justify-center gap-1.5">
                    <legend class="sr-only">المقاس</legend>
                    @foreach (config('quotes.ratios') as $key => $ratio)
                        <label class="inline-flex h-8 cursor-pointer items-center gap-1.5 rounded-full border border-line bg-surface px-3 text-xs transition hover:border-accent has-[:checked]:border-ink has-[:checked]:bg-ink has-[:checked]:text-paper">
                            <input type="radio" name="ratio" value="{{ $key }}" class="sr-only" @checked($value('ratio', $quote->ratio) === $key)>
                            <span aria-hidden="true" class="inline-block w-2.5 rounded-[2px] border border-current" style="aspect-ratio: {{ $ratio['aspect'] }}"></span>
                            {{ $ratio['label'] }}
                        </label>
                    @endforeach
                </fieldset>
                <div data-preview-stage class="preview-stage mx-auto flex max-h-[44dvh] justify-center lg:max-h-[72vh]">
                    <div data-preview-frame class="w-full" style="max-width: {{ config('quotes.ratios.'.$quote->ratio.'.width', 560) }}px">
                        <x-quote-art :quote="$quote" class="rounded-2xl shadow-xl" />
                    </div>
                </div>
                <p data-size-hint class="mt-2 hidden text-center text-xs text-accent"></p>
            </section>

            {{-- Tools --}}
            <section class="min-w-0 space-y-4 lg:order-1">
                @include('quotes.partials.details', ['quote' => $quote, 'draft' => $draft, 'withStory' => true])

                <div class="rounded-3xl border border-line bg-surface">
                    <div role="tablist" aria-label="أدوات التصميم" class="grid grid-cols-6 border-b border-line px-1">
                        @foreach ($tabs as $key => $tab)
                            <button type="button" role="tab" id="tab-{{ $key }}" data-tab="{{ $key }}"
                                aria-controls="panel-{{ $key }}" aria-selected="{{ $loop->first ? 'true' : 'false' }}"
                                class="flex flex-col items-center gap-1 border-b-2 border-transparent py-3 text-[11px] text-muted transition hover:text-ink aria-selected:border-accent aria-selected:font-medium aria-selected:text-accent sm:text-xs">
                                <x-icon :name="$tab['icon']" class="size-5" />
                                {{ $tab['label'] }}
                            </button>
                        @endforeach
                    </div>

                    <div class="space-y-5 p-4 sm:p-5">
                        {{-- قوالب: one tap sets everything but the words and the photo --}}
                        <div role="tabpanel" id="panel-templates" data-panel="templates" aria-labelledby="tab-templates" class="space-y-3">
                            <div class="grid grid-cols-3 gap-2 sm:grid-cols-4">
                                <button type="button" data-surprise class="flex aspect-square flex-col items-center justify-center gap-1 rounded-2xl border border-dashed border-accent/60 text-sm text-accent transition hover:bg-accent-soft">
                                    <x-icon name="shuffle" class="size-6" /> فاجئني
                                </button>
                                @foreach (config('card.templates') as $key => $template)
                                    @php $palette = config('card.palettes.'.$template['palette']); @endphp
                                    <button type="button" data-template="{{ $key }}" aria-pressed="false"
                                        class="template-chip" style="--t-bg: {{ $palette['bg'] }}; --t-ink: {{ ($template['box'] ?? false) ? $palette['text'] : $palette['text'] }}; --t-box: {{ $palette['box'] }}; --t-frame: {{ $palette['frame'] }};"
                                        data-has-box="{{ ($template['box'] ?? false) ? 'on' : 'off' }}" data-has-frame="{{ ($template['frame'] ?? 'none') !== 'none' ? 'on' : 'off' }}">
                                        <span class="template-swatch" aria-hidden="true"><span>أ</span></span>
                                        <span class="text-xs">{{ $template['name'] }}</span>
                                    </button>
                                @endforeach
                            </div>
                            <p class="text-xs text-muted">القالب يغيّر الألوان والخط والإطار، ولا يمسّ كلماتك ولا صورتك.</p>
                        </div>

                        {{-- الخلفية --}}
                        <div role="tabpanel" id="panel-background" data-panel="background" aria-labelledby="tab-background" class="hidden space-y-5">
                            <fieldset>
                                <legend class="{{ $label }}">الألوان</legend>
                                <div class="{{ $row }}">
                                    @foreach (config('card.palettes') as $key => $palette)
                                        <label class="palette-dot" title="{{ $palette['name'] }}" style="--p-bg: {{ $palette['bg'] }}; --p-ink: {{ $palette['text'] }};">
                                            <input type="radio" name="design[palette]" value="{{ $key }}" class="sr-only" @checked($designValue('palette') === $key)>
                                            <span class="sr-only">{{ $palette['name'] }}</span>
                                        </label>
                                    @endforeach
                                    <label class="swatch swatch-custom shrink-0" title="لون من اختيارك">
                                        <input type="color" name="bg_color" value="{{ $value('bg_color', $quote->bg_color) }}" aria-label="لون الخلفية من اختيارك" class="swatch-picker" data-bg-color>
                                    </label>
                                </div>
                            </fieldset>

                            <fieldset>
                                <legend class="{{ $label }}">تدرّج</legend>
                                <div class="{{ $row }}">
                                    @foreach (config('card.gradients') as $key => $gradient)
                                        <label class="{{ $chip }}">
                                            <input type="radio" name="design[gradient]" value="{{ $key }}" class="sr-only" @checked($designValue('gradient') === $key)>
                                            @isset($gradient['from'])<span aria-hidden="true" class="size-4 rounded-full" style="background: linear-gradient(160deg, {{ $gradient['from'] }}, {{ $gradient['to'] }})"></span>@endisset
                                            {{ $gradient['name'] }}
                                        </label>
                                    @endforeach
                                </div>
                            </fieldset>

                            <div>
                                <span class="{{ $label }}">صورة من جهازك</span>
                                <div class="flex flex-wrap items-center gap-2">
                                    <label class="{{ $chip }}">
                                        <input type="file" accept="image/*" class="sr-only" data-photo-input>
                                        <x-icon name="image" class="size-4" /> اختر صورة
                                    </label>
                                    <button type="button" data-photo-remove hidden class="inline-flex h-10 items-center gap-1.5 rounded-full px-3 text-sm text-muted hover:text-accent"><x-icon name="x" class="size-4" /> أزلها</button>
                                </div>
                                <p class="mt-1.5 text-xs text-muted">تبقى الصورة في جهازك ولا تُرفع أبداً؛ تظهر في الصورة التي تحفظها، وتُنشر البطاقة في رقيم بلونها.</p>
                            </div>

                            <fieldset>
                                <legend class="{{ $label }}">الملمس</legend>
                                <div class="{{ $row }}">
                                    @foreach (config('card.textures') as $key => $texture)
                                        <label class="{{ $chip }}">
                                            <input type="radio" name="design[texture]" value="{{ $key }}" class="sr-only" @checked($designValue('texture') === $key)>
                                            {{ $texture }}
                                        </label>
                                    @endforeach
                                </div>
                            </fieldset>

                            <div>
                                <label for="design-dim" class="{{ $label }} flex justify-between">تعتيم <output data-dim-output>{{ $designValue('dim') }}٪</output></label>
                                <input type="range" id="design-dim" name="design[dim]" min="0" max="70" step="5" value="{{ $designValue('dim') }}" class="w-full accent-[var(--color-accent)]">
                                <p class="mt-1 text-xs text-muted">يُظلّل الخلفية ليبقى النص واضحاً، خاصة فوق الصور</p>
                            </div>
                        </div>

                        {{-- الخط --}}
                        <div role="tabpanel" id="panel-font" data-panel="font" aria-labelledby="tab-font" class="hidden space-y-5">
                            <x-font-picker :selected="$value('font', $quote->font)" />

                            <div class="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <span class="{{ $label }}">الحجم</span>
                                    <div role="group" aria-label="حجم الخط" class="flex gap-1.5">
                                        <button type="button" data-size-step="-1" class="{{ $chip }}" aria-label="أصغر">أ−</button>
                                        <button type="button" data-size-auto class="{{ $chip }}">تلقائي</button>
                                        <button type="button" data-size-step="1" class="{{ $chip }}" aria-label="أكبر">أ+</button>
                                    </div>
                                </div>
                                <fieldset>
                                    <legend class="{{ $label }}">المحاذاة</legend>
                                    <div class="flex gap-1.5">
                                        @foreach (config('card.aligns') as $key => $align)
                                            <label class="{{ $chip }}">
                                                <input type="radio" name="design[align]" value="{{ $key }}" class="sr-only" @checked($designValue('align') === $key)>
                                                {{ $align }}
                                            </label>
                                        @endforeach
                                    </div>
                                </fieldset>
                            </div>

                            <div>
                                <span class="{{ $label }}">لون النص</span>
                                <div class="{{ $row }} items-center">
                                    @foreach (['#1E1A17' => 'حبر', '#F5EFE4' => 'ورقي', '#FFFFFF' => 'أبيض', '#8A3324' => 'قاني', '#A8873F' => 'ذهبي'] as $hex => $name)
                                        <button type="button" data-text-color="{{ $hex }}" class="swatch shrink-0" style="--swatch: {{ $hex }}" title="{{ $name }}"><span class="sr-only">{{ $name }}</span></button>
                                    @endforeach
                                    <label class="swatch swatch-custom shrink-0" title="لون من اختيارك">
                                        <input type="color" name="text_color" value="{{ $value('text_color', $quote->text_color) }}" aria-label="لون النص من اختيارك" class="swatch-picker" data-ink-color>
                                    </label>
                                </div>
                            </div>

                            <details class="group rounded-2xl border border-line bg-paper">
                                <summary class="flex cursor-pointer list-none items-center justify-between p-3 text-sm font-medium [&::-webkit-details-marker]:hidden">
                                    <span class="flex items-center gap-2"><x-icon name="highlighter" class="size-4 text-accent" /> إبراز كلمات</span>
                                    <x-icon name="chevron-left" class="size-4 text-muted transition group-open:-rotate-90" />
                                </summary>
                                <div class="space-y-4 border-t border-line p-3">
                                    <div class="flex gap-1.5">
                                        @foreach (['mark' => 'تظليل', 'color' => 'تلوين'] as $key => $styleLabel)
                                            <label class="{{ $chip }}">
                                                <input type="radio" name="highlight_style" value="{{ $key }}" class="sr-only" @checked($value('highlight_style', $quote->highlight_style) === $key)>
                                                {{ $styleLabel }}
                                            </label>
                                        @endforeach
                                    </div>
                                    <div class="{{ $row }} items-center">
                                        @foreach (config('quotes.highlight_colors') as $hex => $name)
                                            <button type="button" data-highlight-color="{{ $hex }}" class="swatch shrink-0" style="--swatch: {{ $hex }}" title="{{ $name }}"><span class="sr-only">{{ $name }}</span></button>
                                        @endforeach
                                        <label class="swatch swatch-custom shrink-0" title="لون من اختيارك">
                                            <input type="color" name="highlight_color" value="{{ $value('highlight_color', $quote->highlight_color) }}" aria-label="لون الإبراز" class="swatch-picker" data-hl-color>
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
                            </details>

                            <fieldset>
                                <legend class="{{ $label }}">التنسيق</legend>
                                <div class="flex gap-1.5">
                                    @foreach (config('quotes.layouts') as $key => $layout)
                                        <label class="{{ $chip }}">
                                            <input type="radio" name="layout" value="{{ $key }}" class="sr-only" @checked($value('layout', $quote->layout) === $key)>
                                            {{ $layout['label'] }}
                                        </label>
                                    @endforeach
                                </div>
                            </fieldset>
                        </div>

                        {{-- الصندوق --}}
                        <div role="tabpanel" id="panel-box" data-panel="box" aria-labelledby="tab-box" class="hidden space-y-5">
                            <label class="{{ $toggle }}">
                                <span><span class="block text-sm font-medium">صندوق للنص</span><span class="text-xs text-muted">مربّع داخل البطاقة يحمل الكلمات</span></span>
                                <input type="hidden" name="design[box]" value="0">
                                <input type="checkbox" name="design[box]" value="1" class="size-5 accent-[var(--color-accent)]" @checked($designValue('box'))>
                            </label>
                            <div data-box-options class="space-y-5">
                                <fieldset>
                                    <legend class="{{ $label }}">النمط</legend>
                                    <div class="flex gap-1.5">
                                        @foreach (config('card.box_styles') as $key => $styleLabel)
                                            <label class="{{ $chip }}"><input type="radio" name="design[box_style]" value="{{ $key }}" class="sr-only" @checked($designValue('box_style') === $key)>{{ $styleLabel }}</label>
                                        @endforeach
                                    </div>
                                </fieldset>
                                <div>
                                    <span class="{{ $label }}">لونه</span>
                                    <div class="{{ $row }} items-center">
                                        @foreach (['#FBF7EF' => 'ورقي', '#FFFFFF' => 'أبيض', '#C9A97A' => 'كرافت', '#B9A994' => 'توب', '#1E1A17' => 'حبر', '#23305A' => 'نيلي'] as $hex => $name)
                                            <button type="button" data-box-color="{{ $hex }}" class="swatch shrink-0" style="--swatch: {{ $hex }}" title="{{ $name }}"><span class="sr-only">{{ $name }}</span></button>
                                        @endforeach
                                        <label class="swatch swatch-custom shrink-0" title="لون من اختيارك">
                                            <input type="color" name="design[box_color]" value="{{ $designValue('box_color') }}" aria-label="لون الصندوق" class="swatch-picker" data-box-color-input>
                                        </label>
                                    </div>
                                </div>
                                <fieldset>
                                    <legend class="{{ $label }}">الحشوة</legend>
                                    <div class="flex gap-1.5">
                                        @foreach (config('card.box_paddings') as $key => $paddingLabel)
                                            <label class="{{ $chip }}"><input type="radio" name="design[box_padding]" value="{{ $key }}" class="sr-only" @checked($designValue('box_padding') === $key)>{{ $paddingLabel }}</label>
                                        @endforeach
                                    </div>
                                </fieldset>
                                <fieldset>
                                    <legend class="{{ $label }}">الظل</legend>
                                    <div class="flex gap-1.5">
                                        @foreach (config('card.box_shadows') as $key => $shadowLabel)
                                            <label class="{{ $chip }}"><input type="radio" name="design[box_shadow]" value="{{ $key }}" class="sr-only" @checked($designValue('box_shadow') === $key)>{{ $shadowLabel }}</label>
                                        @endforeach
                                    </div>
                                </fieldset>
                                <label class="{{ $toggle }}">
                                    <span class="text-sm font-medium">خط حول الصندوق</span>
                                    <input type="hidden" name="design[box_outline]" value="0">
                                    <input type="checkbox" name="design[box_outline]" value="1" class="size-5 accent-[var(--color-accent)]" @checked($designValue('box_outline'))>
                                </label>
                            </div>
                        </div>

                        {{-- الإطار --}}
                        <div role="tabpanel" id="panel-frame" data-panel="frame" aria-labelledby="tab-frame" class="hidden space-y-5">
                            <fieldset>
                                <legend class="{{ $label }}">الإطار</legend>
                                <div class="grid grid-cols-4 gap-2 sm:grid-cols-7">
                                    @foreach (config('card.frames') as $key => $frameLabel)
                                        <label class="frame-chip">
                                            <input type="radio" name="design[frame]" value="{{ $key }}" class="sr-only" @checked($designValue('frame') === $key)>
                                            <x-frame-glyph :frame="$key" class="size-9" />
                                            <span class="text-[11px]">{{ $frameLabel }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </fieldset>
                            <div>
                                <span class="{{ $label }}">لون الإطار</span>
                                <div class="{{ $row }} items-center">
                                    @foreach (['#A8873F' => 'ذهبي', '#8A3324' => 'قاني', '#1E1A17' => 'حبر', '#FFFFFF' => 'أبيض', '#1F4E8C' => 'لازوردي'] as $hex => $name)
                                        <button type="button" data-frame-color="{{ $hex }}" class="swatch shrink-0" style="--swatch: {{ $hex }}" title="{{ $name }}"><span class="sr-only">{{ $name }}</span></button>
                                    @endforeach
                                    <label class="swatch swatch-custom shrink-0" title="لون من اختيارك">
                                        <input type="color" name="design[frame_color]" value="{{ $designValue('frame_color') }}" aria-label="لون الإطار" class="swatch-picker" data-frame-color-input>
                                    </label>
                                </div>
                            </div>
                            <fieldset>
                                <legend class="{{ $label }}">فاصل تحت النص</legend>
                                <div class="flex flex-wrap gap-1.5">
                                    @foreach (config('card.dividers') as $key => $dividerLabel)
                                        <label class="{{ $chip }}"><input type="radio" name="design[divider]" value="{{ $key }}" class="sr-only" @checked($designValue('divider') === $key)>{{ $dividerLabel }}</label>
                                    @endforeach
                                </div>
                            </fieldset>
                            <div class="grid gap-2 sm:grid-cols-2">
                                <label class="{{ $toggle }}">
                                    <span class="text-sm font-medium">علامتا تنصيص كبيرتان</span>
                                    <input type="hidden" name="design[big_marks]" value="0">
                                    <input type="checkbox" name="design[big_marks]" value="1" class="size-5 accent-[var(--color-accent)]" @checked($designValue('big_marks'))>
                                </label>
                                <label class="{{ $toggle }}">
                                    <span class="text-sm font-medium">علامة رقيم</span>
                                    <input type="hidden" name="design[brand]" value="0">
                                    <input type="checkbox" name="design[brand]" value="1" class="size-5 accent-[var(--color-accent)]" @checked($designValue('brand'))>
                                </label>
                            </div>
                        </div>

                        {{-- إهداء: names and message live only in the saved image (no name="…": never sent) --}}
                        <div role="tabpanel" id="panel-gift" data-panel="gift" aria-labelledby="tab-gift" class="hidden space-y-5">
                            <label class="{{ $toggle }}">
                                <span><span class="block text-sm font-medium">بطاقة إهداء</span><span class="text-xs text-muted">«إلى» و«من» ورسالة قصيرة، في الصورة التي تحفظها</span></span>
                                <input type="checkbox" data-gift-toggle class="size-5 accent-[var(--color-accent)]">
                            </label>
                            <div data-gift-fields hidden class="grid gap-3 sm:grid-cols-2">
                                <div>
                                    <label for="gift-to" class="mb-1.5 block text-sm font-medium">إلى</label>
                                    <input id="gift-to" data-gift="to" maxlength="24" autocomplete="off" class="block h-11 w-full rounded-xl border border-line bg-white px-3 focus:border-accent focus:outline-none focus:ring-3 focus:ring-accent/20 dark:bg-surface" placeholder="أمي">
                                </div>
                                <div>
                                    <label for="gift-from" class="mb-1.5 block text-sm font-medium">من</label>
                                    <input id="gift-from" data-gift="from" maxlength="24" autocomplete="off" class="block h-11 w-full rounded-xl border border-line bg-white px-3 focus:border-accent focus:outline-none focus:ring-3 focus:ring-accent/20 dark:bg-surface" placeholder="{{ auth()->user()->name }}">
                                </div>
                                <div class="sm:col-span-2">
                                    <label for="gift-message" class="mb-1.5 block text-sm font-medium">رسالة قصيرة</label>
                                    <input id="gift-message" data-gift="message" maxlength="80" autocomplete="off" class="block h-11 w-full rounded-xl border border-line bg-white px-3 focus:border-accent focus:outline-none focus:ring-3 focus:ring-accent/20 dark:bg-surface" placeholder="كلماتٌ بقيت معي، فأهديتها لك">
                                    <p class="mt-1.5 text-xs text-muted">حتى ٨٠ حرفاً. الأسماء والرسالة لا تُنشر في رقيم؛ تظهر في الصورة المحفوظة وحدها.</p>
                                </div>
                            </div>

                            <label class="{{ $toggle }}">
                                <span><span class="block text-sm font-medium">ختم شمعي</span><span class="text-xs text-muted">يُختم به طرف البطاقة</span></span>
                                <input type="hidden" name="design[seal]" value="0">
                                <input type="checkbox" name="design[seal]" value="1" class="size-5 accent-[var(--color-accent)]" @checked($designValue('seal'))>
                            </label>
                            <div data-seal-options class="space-y-4">
                                <fieldset>
                                    <legend class="{{ $label }}">لون الشمع</legend>
                                    <div class="flex gap-2.5">
                                        @foreach (config('card.seal_colors') as $key => $wax)
                                            <label class="swatch" title="{{ $wax['name'] }}" style="--swatch: radial-gradient(circle at 35% 30%, {{ $wax['light'] }}, {{ $wax['base'] }} 55%, {{ $wax['dark'] }})">
                                                <input type="radio" name="design[seal_color]" value="{{ $key }}" class="sr-only" @checked($designValue('seal_color') === $key)>
                                                <span class="sr-only">{{ $wax['name'] }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </fieldset>
                                <fieldset>
                                    <legend class="{{ $label }}">نقش الختم</legend>
                                    <div class="flex gap-1.5">
                                        @foreach (config('card.seal_emblems') as $key => $emblemLabel)
                                            <label class="{{ $chip }}"><input type="radio" name="design[seal_emblem]" value="{{ $key }}" class="sr-only" @checked($designValue('seal_emblem') === $key)>{{ $emblemLabel }}</label>
                                        @endforeach
                                    </div>
                                </fieldset>
                                <fieldset>
                                    <legend class="{{ $label }}">موضعه</legend>
                                    <div class="flex gap-1.5">
                                        @foreach (config('card.seal_positions') as $key => $positionLabel)
                                            <label class="{{ $chip }}"><input type="radio" name="design[seal_position]" value="{{ $key }}" class="sr-only" @checked($designValue('seal_position') === $key)>{{ $positionLabel }}</label>
                                        @endforeach
                                    </div>
                                </fieldset>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        {{-- Save the card as an image, share it, or publish it — always in reach. --}}
        <div class="fixed inset-x-0 bottom-16 z-30 border-t border-line bg-surface px-4 py-3 md:bottom-0 lg:static lg:border-0 lg:bg-transparent lg:pb-10 lg:pt-0">
            <div class="mx-auto flex max-w-6xl items-center gap-2">
                <x-button type="submit" icon="quill" class="h-12 flex-1 sm:flex-none sm:px-8">{{ $isEditing ? 'احفظ' : 'انشر' }}</x-button>
                <button type="button" data-save-image class="inline-flex h-12 items-center gap-2 rounded-full border border-ink px-4 font-medium transition hover:bg-ink hover:text-paper">
                    <x-icon name="download" class="size-5" /> <span>صورة</span>
                </button>
                <button type="button" data-share-image hidden class="grid size-12 place-items-center rounded-full border border-line text-ink transition hover:border-accent hover:text-accent" aria-label="شارك الصورة">
                    <x-icon name="share" class="size-5" />
                </button>
                <span data-export-status role="status" class="hidden text-xs text-muted sm:inline"></span>
            </div>
        </div>
    </form>
@endsection
