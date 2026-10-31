{{--
    خط ثمانية on the hosted site. The repository is public, so the font files are not in it: they are
    served from a public Laravel Cloud bucket (THMANYAH_FONTS_URL). These rules come after app.css,
    so they win over its local @font-face. Locally (no URL set) nothing is printed and Vite serves
    resources/fonts/thmanyah as before.
--}}
@php
    $base = rtrim((string) config('services.thmanyah.url'), '/');
    $faces = [
        ['thmanyah sans', 'thmanyahsans-Regular', 400],
        ['thmanyah sans', 'thmanyahsans-Medium', 500],
        ['thmanyah sans', 'thmanyahsans-Bold', 700],
        ['thmanyah serif display', 'thmanyahserifdisplay-Bold', 700],
        ['thmanyah serif display', 'thmanyahserifdisplay-Black', 900],
        ['thmanyah serif text', 'thmanyahseriftext-Regular', 400],
        ['thmanyah serif text', 'thmanyahseriftext-Medium', 500],
    ];
@endphp

@if ($base !== '')
    <link rel="preconnect" href="{{ $base }}" crossorigin>
    {{-- The body text and the headings: fetched at once so the first paint is already in ثمانية. --}}
    <link rel="preload" href="{{ $base }}/thmanyahsans-Regular.woff2" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="{{ $base }}/thmanyahserifdisplay-Bold.woff2" as="font" type="font/woff2" crossorigin>
    <style>
        @foreach ($faces as [$family, $file, $weight])
            @font-face { font-family: '{{ $family }}'; src: url('{{ $base }}/{{ $file }}.woff2') format('woff2'); font-weight: {{ $weight }}; font-display: swap; }
        @endforeach
    </style>
@endif
