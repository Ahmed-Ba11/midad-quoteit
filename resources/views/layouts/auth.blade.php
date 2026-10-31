@extends('layouts.app')

{{--
    Sign in and sign up: a reading room in the afternoon. Sunlight falls from the upper left, an arched
    window casts its light on the wall with the shadow of the رقيم mark standing on its sill, and loose
    pages lie in the corners. The card sits in that light: the form, and beside it a dark tablet (لوح)
    under a lit arch where a verse writes itself. Every layer is decorative (aria-hidden, no clicks).
--}}
@section('backdrop')
    <div class="room" aria-hidden="true">
        <div class="shafts"></div>
        <div class="window">
            <svg viewBox="0 0 600 900">
                <path class="glow softer" d="M60 900 V330 C60 170 180 70 300 30 C420 70 540 170 540 330 V900 Z" />
                <g class="bars soft">
                    <rect x="288" y="40" width="24" height="860" />
                    <rect x="60" y="440" width="480" height="22" />
                    <path d="M60 330 C60 170 180 70 300 30 C420 70 540 170 540 330 L520 330 C520 182 410 92 300 54 C190 92 80 182 80 330 Z" />
                </g>
                <g class="mk"><svg x="110" y="520" width="380" height="380"><use href="#mark" /></svg></g>
            </svg>
        </div>
        <svg class="pages br" viewBox="0 0 360 260">
            <ellipse class="sh" cx="210" cy="200" rx="150" ry="40" />
            <path class="s" d="M40 230 C110 200 200 190 300 205 L360 260 L60 260 Z" />
            <path class="s" d="M70 215 C140 170 230 160 320 180 L360 230 L100 250 Z" />
            <path class="s" d="M110 196 C170 140 250 128 340 150 L360 200 L140 236 Z" />
            <path class="l" d="M150 200 C200 168 260 160 330 172 M150 214 C200 184 262 178 336 188" />
        </svg>
        <svg class="pages bl" viewBox="0 0 360 260">
            <ellipse class="sh" cx="210" cy="200" rx="150" ry="40" />
            <path class="s" d="M40 230 C110 200 200 190 300 205 L360 260 L60 260 Z" />
            <path class="s" d="M80 214 C150 172 240 162 330 182 L360 232 L110 250 Z" />
        </svg>
    </div>
    <div class="glare" aria-hidden="true"></div>
@endsection

@section('content')
    <div class="auth-card mx-auto grid max-w-[940px] px-4 py-10 md:grid-cols-2 md:py-16">
        <section class="rounded-3xl border border-line bg-surface p-7 shadow-xl shadow-black/5 sm:p-10 md:rounded-e-none">
            <h1 class="font-display text-3xl font-bold">@yield('heading')</h1>
            <p class="mt-2 text-muted">@yield('subheading')</p>

            <div class="mt-8">
                @yield('form')
            </div>
        </section>

        @yield('verse')
    </div>
@endsection
