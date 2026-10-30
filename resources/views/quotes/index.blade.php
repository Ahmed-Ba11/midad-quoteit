@extends('layouts.app')

@section('title', 'الرئيسية')

@section('content')
    @php
        $editingId = request()->integer('edit') ?: null;
        $sorts = ['latest' => 'الأحدث', 'likes' => 'الأكثر إعجاباً', 'replies' => 'الأكثر نقاشاً'];
        $keep = array_filter(['q' => $filters['q'], 'sort' => $filters['sort'] === 'latest' ? null : $filters['sort'], 'original' => $filters['original'] ? '1' : null]);
    @endphp

    <div class="mx-auto max-w-6xl px-4 py-10">
        @if ($quoteOfTheDay)
            <x-quote-of-the-day :quote="$quoteOfTheDay" class="mb-12" />
        @endif

        <header class="mb-6 flex flex-wrap items-end justify-between gap-4">
            <div>
                <h1 class="font-display text-4xl font-bold">أحدث الاقتباســات</h1>
                <x-verse layout="paired" class="mt-3 max-w-xl text-muted" sadr="لسانُ الفتى نصفٌ ونصفٌ فؤادُه"
                    ajuz="فلم يبقَ إلا صورةُ اللحمِ والدمِ" />
            </div>

            @guest
                <x-button :href="route('login')" icon="plus">سجّل دخولك لتشارك اقتباساً</x-button>
            @endguest
        </header>

        {{-- Search + sort (GET, so results can be shared as a link) --}}
        <form method="GET" action="{{ route('home') }}" role="search" class="flex flex-col gap-3 sm:flex-row">
            @if ($filters['category'])
                <input type="hidden" name="category" value="{{ $filters['category'] }}">
            @endif
            @if ($filters['original'])
                <input type="hidden" name="original" value="1">
            @endif

            <div class="relative flex-1">
                <label for="q" class="sr-only">ابحث في الاقتباسات</label>
                <x-icon name="search" class="pointer-events-none absolute start-4 top-1/2 -translate-y-1/2 text-muted" />
                <input id="q" name="q" type="search" aria-describedby="search-hint" value="{{ $filters['q'] }}" maxlength="100"
                    placeholder="ابحث في النص أو القائل أو الكتاب…"
                    class="block h-12 w-full rounded-full border border-line bg-surface ps-12 pe-4 text-ink placeholder:text-muted/70 focus:border-accent focus:outline-none focus:ring-3 focus:ring-accent/20">
            </div>

            <div class="flex gap-2">
                <label for="sort" class="sr-only">الترتيب</label>
                <select id="sort" name="sort" aria-describedby="search-hint" onchange="this.form.requestSubmit()"
                    class="h-12 flex-1 rounded-full border border-line bg-surface px-5 text-ink focus:border-accent focus:outline-none focus:ring-3 focus:ring-accent/20">
                    @foreach ($sorts as $value => $label)
                        <option value="{{ $value }}" @selected($filters['sort'] === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <x-button type="submit" class="h-12">بحث</x-button>
            </div>
        </form>

        <p id="search-hint" class="mt-2 text-xs text-muted">ابحث بكلمة من النص أو باسم القائل أو الكتاب (حتى ١٠٠ حرف)، واختر الترتيب.</p>

        {{-- Category chips --}}
        <nav aria-label="التصنيفات" class="mt-5 flex flex-wrap gap-2">
            <x-chip :href="route('home', \Illuminate\Support\Arr::except($keep, 'original'))" :active="$filters['category'] === null && ! $filters['original']">الكل</x-chip>
            <x-chip :href="route('home', [...$keep, 'original' => $filters['original'] ? null : '1'])" :active="$filters['original']">
                <x-icon name="feather" class="size-4" /> بأقلام القرّاء
            </x-chip>
            <span aria-hidden="true" class="mx-1 h-6 w-px self-center bg-line"></span>
            @foreach ($categories as $category)
                <x-chip :href="route('home', [...$keep, 'category' => $category->slug])"
                    :active="$filters['category'] === $category->slug">{{ $category->name }}</x-chip>
            @endforeach
        </nav>

        <div class="mt-8">
            @if ($quotes->isEmpty())
                @if ($filters['q'] !== '' || $filters['category'] || $filters['original'])
                    <x-empty-state title="لا نتائج" icon="search">
                        لم نجد اقتباساً يطابق بحثك. جرّب كلمة أخرى أو
                        <a href="{{ route('home') }}" class="font-medium text-accent hover:underline">اعرض الكل</a>.
                    </x-empty-state>
                    @if ($filters['q'] !== '')
                        <x-search-elsewhere :term="$filters['q']" here="quotes" class="mt-8" />
                    @endif
                @else
                    <x-empty-state title="لا توجد اقتباسات بعد">
                        كن أول من يشارك اقتباساً يستحق أن يُقرأ.
                    </x-empty-state>
                @endif
            @else
                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($quotes as $quote)
                        @if ($quote->id === $editingId && auth()->user()?->can('update', $quote))
                            <form method="POST" action="{{ route('edit_quote', $quote) }}"
                                class="flex flex-col gap-4 rounded-3xl border-2 border-accent bg-surface p-5">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="_form" value="edit">

                                <x-textarea name="content" id="content-{{ $quote->id }}" label="تعديل الاقتباس"
                                    hint="من ٣ إلى ١٥٥ حرفاً" rows="5" minlength="3" maxlength="155" required autofocus
                                    :value="old('_form') === 'edit' ? old('content') : $quote->content"
                                    :show-error="old('_form') === 'edit'" />

                                <div class="flex gap-2">
                                    <x-button type="submit">حفظ</x-button>
                                    <x-button variant="ghost" :href="request()->fullUrlWithoutQuery('edit')">إلغاء</x-button>
                                </div>
                            </form>
                        @else
                            <x-quote-card :quote="$quote" />
                        @endif
                    @endforeach
                </div>

                <x-pagination :paginator="$quotes" class="mt-10" />
            @endif
        </div>
    </div>
@endsection
