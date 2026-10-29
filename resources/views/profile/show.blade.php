@extends('layouts.app')

@section('title', $user->name)

@section('content')
    @php
        $tabClass = 'inline-flex h-11 items-center border-b-2 px-4 text-sm transition';
    @endphp

    <div class="mx-auto max-w-6xl px-4 py-10">
        <header class="flex flex-col items-center gap-6 rounded-3xl border border-line bg-surface p-8 text-center sm:flex-row sm:text-start">
            <span class="grid size-24 shrink-0 place-items-center rounded-full bg-accent-soft font-display text-4xl font-bold text-accent">
                {{ mb_substr($user->name, 0, 1) }}
            </span>

            <div class="min-w-0 flex-1">
                <h1 class="font-display text-3xl font-bold">{{ $user->name }}</h1>

                @if ($isOwner)
                    <form method="POST" action="{{ route('profile.bio') }}" class="mt-3 flex flex-col gap-2 sm:flex-row sm:items-start">
                        @csrf
                        @method('PATCH')
                        <div class="flex-1">
                            <x-input name="bio" label="نبذة عنك" hint="اختياري، حتى ١٦٠ حرفاً" maxlength="160"
                                :value="$user->bio" placeholder="قارئ يحب الشعر الجاهلي…" />
                        </div>
                        <x-button type="submit" variant="secondary" class="sm:mt-7">حفظ</x-button>
                    </form>
                @elseif ($user->bio)
                    <p class="mt-2 text-muted">{{ $user->bio }}</p>
                @endif
            </div>

            <dl class="grid grid-cols-3 gap-6">
                @foreach ($stats as $label => $value)
                    <div class="text-center">
                        <dt class="text-xs text-muted">{{ $label }}</dt>
                        <dd class="font-display text-3xl font-bold">{{ $value }}</dd>
                    </div>
                @endforeach
            </dl>
        </header>

        <nav aria-label="أقسام الملف" class="mt-8 flex border-b border-line">
            <a href="{{ route('profile.show', $user) }}" @if ($tab === 'quotes') aria-current="page" @endif
                @class([$tabClass, 'border-accent font-bold text-accent' => $tab === 'quotes', 'border-transparent text-muted hover:text-ink' => $tab !== 'quotes'])>
                {{ $isOwner ? 'اقتباساتي' : 'اقتباساته' }}
            </a>
            @if ($isOwner)
                <a href="{{ route('profile.show', [$user, 'tab' => 'saved']) }}" @if ($tab === 'saved') aria-current="page" @endif
                    @class([$tabClass, 'border-accent font-bold text-accent' => $tab === 'saved', 'border-transparent text-muted hover:text-ink' => $tab !== 'saved'])>
                    المحفوظات
                </a>
                <a href="{{ route('profile.show', [$user, 'tab' => 'books']) }}" @if ($tab === 'books') aria-current="page" @endif
                    @class([$tabClass, 'border-accent font-bold text-accent' => $tab === 'books', 'border-transparent text-muted hover:text-ink' => $tab !== 'books'])>
                    كتبي
                </a>
            @endif
        </nav>

        <div class="mt-8">
            @if ($tab === 'books')
                {{-- كتبي: visible to its owner only. --}}
                @forelse (config('quotes.reading_statuses') as $status => $label)
                    @if ($shelf->has($status))
                        <section class="mb-10" aria-label="{{ $label }}">
                            <h2 class="font-display text-xl font-bold">{{ $label }} <span class="text-base font-normal text-muted">({{ $shelf[$status]->count() }})</span></h2>
                            <ul class="mt-4 grid grid-cols-2 gap-x-5 gap-y-8 sm:grid-cols-4 lg:grid-cols-6">
                                @foreach ($shelf[$status] as $book)
                                    <li><x-book-tile :book="$book" /></li>
                                @endforeach
                            </ul>
                        </section>
                    @endif
                @empty
                @endforelse

                @if ($shelf->isEmpty())
                    <x-empty-state title="رفّك فارغ" icon="book-open">
                        افتح صفحة أي كتاب في <a href="{{ route('books.index') }}" class="text-accent hover:underline">رفوف القرّاء</a> واختر: أقرؤه الآن، أو قرأته، أو أريد قراءته.
                    </x-empty-state>
                @endif
            @elseif ($quotes->isEmpty())
                <x-empty-state :title="$tab === 'saved' ? 'لا محفوظات بعد' : 'لا اقتباسات بعد'" :icon="$tab === 'saved' ? 'bookmark' : 'feather'">
                    {{ $tab === 'saved' ? 'احفظ ما يعجبك من الاقتباسات لتجده هنا.' : ($isOwner ? 'شارك أول اقتباس لك من زر «شارك اقتباساً».' : 'لم يشارك اقتباسات بعد.') }}
                </x-empty-state>
            @else
                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($quotes as $quote)
                        <x-quote-card :quote="$quote" />
                    @endforeach
                </div>

                <x-pagination :paginator="$quotes" class="mt-10" />
            @endif
        </div>
    </div>
@endsection
