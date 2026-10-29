<header class="sticky top-0 z-40 border-b border-line bg-paper/85 backdrop-blur-md">
    <nav class="mx-auto flex h-16 max-w-6xl items-center gap-6 px-4">
        <a href="{{ route('home') }}" class="logo font-display text-3xl font-black text-accent">مِـــداد</a>

        <ul class="hidden items-center gap-1 text-sm sm:flex">
            <li>
                <a href="{{ route('home') }}" @class([
                    'rounded-full px-3 py-2 transition hover:text-accent',
                    'font-bold text-accent' => request()->routeIs('home'),
                ]) @if (request()->routeIs('home')) aria-current="page" @endif>الرئيسية</a>
            </li>
        </ul>

        <div class="ms-auto flex items-center gap-2">
            <button type="button" data-theme-toggle aria-label="تبديل الوضع الليلي" title="الوضع الليلي"
                class="grid size-11 place-items-center rounded-full text-muted transition hover:bg-accent-soft hover:text-accent">
                <x-icon name="moon" class="size-5 dark:hidden" />
                <x-icon name="sun" class="hidden size-5 dark:block" />
            </button>

            @auth
                <x-button popovertarget="quote-popup" icon="plus" aria-label="شارك اقتباساً">
                    <span class="hidden sm:inline">شارك اقتباساً</span>
                </x-button>

                <span title="{{ auth()->user()->name }}"
                    class="grid size-10 place-items-center rounded-full bg-accent-soft font-bold text-accent">
                    {{ mb_substr(auth()->user()->name, 0, 1) }}
                </span>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" aria-label="تسجيل الخروج" title="تسجيل الخروج"
                        class="grid size-11 place-items-center rounded-full text-muted transition hover:bg-accent-soft hover:text-accent">
                        <x-icon name="logout" class="size-5 rtl:-scale-x-100" />
                    </button>
                </form>
            @else
                <x-button variant="ghost" :href="route('login')">دخول</x-button>
                <x-button :href="route('signup')">حساب جديد</x-button>
            @endauth
        </div>
    </nav>
</header>
