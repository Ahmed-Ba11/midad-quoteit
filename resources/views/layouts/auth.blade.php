@extends('layouts.app')

@section('content')
    <div class="mx-auto grid max-w-5xl px-4 py-10 md:grid-cols-2 md:py-16">
        <section class="rounded-3xl border border-line bg-surface p-7 sm:p-10 md:rounded-e-none">
            <h1 class="font-display text-3xl font-bold">@yield('heading')</h1>
            <p class="mt-2 text-muted">@yield('subheading')</p>

            <div class="mt-8">
                @yield('form')
            </div>
        </section>

        <aside class="relative hidden items-center justify-center overflow-hidden rounded-3xl rounded-s-none bg-[#1C1A18] p-10 text-[#F2ECE2] md:flex">
            <div class="pointer-events-none absolute inset-4 rounded-2xl border border-[#C9A45C]/30"></div>
            <div class="pointer-events-none absolute inset-6 rounded-xl border border-[#C9A45C]/15"></div>
            @yield('verse')
        </aside>
    </div>
@endsection
