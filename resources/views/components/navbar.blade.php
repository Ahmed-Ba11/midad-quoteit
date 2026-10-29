<header class="sticky top-0 z-40 border-b border-line bg-paper/85 backdrop-blur-md">
    <nav class="mx-auto flex h-16 max-w-6xl items-center gap-3 px-4 sm:gap-6">
        <a href="{{ route('home') }}" class="logo font-display text-3xl font-black text-accent">مِـــداد</a>

        <ul class="hidden items-center gap-1 text-sm sm:flex">
            <li>
                <a href="{{ route('home') }}" @class([
                    'rounded-full px-3 py-2 transition hover:text-accent',
                    'font-bold text-accent' => request()->routeIs('home'),
                ]) @if (request()->routeIs('home')) aria-current="page" @endif>الرئيسية</a>
            </li>
            <li>
                <a href="{{ route('library.index') }}" @class([
                    'rounded-full px-3 py-2 transition hover:text-accent',
                    'font-bold text-accent' => request()->routeIs('library.*'),
                ]) @if (request()->routeIs('library.*')) aria-current="page" @endif>المكتبة</a>
            </li>
            <li>
                <a href="{{ route('books.index') }}" @class([
                    'rounded-full px-3 py-2 transition hover:text-accent',
                    'font-bold text-accent' => request()->routeIs('books.*'),
                ]) @if (request()->routeIs('books.*')) aria-current="page" @endif>الكتب</a>
            </li>
            @auth
                @php $onSaved = request()->routeIs('profile.show') && request('tab') === 'saved'; @endphp
                <li>
                    <a href="{{ route('profile.show', [auth()->user(), 'tab' => 'saved']) }}" @class([
                        'rounded-full px-3 py-2 transition hover:text-accent',
                        'font-bold text-accent' => $onSaved,
                    ]) @if ($onSaved) aria-current="page" @endif>المحفوظات</a>
                </li>
            @endauth
        </ul>

        <div class="ms-auto flex items-center gap-1 sm:gap-2">
            <button type="button" data-theme-toggle aria-label="تبديل الوضع الليلي" title="الوضع الليلي"
                class="grid size-11 place-items-center rounded-full text-muted transition hover:bg-accent-soft hover:text-accent">
                <x-icon name="moon" class="size-5 dark:hidden" />
                <x-icon name="sun" class="hidden size-5 dark:block" />
            </button>

            @auth
                <x-button popovertarget="quote-popup" icon="plus" aria-label="شارك اقتباساً">
                    <span class="hidden sm:inline">شارك اقتباساً</span>
                </x-button>

                <a href="{{ route('profile.show', auth()->user()) }}" aria-label="ملفي الشخصي"
                    class="flex items-center gap-2 rounded-full transition hover:text-accent">
                    <span aria-hidden="true"
                        class="grid size-10 shrink-0 place-items-center rounded-full bg-accent-soft font-bold text-accent">
                        {{ mb_substr(auth()->user()->name, 0, 1) }}
                    </span>
                    <span class="hidden max-w-28 truncate text-sm font-medium sm:inline">{{ auth()->user()->name }}</span>
                </a>

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
