@extends('layouts.app')

@section('title', $poet->name)

@section('content')
    <div class="mx-auto max-w-5xl px-4 py-12">
        <nav aria-label="مسار التصفح" class="mb-8 text-sm text-muted">
            <a href="{{ route('library.index') }}" class="hover:text-accent">خزانة الأدب</a>
            <span aria-hidden="true" class="mx-1">/</span>
            <a href="{{ route('library.index') }}#poets" class="hover:text-accent">رواق الشعراء</a>
        </nav>

        {{-- The poet's arch, filled with their colour, beside the biography. --}}
        <header class="flex flex-col items-center gap-8 sm:flex-row sm:items-end">
            <div class="arch relative flex shrink-0 flex-col items-center border border-transparent px-3 pb-6 pt-10 text-center"
                aria-pressed="true" style="--poet: {{ $poet->color }}">
                <x-ornament name="star" class="size-9 text-[#E2C27E]" />
                <span class="mt-auto font-display text-lg font-bold leading-snug">{{ $poet->name }}</span>
                <span class="mt-1 text-xs opacity-75">{{ $poet->years }}</span>
            </div>

            <div class="text-center sm:text-start">
                <p class="text-sm text-gold">{{ $poet->eraLabel() }}@if ($poet->region) · {{ $poet->region }}@endif</p>
                <h1 class="mt-1 font-display text-5xl font-black">{{ $poet->name }}</h1>
                <p class="mt-4 max-w-2xl font-text text-lg leading-loose text-muted">{{ $poet->bio }}</p>

                {{-- شعرائي: keep this poet in my corner. --}}
                <div class="mt-5 flex flex-wrap items-center justify-center gap-3 sm:justify-start">
                    @auth
                        <form method="POST" action="{{ route('poets.favorite', $poet) }}">
                            @csrf
                            <x-button type="submit" :variant="$isFavorite ? 'secondary' : 'primary'" icon="heart" aria-pressed="{{ $isFavorite ? 'true' : 'false' }}">
                                {{ $isFavorite ? 'في شعرائي' : 'أضف إلى شعرائي' }}
                            </x-button>
                        </form>
                    @else
                        <x-button :href="route('login')" variant="secondary" icon="heart">سجّل دخولك لتضيفه إلى شعرائك</x-button>
                    @endauth
                    @if ($poet->fans_count)
                        <span class="text-sm text-muted">في ركن {{ $poet->fans_count }} من القرّاء</span>
                    @endif
                </div>
            </div>
        </header>

        <x-read-more :poet="$poet" class="mt-10" />

        @foreach ($poet->works as $work)
            <section class="mt-14" aria-labelledby="work-{{ $work->id }}">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <h2 id="work-{{ $work->id }}" class="flex items-center gap-3 font-display text-2xl font-bold">
                        <span aria-hidden="true" class="h-8 w-3 rounded-sm" style="background: {{ $work->color }}"></span>
                        {{ $work->title }}
                    </h2>
                    <a href="{{ route('library.work', $work) }}" class="text-sm text-accent hover:underline">صفحة {{ $work->typeLabel() }}</a>
                </div>

                <ol class="mt-5 divide-y divide-line rounded-3xl border border-line bg-surface px-5 sm:px-8">
                    @foreach ($work->verses as $verse)
                        <li class="py-6">
                            <x-verse layout="paired" :sadr="$verse->sadr" :ajuz="$verse->ajuz" class="text-lg" />
                            <div class="mt-3 flex flex-wrap items-center justify-center gap-x-4 gap-y-2">
                                @if ($verse->source_url)
                                    <x-external-link :href="$verse->source_url" class="text-xs text-muted">القصيدة كاملة</x-external-link>
                                @endif
                                <x-pick-button :id="$verse->id" :text="$verse->sadr.' / '.$verse->ajuz" class="text-accent" />
                            </div>
                            <x-verse-explanation :verse="$verse" class="mx-auto mt-3 max-w-2xl" />
                        </li>
                    @endforeach
                </ol>
            </section>
        @endforeach

        <p class="mt-12 text-center text-xs text-muted">النصوص من ويكي مصدر (CC BY-SA).</p>
    </div>

    <x-selection-bar />
@endsection
