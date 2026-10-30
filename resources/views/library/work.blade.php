@extends('layouts.app')

@section('title', $work->title)

@section('content')
    {{-- The cover: leather in the work's own colour, stamped with a shamsa. --}}
    <header class="relative isolate overflow-hidden text-center" style="background-color: {{ $work->color }}; color: {{ $work->ink }};">
        <x-ornament name="girih" class="girih absolute inset-0 -z-10 h-full w-full" />
        <div aria-hidden="true" class="pointer-events-none absolute inset-3 border border-current/30 sm:inset-5"></div>

        <div class="mx-auto max-w-3xl px-6 py-16">
            <x-ornament name="shamsa" class="mx-auto size-20" />
            <p class="mt-5 text-sm opacity-75">{{ $work->typeLabel() }}</p>
            <h1 class="mt-1 font-display text-4xl font-black sm:text-5xl">{{ $work->title }}</h1>
            <a href="{{ route('library.poet', $work->poet) }}" class="mt-3 inline-block text-lg opacity-90 hover:underline">{{ $work->poet->name }} · {{ $work->poet->years }}</a>
        </div>
    </header>

    <div class="mx-auto max-w-4xl px-4 py-12">
        <x-back :href="route('library.index').'#shelf'" label="رفّ الدواوين" />

        <p class="mb-6 text-muted">{{ $work->verses->count() }} بيتاً مختاراً. اختر بيتاً أو بيتين لتصمّمهما في مرسم الحروف.</p>

        {{-- Verses set as a printed diwan: صدر and عجز side by side, ruled like a manuscript page. --}}
        <ol class="manuscript-page divide-y divide-[#A8873F]/25 rounded-2xl px-5 py-8 sm:px-12">
            @foreach ($work->verses as $verse)
                <li class="py-6">
                    <x-verse layout="paired" :sadr="$verse->sadr" :ajuz="$verse->ajuz" class="text-xl" />
                    <div class="mt-3 flex flex-wrap items-center justify-center gap-x-4 gap-y-2">
                        @if ($verse->occasion)
                            <span class="text-xs text-[#7A7068]">{{ config('library.occasions.'.$verse->occasion) }}</span>
                        @endif
                        @if ($verse->source_url)
                            <x-external-link :href="$verse->source_url" class="text-xs text-[#7A7068]">القصيدة كاملة</x-external-link>
                        @endif
                        <x-pick-button :id="$verse->id" :text="$verse->sadr.' / '.$verse->ajuz" class="text-[#8A3324]" />
                    </div>
                    <x-verse-explanation :verse="$verse" class="mx-auto mt-3 max-w-2xl" />
                </li>
            @endforeach
        </ol>

        <x-read-more :poet="$work->poet" :work="$work" class="mt-8" />

        @if ($work->poet->works->count() > 1)
            <section class="mt-12">
                <h2 class="font-display text-xl font-bold">من آثار {{ $work->poet->name }} أيضاً</h2>
                <div class="mt-4 flex flex-wrap gap-2">
                    @foreach ($work->poet->works->except($work->id) as $other)
                        <x-chip :href="route('library.work', $other)">{{ $other->title }}</x-chip>
                    @endforeach
                </div>
            </section>
        @endif

        <p class="mt-12 text-center text-xs text-muted">النصوص من ويكي مصدر (CC BY-SA).</p>
    </div>

    <x-selection-bar />
@endsection
