@props(['href', 'label'])

{{--
    "رجوع": goes up to the page this one belongs to (its parent), never blindly back in history —
    after saving a form, history would return to the form itself. Large tap target for phones.
--}}
<nav aria-label="رجوع" {{ $attributes->class('mb-5') }}>
    <a href="{{ $href }}"
        class="group inline-flex h-11 items-center gap-1.5 rounded-full border border-line bg-surface/80 pe-4 ps-2.5 text-sm text-muted backdrop-blur transition hover:border-accent hover:text-accent">
        <x-icon name="back" class="size-5 transition group-hover:translate-x-0.5" />
        <span>{{ $label }}</span>
    </a>
</nav>
