@extends('layouts.app')

@section('title', $anthology->title)

@section('content')
    @php $colors = $anthology->colors(); @endphp

    {{-- The anthology's cover, in its own palette. --}}
    <header class="relative isolate overflow-hidden" style="background: {{ $colors['bg'] }}; color: {{ $colors['text'] }}">
        <x-ornament name="girih" class="girih absolute inset-0 -z-10 h-full w-full" style="color: {{ $colors['highlight'] }}" />
        <div aria-hidden="true" class="pointer-events-none absolute inset-3 rounded-2xl border sm:inset-5" style="border-color: {{ $colors['highlight'] }}55"></div>

        <div class="mx-auto max-w-3xl px-6 py-16 text-center">
            <p class="inline-flex items-center gap-1.5 text-sm opacity-80">
                <x-icon name="layers" class="size-4" /> مختارات
                @unless ($anthology->is_public)
                    · <x-icon name="lock" class="size-3.5" /> خاصة بك
                @endunless
            </p>
            <h1 class="mt-2 font-display text-4xl font-black sm:text-5xl" style="color: {{ $colors['highlight'] }}">{{ $anthology->title }}</h1>
            @if ($anthology->description)
                <p class="mx-auto mt-3 max-w-xl font-text text-lg opacity-90">{{ $anthology->description }}</p>
            @endif
            <p class="mt-4 text-sm opacity-80">
                جمعها <a href="{{ route('profile.show', $anthology->user) }}" class="font-medium underline-offset-4 hover:underline">{{ $anthology->user->name }}</a>
                · {{ $anthology->quotes_count }} اقتباس
            </p>
        </div>
    </header>

    <div class="mx-auto max-w-6xl px-4 py-10">
        @can('update', $anthology)
            <details class="group mb-8 rounded-3xl border border-line bg-surface" @if ($errors->any()) open @endif>
                <summary class="flex cursor-pointer list-none items-center gap-2 px-6 py-4 font-medium [&::-webkit-details-marker]:hidden">
                    <x-icon name="pencil" class="size-4" /> تعديل المختارات
                </summary>
                @include('anthologies.partials.form', ['action' => route('anthologies.update', $anthology)])
                <form method="POST" action="{{ route('anthologies.destroy', $anthology) }}" class="border-t border-line px-6 py-4">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="inline-flex items-center gap-1.5 text-sm text-muted transition hover:text-accent">
                        <x-icon name="trash" class="size-4" /> حذف المختارات (تبقى الاقتباسات نفسها)
                    </button>
                </form>
            </details>
        @endcan

        @if ($quotes->isEmpty())
            <x-empty-state title="المختارات فارغة" icon="layers">
                افتح أي اقتباس يعجبك واضغط «أضف إلى مختاراتي».
                <a href="{{ route('home') }}" class="mt-3 block text-accent hover:underline">تصفّح الاقتباسات</a>
            </x-empty-state>
        @else
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($quotes as $quote)
                    <div class="flex flex-col">
                        <x-quote-card :quote="$quote" class="flex-1" />
                        @can('update', $anthology)
                            <form method="POST" action="{{ route('anthologies.toggle', [$anthology, $quote]) }}" class="mt-2 text-center">
                                @csrf
                                <button type="submit" class="text-xs text-muted transition hover:text-accent">أخرجه من المختارات</button>
                            </form>
                        @endcan
                    </div>
                @endforeach
            </div>

            <x-pagination :paginator="$quotes" class="mt-10" />
        @endif
    </div>
@endsection
