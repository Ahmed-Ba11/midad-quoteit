@extends('layouts.app')

@section('title', 'دواوين القرّاء')

@section('content')
    <header class="relative isolate overflow-hidden border-b border-line bg-surface">
        <x-ornament name="girih" class="girih absolute inset-0 -z-10 h-full w-full" />
        <div class="mx-auto max-w-4xl px-6 py-14 text-center">
            <x-ornament name="shamsa" class="mx-auto size-14 text-gold" />
            <h1 class="mt-4 font-display text-4xl font-black sm:text-5xl">دواوين القرّاء</h1>
            <p class="mx-auto mt-4 max-w-2xl font-text text-lg leading-loose text-muted">
                شعراء ليسوا في خزانة الأدب، نقل أبياتهم القرّاء مع مصادرها. لم يراجعها رقيم، فارجع إلى المصدر المذكور في كل ديوان.
            </p>

            <form method="GET" action="{{ route('diwans.index') }}" role="search" class="mx-auto mt-8 flex max-w-lg gap-2">
                <label for="q" class="sr-only">ابحث في دواوين القرّاء</label>
                <input id="q" name="q" type="search" value="{{ $term }}" maxlength="60" aria-describedby="diwans-search-hint"
                    placeholder="اسم الشاعر أو الديوان"
                    class="h-12 min-w-0 flex-1 rounded-full border border-line bg-white px-5 text-ink focus:border-accent focus:outline-none focus:ring-3 focus:ring-accent/20 dark:bg-paper">
                <x-button type="submit" icon="search">بحث</x-button>
            </form>
            <p id="diwans-search-hint" class="mt-2 text-xs text-muted">حتى ٦٠ حرفاً</p>
        </div>
    </header>

    <div class="mx-auto max-w-6xl px-4 py-12">
        {{-- The viewer's own diwans first, private ones included. --}}
        @auth
            <section aria-labelledby="mine-title" class="mb-14">
                <div class="flex flex-wrap items-end justify-between gap-4">
                    <div>
                        <h2 id="mine-title" class="font-display text-2xl font-bold">دواويني</h2>
                        <p class="mt-1 text-sm text-muted">الخاصة منها لا يراها أحد غيرك.</p>
                    </div>
                    <x-button :href="route('diwans.create')" icon="plus">ديوان جديد</x-button>
                </div>
                @if ($mine->isEmpty())
                    <p class="mt-5 rounded-2xl border border-dashed border-line p-6 text-center text-muted">
                        تحب شاعراً ليس في الخزانة؟ أنشئ له ديواناً وانقل أبياته مع مصدرها، ثم صمّم منها في مرسم الحروف.
                    </p>
                @else
                    <ul class="mt-6 grid grid-cols-2 gap-x-5 gap-y-8 sm:grid-cols-4 lg:grid-cols-6">
                        @foreach ($mine as $diwan)
                            <li><x-diwan-tile :diwan="$diwan" /></li>
                        @endforeach
                    </ul>
                @endif
            </section>
        @endauth

        <section aria-labelledby="public-title">
            <h2 id="public-title" class="font-display text-2xl font-bold">{{ $term !== '' ? 'نتائج «'.$term.'»' : 'منشورة للجميع' }}</h2>
            <p class="mt-1 text-sm text-muted">لا يُنشر للجميع إلا ديوان شاعر تُوفّي قبل أكثر من ٥٠ سنة، احتراماً لحقوق المؤلف.</p>

            @if ($diwans->isEmpty())
                <x-empty-state :title="$term !== '' ? 'لا نتائج' : 'لا دواوين منشورة بعد'" icon="scroll" class="mt-6">
                    @if ($term !== '')
                        جرّب اسماً آخر، أو ابحث في <a href="{{ route('library.index', ['q' => $term]) }}" class="text-accent hover:underline">خزانة الأدب</a>.
                    @else
                        كن أول من ينشر ديواناً لشاعر من التراث.
                    @endif
                </x-empty-state>
            @else
                <ul class="mt-6 grid grid-cols-2 gap-x-5 gap-y-8 sm:grid-cols-4 lg:grid-cols-6">
                    @foreach ($diwans as $diwan)
                        <li><x-diwan-tile :diwan="$diwan" show-owner /></li>
                    @endforeach
                </ul>
                <x-pagination :paginator="$diwans" class="mt-10" />
            @endif
        </section>
    </div>
@endsection
