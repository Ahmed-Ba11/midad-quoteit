@props(['layout'])

{{-- A tiny picture of each layout for «كيف يُكتب النص؟»: two hemistichs, a paragraph, or the detector's spark. --}}
<svg viewBox="0 0 56 32" aria-hidden="true" {{ $attributes }} fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
    @switch($layout)
        @case('verse')
            <path d="M33 9h18M33 17h18M33 25h18M5 9h18M5 17h18M5 25h18" />
            <path d="M28 9.5v0M28 17.5v0M28 25.5v0" stroke-width="3" />
            @break
        @case('prose')
            <path d="M5 8h46M5 15h46M5 22h46M21 29h30" />
            @break
        @default
            <path d="M24 9h18M24 17h18M24 25h12" />
            <path d="M12 6l1.6 4.4L18 12l-4.4 1.6L12 18l-1.6-4.4L6 12l4.4-1.6Z" fill="currentColor" stroke="none" />
    @endswitch
</svg>
