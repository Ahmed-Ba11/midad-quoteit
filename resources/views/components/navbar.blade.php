@props(['page' => ''])

<nav class="relative sticky top-0 z-50 border-b border-white/30 bg-[#10081F] shadow-sm backdrop-blur-md">
    <p class="site-name">QuoteIt</p>
    <ul class="mx-auto flex max-w-7xl items-center gap-6 px-4 py-1">

        @if (($page ?? '') === 'Sign Up')
        <li><a href="/login" class="btn-primary">Log In</a></li>
        @elseif(($page ?? '') === 'Log In')
        <li><a href="/signup" class="btn-primary">Sign Up</a></li>
        @else
        @auth
        <form method="get" action="/logout">

            <li><button type="submit" class="btn-primary">Log Out</button></li>
        </form>
        <li>
            <a href="/profile"
                class="block rounded-full focus:outline-none focus-visible:ring-4 focus-visible:ring-tuwaiq-purple/30">
                <img src="{{ asset('images/profile.jpeg') }}" alt="Profile"
                    class="h-10 w-10 rounded-full object-cover ring-2 ring-tuwaiq-purple ring-offset-2 transition hover:scale-105">
            </a>
        </li>
        <li>
            <p class="text-white">{{auth()->user()->name}} </p>
        </li>
        @endauth
        @guest
        <a href="/login" class="btn-primary">Log In</a>
        @endguest


        @endif
        <li class="ml-auto"><img src="{{ asset('images/logo.png') }}" class=" h-16 w-auto" alt="Logo"></li>


    </ul>
</nav>