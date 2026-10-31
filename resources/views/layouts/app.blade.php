<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>@hasSection('title')@yield('title') · @endif رقيم</title>
    <meta name="description" content="رقيم، حيثُ يُرقَمُ الأثر: اقتبس من كتاب تقرؤه أو بيت تحفظه، وصمّمه وشاركه.">

    {{-- PWA: installable on phones, with the bar in the brand's قاني red. --}}
    <meta name="theme-color" content="#8A3324">
    <link rel="manifest" href="/manifest.webmanifest">
    <link rel="icon" href="/favicon.ico" sizes="48x48">
    <link rel="icon" href="/icons/favicon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/icons/apple-touch-icon.png">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="رقيم">

    <script>
        try {
            const theme = localStorage.getItem('theme');
            if (theme === 'dark' || (!theme && matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
            }
        } catch {}
    </script>

    {{-- Reading fonts block the first paint; the card-design fonts load after it, so phones show text sooner. --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Amiri:ital,wght@0,400;0,700;1,400&family=IBM+Plex+Sans+Arabic:wght@300;400;500;700&family=Noto+Naskh+Arabic:wght@400;700&display=swap">
    {{-- Only the card fonts this page shows (App\Support\PageFonts), after first paint. --}}
    @if ($cardFonts = app(\App\Support\PageFonts::class)->stylesheetUrl())
        <link rel="stylesheet" media="print" onload="this.media='all'" href="{{ $cardFonts }}">
    @endif

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{--
        Fetch a page as soon as the reader points at (or touches) its link, so it opens at once.
        Prefetch only reads; pages that sign in or out, start a form, or edit are never fetched ahead.
    --}}
    <script type="speculationrules">
        {
            "prefetch": [{
                "where": {
                    "and": [
                        { "href_matches": "/*" },
                        { "not": { "href_matches": ["/logout", "/login", "/signup", "/quotes/create", "/diwans/create", "/*/edit", "/_boost/*"] } },
                        { "not": { "selector_matches": "[target=_blank], [data-no-prefetch]" } }
                    ]
                },
                "eagerness": "moderate"
            }]
        }
    </script>
</head>

<body class="flex min-h-screen flex-col bg-paper pb-16 font-sans text-ink antialiased md:pb-0">
    <x-brand-mark-defs />
    @yield('backdrop')
    <x-navbar />

    <main class="relative z-[1] flex-1">
        @yield('content')
    </main>

    <x-site-footer />

    <x-toast />

    @auth
        <x-share-sheet />
    @endauth
</body>

</html>
