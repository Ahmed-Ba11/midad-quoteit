{{--
    خط ثمانية on the hosted site. The repository is public, so the font files are not in it: they sit in
    a public Laravel Cloud bucket (THMANYAH_FONTS_URL), and FontController serves them from the site's
    own address with a year's cache. These rules come after app.css, so they win over its local
    @font-face. `block`, not `swap`: a page never shows another font first and then switches —
    after the first visit the fonts come straight from the browser's cache.
    Locally (no URL set) nothing is printed and Vite serves resources/fonts/thmanyah as before.
--}}
@php
    $faces = [
        ['thmanyah sans', 'thmanyahsans-Regular', 400],
        ['thmanyah sans', 'thmanyahsans-Medium', 500],
        ['thmanyah sans', 'thmanyahsans-Bold', 700],
        ['thmanyah serif display', 'thmanyahserifdisplay-Bold', 700],
        ['thmanyah serif display', 'thmanyahserifdisplay-Black', 900],
        ['thmanyah serif text', 'thmanyahseriftext-Regular', 400],
        ['thmanyah serif text', 'thmanyahseriftext-Medium', 500],
    ];
    // What every page draws first: body text, labels, bold words and headings.
    $preload = ['thmanyahsans-Regular', 'thmanyahsans-Medium', 'thmanyahsans-Bold', 'thmanyahserifdisplay-Bold'];
@endphp

@if (filled(config('services.thmanyah.url')))
    @foreach ($preload as $file)
        <link rel="preload" href="{{ route('fonts.thmanyah', $file) }}" as="font" type="font/woff2" crossorigin>
    @endforeach
    <style>
        @foreach ($faces as [$family, $file, $weight])
            @font-face { font-family: '{{ $family }}'; src: url('{{ route('fonts.thmanyah', $file) }}') format('woff2'); font-weight: {{ $weight }}; font-display: block; }
        @endforeach
    </style>
@endif
