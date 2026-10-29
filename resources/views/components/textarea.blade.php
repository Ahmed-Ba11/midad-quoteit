@props(['name', 'label', 'hint' => null, 'value' => null, 'showError' => true, 'id' => null])

@php
    $id ??= $name;
    $hasError = $showError && $errors->has($name);
@endphp

<div>
    <label for="{{ $id }}" class="mb-1.5 block text-sm font-medium">{{ $label }}</label>

    <textarea id="{{ $id }}" name="{{ $name }}" @if ($hint) aria-describedby="{{ $id }}-hint" @endif
        {{ $attributes->class([
            'block w-full resize-none rounded-xl border bg-white px-4 py-3 leading-relaxed text-ink transition placeholder:text-muted/70 dark:bg-surface',
            'focus:border-accent focus:outline-none focus:ring-3 focus:ring-accent/20',
            'border-accent' => $hasError,
            'border-line' => ! $hasError,
        ]) }}>{{ $value }}</textarea>

    @if ($hint)
        <p id="{{ $id }}-hint" class="mt-1.5 text-xs text-muted">{{ $hint }}</p>
    @endif

    @if ($hasError)
        <p class="mt-1 text-xs font-medium text-accent">{{ $errors->first($name) }}</p>
    @endif
</div>
