@props(['name', 'label', 'type' => 'text', 'hint' => null, 'value' => null])

<div>
    <label for="{{ $name }}" class="mb-1.5 block text-sm font-medium">{{ $label }}</label>

    <input id="{{ $name }}" name="{{ $name }}" type="{{ $type }}"
        @if ($type !== 'password') value="{{ old($name, $value) }}" @endif
        @if ($hint) aria-describedby="{{ $name }}-hint" @endif
        {{ $attributes->class([
            'block h-12 w-full rounded-xl border bg-white px-4 text-ink transition placeholder:text-muted/70 dark:bg-surface',
            'focus:border-accent focus:outline-none focus:ring-3 focus:ring-accent/20',
            'border-accent' => $errors->has($name),
            'border-line' => ! $errors->has($name),
        ]) }}>

    @if ($hint)
        <p id="{{ $name }}-hint" class="mt-1.5 text-xs text-muted">{{ $hint }}</p>
    @endif

    @error($name)
        <p class="mt-1 text-xs font-medium text-accent">{{ $message }}</p>
    @enderror
</div>
