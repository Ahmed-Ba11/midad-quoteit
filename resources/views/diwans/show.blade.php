@extends('layouts.app')

@section('title', $diwan->title)

@section('content')
    @php $colors = $diwan->colors(); @endphp

    {{-- The cover in the reader's chosen leather, like the library's diwans. --}}
    <header class="relative isolate overflow-hidden text-center" style="background: {{ $colors['bg'] }}; color: {{ $colors['text'] }}">
        <x-ornament name="girih" class="girih absolute inset-0 -z-10 h-full w-full" style="color: {{ $colors['highlight'] }}" />
        <div aria-hidden="true" class="pointer-events-none absolute inset-3 border sm:inset-5" style="border-color: {{ $colors['highlight'] }}55"></div>

        <div class="mx-auto max-w-3xl px-6 py-16">
            <x-ornament name="shamsa" class="mx-auto size-16" style="color: {{ $colors['highlight'] }}" />
            <p class="mt-4 inline-flex items-center gap-1.5 text-sm opacity-80">
                @if ($diwan->is_public)
                    <x-icon name="globe" class="size-4" /> ديوان قارئ · منشور للجميع
                @else
                    <x-icon name="lock" class="size-4" /> دفتر خاص · لا يراه غيرك
                @endif
            </p>
            <h1 class="mt-2 font-display text-4xl font-black sm:text-5xl" style="color: {{ $colors['highlight'] }}">{{ $diwan->title }}</h1>
            <p class="mt-3 text-lg opacity-90">{{ $diwan->poet_name }}@if ($diwan->poet_death_year) · ت {{ $diwan->poet_death_year }}م @endif</p>
        </div>
    </header>

    <div class="mx-auto max-w-4xl px-4 py-12">
        <x-back :href="route('diwans.index')" label="دواوين القرّاء" />

        {{-- Provenance: who copied it, from where, and that رقيم has not reviewed it. --}}
        <aside class="rounded-3xl border border-line bg-surface p-6">
            @if ($diwan->about)
                <p class="mb-3 font-text text-lg leading-loose">{{ $diwan->about }}</p>
            @endif
            <dl class="grid gap-2 text-sm sm:grid-cols-[auto_1fr] sm:gap-x-4">
                <dt class="text-muted">المصدر</dt>
                <dd>
                    @if ($diwan->sourceUrl())
                        <a href="{{ $diwan->sourceUrl() }}" target="_blank" rel="nofollow ugc noopener noreferrer" dir="ltr"
                            class="inline-flex items-center gap-1 text-accent hover:underline">{{ \Illuminate\Support\Str::limit(rawurldecode(preg_replace('#^https?://(www\.)?#i', '', $diwan->source)), 60) }}
                            <x-icon name="external" class="size-3.5" /></a>
                    @else
                        {{ $diwan->source }}
                    @endif
                </dd>
                <dt class="text-muted">نقله</dt>
                <dd><a href="{{ route('profile.show', $diwan->user) }}" class="hover:text-accent">{{ $diwan->user->name }}</a></dd>
            </dl>
            <p class="mt-4 flex items-start gap-2 rounded-xl bg-paper p-3 text-xs leading-relaxed text-muted">
                <x-icon name="book-open" class="mt-0.5 size-4 shrink-0" />
                مساهمة قارئ لم يراجعها رقيم؛ قد يقع في النقل خطأ أو اختلاف رواية، فارجع إلى المصدر قبل الاستشهاد.
            </p>

            @can('update', $diwan)
                <div class="mt-4">
                    <x-button variant="secondary" :href="route('diwans.edit', $diwan)" icon="pencil">تعديل بيانات الديوان</x-button>
                </div>
            @endcan
        </aside>

        <h2 id="verses" class="mt-12 scroll-mt-24 font-display text-2xl font-bold">الأبيات <span class="text-base font-normal text-muted">({{ $diwan->verses->count() }})</span></h2>

        @if ($diwan->verses->isEmpty())
            <p class="mt-4 rounded-2xl border border-dashed border-line p-8 text-center text-muted">لا أبيات بعد.</p>
        @else
            <ol class="manuscript-page mt-5 divide-y divide-[#A8873F]/25 rounded-2xl px-5 py-8 sm:px-12">
                @foreach ($diwan->verses as $verse)
                    <li class="py-6">
                        @if ($verse->ajuz)
                            <x-verse layout="paired" :sadr="$verse->sadr" :ajuz="$verse->ajuz" class="text-xl" />
                        @else
                            <p class="hemistich text-center font-text text-xl leading-[1.9]">{{ $verse->sadr }}</p>
                        @endif
                        @if ($verse->note)
                            <p class="mx-auto mt-2 max-w-xl text-center text-sm text-[#7A7068]">{{ $verse->note }}</p>
                        @endif
                        <div class="mt-3 flex flex-wrap items-center justify-center gap-x-4 gap-y-2 text-sm">
                            @auth
                                <a href="{{ route('quotes.create', ['diwan_verse' => $verse->id]) }}" class="inline-flex min-h-11 items-center gap-1 rounded-full px-3 text-[#8A3324] hover:bg-[#8A3324]/10">
                                    <x-icon name="palette" class="size-4" /> صمّمه في مرسم الحروف
                                </a>
                            @endauth
                            @can('update', $diwan)
                                <form method="POST" action="{{ route('diwans.verses.destroy', [$diwan, $verse]) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-flex min-h-11 items-center gap-1 rounded-full px-3 text-[#7A7068] transition hover:bg-[#8A3324]/10 hover:text-[#8A3324]">
                                        <x-icon name="trash" class="size-4" /> احذفه
                                    </button>
                                </form>
                            @endcan
                        </div>
                    </li>
                @endforeach
            </ol>
        @endif

        @can('update', $diwan)
            <form id="add-verse" method="POST" action="{{ route('diwans.verses.store', $diwan) }}"
                class="mt-8 scroll-mt-24 space-y-5 rounded-3xl border border-line bg-surface p-6 sm:p-8">
                @csrf
                <h2 class="font-display text-xl font-bold">أضف بيتاً</h2>
                <div class="grid gap-5 sm:grid-cols-2">
                    <x-input name="sadr" label="الصدر" hint="مطلوب، حتى ١٢٠ حرفاً؛ انقله بتشكيله كما في المصدر" maxlength="120" required />
                    <x-input name="ajuz" label="العجز" hint="اختياري لشعر التفعيلة، حتى ١٢٠ حرفاً" maxlength="120" />
                </div>
                <x-input name="note" label="ملاحظة" hint="اختياري، حتى ٢٠٠ حرف: مناسبة البيت أو رقم الصفحة" maxlength="200" />
                <x-button type="submit" icon="plus">أضف البيت</x-button>
            </form>
        @endcan
    </div>
@endsection
