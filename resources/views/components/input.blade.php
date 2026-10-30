@props(['name', 'label', 'type' => 'text', 'hint' => null, 'value' => null, 'id' => null, 'showError' => true, 'useOld' => true])

@php $id ??= $name; @endphp

<div>
    <label for="{{ $id }}" class="mb-1.5 block text-sm font-medium">{{ $label }}</label>

    <input id="{{ $id }}" name="{{ $name }}" type="{{ $type }}"
        @if ($type !== 'password') value="{{ $useOld ? old($name, $value) : $value }}" @endif
        @if ($hint) aria-describedby="{{ $id }}-hint" @endif
        {{ $attributes->class([
            'block h-12 w-full rounded-xl border bg-white px-4 text-ink transition placeholder:text-muted/70 dark:bg-surface',
            'focus:border-accent focus:outline-none focus:ring-3 focus:ring-accent/20',
            'border-accent' => $showError && $errors->has($name),
            'border-line' => ! ($showError && $errors->has($name)),
        ]) }}>

    @if ($hint)
        <p id="{{ $id }}-hint" class="mt-1.5 text-xs text-muted">{{ $hint }}</p>
    @endif

    @if ($showError)
        @error($name)
            <p class="mt-1 text-xs font-medium text-accent">{{ $message }}</p>
        @enderror
    @endif
</div>
