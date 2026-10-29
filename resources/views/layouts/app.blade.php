<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@hasSection('title')@yield('title') · @endif مِداد</title>

    <script>
        try {
            const theme = localStorage.getItem('theme');
            if (theme === 'dark' || (!theme && matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
            }
        } catch {}
    </script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Amiri:wght@400;700&family=IBM+Plex+Sans+Arabic:wght@300;400;500;700&family=Noto+Naskh+Arabic:wght@700&display=swap">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="flex min-h-screen flex-col bg-paper font-sans text-ink antialiased">
    <x-navbar />

    <main class="flex-1">
        @yield('content')
    </main>

    <footer class="mt-16 border-t border-line">
        <div class="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-2 px-4 py-8 text-sm text-muted">
            <p><span class="logo font-display text-base font-bold text-ink">مِـــداد</span> · منصّة لنشر الاقتباسات وتصميمها</p>
            <p>معسكر القمّة · ٢٠٢٦</p>
        </div>
    </footer>

    @auth
        <div id="quote-popup" popover
            class="m-auto w-[min(92vw,32rem)] rounded-3xl border border-line bg-surface p-0 text-ink shadow-2xl backdrop:bg-black/40 backdrop:backdrop-blur-sm">
            <form method="POST" action="{{ route('store_quote') }}" class="space-y-5 p-6 sm:p-8">
                @csrf
                <input type="hidden" name="_form" value="share">

                <div class="flex items-center justify-between">
                    <h2 class="font-display text-2xl font-bold">شارك اقتباساً</h2>
                    <button type="button" popovertarget="quote-popup" popovertargetaction="hide" aria-label="إغلاق"
                        class="grid size-11 place-items-center rounded-full text-muted transition hover:bg-accent-soft hover:text-accent">
                        <x-icon name="x" />
                    </button>
                </div>

                <x-textarea name="content" label="نص الاقتباس" hint="من ٣ إلى ١٥٥ حرفاً" rows="5" minlength="3"
                    maxlength="155" required autofocus placeholder="اكتب اقتباساً أثّر فيك…"
                    :value="old('_form') === 'share' ? old('content') : ''"
                    :show-error="old('_form') === 'share'" />

                <x-button type="submit" class="w-full">انشر الاقتباس</x-button>
            </form>
        </div>

        @if (old('_form') === 'share' && $errors->any())
            <script>
                document.getElementById('quote-popup').showPopover();
            </script>
        @endif
    @endauth
</body>

</html>
