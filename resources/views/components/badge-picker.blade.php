@props(['user'])

{{-- اختر شارتك: the emblem shown beside your name everywhere, or simply your initial. --}}
@php
    $colors = $user->accentColors();
    $current = old('badge', $user->badge ?? '');
    $swatch = 'grid size-12 place-items-center rounded-full ring-2 ring-transparent transition peer-checked:ring-accent peer-checked:ring-offset-2 peer-checked:ring-offset-paper peer-focus-visible:ring-accent/50';
@endphp

<fieldset {{ $attributes }}>
    <legend class="mb-2 text-sm font-medium">شارتك</legend>
    <p id="badge-hint" class="-mt-1 mb-3 text-xs text-muted">تظهر بجانب اسمك في كل مكان بدل الحرف الأول.</p>

    <div class="grid grid-cols-5 gap-3 sm:grid-cols-7">
        <label class="flex cursor-pointer flex-col items-center gap-1" title="حرفك الأول">
            <input type="radio" name="badge" value="" class="peer sr-only" aria-describedby="badge-hint" @checked($current === '')>
            <span class="{{ $swatch }} font-bold" style="background: {{ $colors['bg'] }}; color: {{ $colors['highlight'] }}">{{ mb_substr($user->name, 0, 1) }}</span>
            <span class="text-xs text-muted">حرفك</span>
        </label>
        @foreach (config('quotes.badges') as $key => $badge)
            <label class="flex cursor-pointer flex-col items-center gap-1" title="{{ $badge['label'] }}">
                <input type="radio" name="badge" value="{{ $key }}" class="peer sr-only" aria-describedby="badge-hint" @checked($current === $key)>
                <span class="{{ $swatch }}" style="background: {{ $colors['bg'] }}; color: {{ $colors['highlight'] }}">
                    <x-badge :name="$key" class="size-7" />
                </span>
                <span class="max-w-full truncate text-xs text-muted">{{ $badge['label'] }}</span>
            </label>
        @endforeach
    </div>

    @error('badge')
        <p class="mt-1 text-xs font-medium text-accent">{{ $message }}</p>
    @enderror
</fieldset>
