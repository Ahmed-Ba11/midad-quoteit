@extends('layouts.app')

@section('title', 'الرئيسية')

@section('content')
    @php
        $editingId = request()->integer('edit') ?: null;
    @endphp

    <div class="mx-auto max-w-6xl px-4 py-10">
        <header class="mb-8 flex flex-wrap items-end justify-between gap-4">
            <div>
                <h1 class="font-display text-4xl font-bold">أحدث الاقتباســات</h1>
                <p class="mt-2 text-muted">كلماتٌ تستحق أن تُقرأ مرتين، يشاركها أهل الحرف.</p>
            </div>

            @guest
                <x-button :href="route('signup')" icon="plus">انضم وشارك اقتباساً</x-button>
            @endguest
        </header>

        @if ($quotes->isEmpty())
            <x-empty-state title="لا توجد اقتباسات بعد">
                كن أول من يشارك اقتباساً يستحق أن يُقرأ.
            </x-empty-state>
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
@endsection
