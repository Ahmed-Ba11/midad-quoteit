@props(['title', 'icon' => 'feather'])

<div {{ $attributes->class('rounded-3xl border border-dashed border-line bg-surface px-6 py-16 text-center') }}>
    <span class="mx-auto grid size-14 place-items-center rounded-full bg-accent-soft text-accent">
        <x-icon :name="$icon" class="size-6" />
    </span>
    <h2 class="mt-5 font-display text-2xl font-bold">{{ $title }}</h2>
    <div class="mx-auto mt-2 max-w-sm text-muted">{{ $slot }}</div>
</div>
