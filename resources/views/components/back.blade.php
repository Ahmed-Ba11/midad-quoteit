@props(['href', 'label'])

{{--
    "رجوع": returns to where the reader came from on this site — the same place on that page (the poets'
    arcade, a shelf, a search) — through history (resources/js/app.js, [data-back]). When there is no
    such page, or it was a form, sign-in or the page itself, it goes to `href`: the page this one
    belongs to. Large tap target for phones.
--}}
<nav aria-label="رجوع" {{ $attributes->class('mb-5') }}>
    <a href="{{ $href }}" data-back
        class="group inline-flex h-11 items-center gap-1.5 rounded-full border border-line bg-surface/80 pe-4 ps-2.5 text-sm text-muted transition hover:border-accent hover:text-accent">
        <x-icon name="back" class="size-5 transition group-hover:translate-x-0.5" />
        <span>{{ $label }}</span>
    </a>
</nav>
