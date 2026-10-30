@props(['type' => 'verse', 'id', 'text', 'label' => null])

{{-- Adds a verse or saying to the selection bar (resources/js/library.js); state survives page changes. Kept light: it repeats many times per page. --}}
<button type="button" data-pick="{{ $type }}:{{ $id }}" data-pick-text="{{ $text }}" aria-pressed="false"
    {{ $attributes->class('pick inline-flex h-10 shrink-0 items-center gap-1.5 rounded-full border border-current/25 px-4 text-sm transition hover:border-current aria-pressed:border-transparent aria-pressed:bg-accent aria-pressed:text-paper') }}><span class="pick-off">＋ {{ $label ?? ($type === 'verse' ? 'اختر البيت' : 'اختر المأثور') }}</span><span class="pick-on">✓ مُختار</span></button>
