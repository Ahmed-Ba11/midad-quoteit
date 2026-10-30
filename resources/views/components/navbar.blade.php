@php
    $user = auth()->user();
    $onMyCorner = $user && request()->routeIs('profile.show') && request()->route('user')?->is($user);
    // Each place has an icon and a one-line hint, shown under the link on hover (and read by screen readers).
    $places = [
        ['route' => 'home', 'active' => request()->routeIs('home', 'quotes.*'), 'label' => 'الرئيسية', 'icon' => 'feather', 'hint' => 'أحدث ما شاركه القرّاء'],
        ['route' => 'library.index', 'active' => request()->routeIs('library.*', 'diwans.*'), 'label' => 'المكتبة', 'icon' => 'scroll', 'hint' => 'معلّقات ودواوين موثّقة'],
        ['route' => 'books.index', 'active' => request()->routeIs('books.*'), 'label' => 'الكتب', 'icon' => 'book-open', 'hint' => 'ما اقتبسه القرّاء من كتبهم'],
    ];
    if ($user) {
        $places[] = ['route' => 'profile.show', 'params' => $user, 'active' => $onMyCorner, 'label' => 'ركني', 'icon' => 'user', 'hint' => 'ما كتبتَ وجمعتَ وحفظت'];
    }
@endphp

<header class="sticky top-0 z-40 border-b border-line bg-paper/85 backdrop-blur-md">
    <nav aria-label="التنقّل الرئيسي" class="mx-auto flex h-16 max-w-6xl items-center gap-3 px-4 sm:gap-6">
        <a href="{{ route('home') }}" class="flex items-center gap-2" aria-label="مِداد، الصفحة الرئيسية">
            <x-ornament name="shamsa" class="size-8 text-gold" />
            <span class="logo font-display text-3xl font-black text-accent">مِـــداد</span>
        </a>

        {{-- Wide screens: the places, each with a short hint underneath on hover. --}}
        <ul class="hidden items-center gap-1 text-sm md:flex">
            @foreach ($places as $place)
                <li class="nav-place relative">
                    <a href="{{ route($place['route'], $place['params'] ?? []) }}" aria-describedby="hint-{{ $loop->index }}"
                        @if ($place['active']) aria-current="page" @endif
                        @class([
                            'inline-flex items-center gap-1.5 rounded-full px-3.5 py-2 transition',
                            'bg-accent-soft font-bold text-accent' => $place['active'],
                            'text-ink/80 hover:bg-surface hover:text-accent' => ! $place['active'],
                        ])>
                        <x-icon :name="$place['icon']" class="size-4" />
                        {{ $place['label'] }}
                    </a>
                    <span id="hint-{{ $loop->index }}" role="tooltip" class="nav-hint">{{ $place['hint'] }}</span>
                </li>
            @endforeach
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

                @php $mine = $user->accentColors(); @endphp
                <a href="{{ route('profile.show', $user) }}" aria-label="ركني: {{ $user->name }}" title="ركني"
                    class="hidden items-center gap-2 rounded-full border border-line py-1 pe-3 ps-1 transition hover:border-accent sm:flex">
                    <span aria-hidden="true" class="grid size-8 shrink-0 place-items-center rounded-full text-sm font-bold"
                        style="background: {{ $mine['bg'] }}; color: {{ $mine['highlight'] }}">
                        {{ mb_substr($user->name, 0, 1) }}
                    </span>
                    <span class="max-w-28 truncate text-sm font-medium">{{ $user->name }}</span>
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

{{-- Phones: the same places as a tab bar at the bottom, within thumb's reach. --}}
<nav aria-label="التنقّل السريع" class="fixed inset-x-0 bottom-0 z-40 border-t border-line bg-paper/95 pb-[env(safe-area-inset-bottom)] backdrop-blur-md md:hidden">
    <ul class="mx-auto grid max-w-md" style="grid-template-columns: repeat({{ count($places) }}, minmax(0, 1fr))">
        @foreach ($places as $place)
            <li>
                <a href="{{ route($place['route'], $place['params'] ?? []) }}" @if ($place['active']) aria-current="page" @endif
                    @class([
                        'flex h-16 flex-col items-center justify-center gap-1 text-[11px] transition',
                        'font-bold text-accent' => $place['active'],
                        'text-muted' => ! $place['active'],
                    ])>
                    <x-icon :name="$place['icon']" class="size-5" />
                    {{ $place['label'] }}
                </a>
            </li>
        @endforeach
    </ul>
</nav>
