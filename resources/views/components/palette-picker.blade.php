@props(['name', 'label', 'selected' => null, 'hint' => null])

{{-- A row of the ready-made palettes (config/quotes.php) as radio swatches. --}}
<fieldset {{ $attributes }}>
    <legend class="mb-2 text-sm font-medium">{{ $label }}</legend>
    <div class="flex flex-wrap gap-2.5">
        @foreach (config('quotes.palettes') as $key => $palette)
            <label class="group cursor-pointer" title="{{ $palette['name'] }}">
                <input type="radio" name="{{ $name }}" value="{{ $key }}" class="peer sr-only"
                    @checked(old($name, $selected ?? array_key_first(config('quotes.palettes'))) === $key)
                    @if ($hint) aria-describedby="{{ $name }}-hint" @endif>
                <span class="grid size-11 place-items-center rounded-full border-2 border-transparent font-display text-lg font-bold shadow-sm ring-1 ring-black/10 transition peer-checked:scale-110 peer-checked:border-accent peer-focus-visible:ring-3 peer-focus-visible:ring-accent/40"
                    style="background: {{ $palette['bg'] }}; color: {{ $palette['highlight'] }}">ع</span>
                <span class="sr-only">{{ $palette['name'] }}</span>
            </label>
        @endforeach
    </div>
    @if ($hint)
        <p id="{{ $name }}-hint" class="mt-1.5 text-xs text-muted">{{ $hint }}</p>
    @endif
    @error($name)
        <p class="mt-1 text-xs font-medium text-accent">{{ $message }}</p>
    @enderror
</fieldset>
