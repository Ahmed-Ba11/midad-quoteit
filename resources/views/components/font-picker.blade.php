@props(['selected' => 'amiri', 'name' => 'font'])

{{--
    «بأيّ قلمٍ تكتب؟» — the fonts as a compact list, one row each, grouped (نسخ، رقعة، كوفي…) with
    filter chips. Each row writes a short line in its own face (a tiny preview stylesheet holding only
    those letters, loaded by resources/js/font-picker.js when the list first appears); the chosen font
    is loaded in full and shown at once in the live preview. A hint says what each face suits.
--}}
@php
    $fonts = config('quotes.fonts');
    $groups = collect(config('quotes.font_groups'))->filter(fn (string $label, string $group) => collect($fonts)->contains('group', $group));
    $best = ['verse' => 'للشعر', 'prose' => 'للنثر', 'both' => 'للشعر والنثر'];
    $chip = 'h-8 shrink-0 rounded-full border border-line px-3 text-xs transition hover:border-accent aria-pressed:border-ink aria-pressed:bg-ink aria-pressed:text-paper';
@endphp

<div data-font-picker data-fonts="{{ json_encode(collect($fonts)->map(fn (array $font) => ['google' => $font['google'] ?? null, 'stack' => $font['stack']])) }}" {{ $attributes }}>
    <div role="toolbar" aria-label="أنواع الخطوط" class="no-scrollbar -mx-1 flex gap-1.5 overflow-x-auto px-1 pb-2">
        <button type="button" data-font-filter="all" aria-pressed="true" class="{{ $chip }}">الكل</button>
        @foreach ($groups as $group => $label)
            <button type="button" data-font-filter="{{ $group }}" aria-pressed="false" class="{{ $chip }}">{{ $label }}</button>
        @endforeach
    </div>

    <fieldset>
        <legend class="sr-only">الخط</legend>
        <div class="font-list max-h-72 overflow-y-auto overscroll-contain rounded-2xl border border-line bg-paper">
            @foreach ($fonts as $key => $font)
                <label data-font-row data-group="{{ $font['group'] }}" class="font-row">
                    <input type="radio" name="{{ $name }}" value="{{ $key }}" class="sr-only" @checked($selected === $key)>
                    <span class="font-row-sample" style="font-family: {{ $font['stack'] }};{{ ($font['italic'] ?? false) ? ' font-style: italic;' : '' }}" @if ($font['lang'] === 'en') dir="ltr" @endif>
                        {{ $font['lang'] === 'en' ? 'Words that stay' : 'بأيّ قلمٍ تكتب؟' }}
                    </span>
                    <span class="font-row-meta">
                        <span class="font-medium text-ink">{{ $font['label'] }}</span>
                        <span>{{ config('quotes.font_groups.'.$font['group']) }} · {{ $best[$font['best']] }}</span>
                    </span>
                    <x-icon name="check" class="font-row-check size-4" />
                </label>
            @endforeach
        </div>
    </fieldset>
</div>
