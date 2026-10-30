@extends('layouts.app')

@section('title', $diwan->exists ? 'تعديل '.$diwan->title : 'ديوان جديد')

@section('content')
    <div class="mx-auto max-w-2xl px-4 py-12">
        <x-back :href="$diwan->exists ? route('diwans.show', $diwan) : route('diwans.index')" :label="$diwan->exists ? $diwan->title : 'دواوين القرّاء'" />

        <h1 class="font-display text-4xl font-black">{{ $diwan->exists ? 'تعديل الديوان' : 'ديوان جديد' }}</h1>
        <p class="mt-2 text-muted">انقل أبيات شاعر تحبه مع مصدرها. بعد الحفظ تضيف الأبيات واحداً واحداً.</p>

        <form method="POST" action="{{ $diwan->exists ? route('diwans.update', $diwan) : route('diwans.store') }}"
            class="mt-8 space-y-6 rounded-3xl border border-line bg-surface p-6 sm:p-8">
            @csrf
            @if ($diwan->exists)
                @method('PUT')
            @endif

            <div class="grid gap-5 sm:grid-cols-[1fr_10rem]">
                <x-input name="poet_name" label="الشاعر" hint="من حرفين إلى ٦٠ حرفاً" maxlength="60" required
                    :value="$diwan->poet_name" placeholder="ابن زيدون" />
                <x-input name="poet_death_year" type="number" label="سنة الوفاة (م)" min="1" :max="now()->year" inputmode="numeric"
                    :value="$diwan->poet_death_year" hint="اتركها فارغة إن كان حيّاً" />
            </div>

            <x-input name="title" label="اسم الديوان" hint="من حرفين إلى ٨٠ حرفاً، مثل: ديوان ابن زيدون، أو: من شعره" maxlength="80" required
                :value="$diwan->title" />

            <x-textarea name="about" label="نبذة" hint="اختياري، حتى ٣٠٠ حرف: من الشاعر؟ ولماذا تحب شعره؟" rows="3" maxlength="300"
                :value="old('about', $diwan->about)" />

            <x-input name="source" label="المصدر" hint="مطلوب، من ٣ إلى ٢٠٠ حرف: الكتاب المطبوع وطبعته، أو رابط الصفحة" maxlength="200" required
                :value="$diwan->source" placeholder="ديوان ابن زيدون، دار صادر، ص ٢٣" />

            <x-palette-picker name="color" label="لون الغلاف" :selected="$diwan->color ?? 'cocoa'" hint="يظهر على غلاف الديوان" />

            <div class="rounded-2xl border border-gold/40 bg-paper p-5">
                <label class="flex cursor-pointer items-start gap-3">
                    <input type="checkbox" name="is_public" value="1" @checked(old('is_public', $diwan->is_public))
                        class="mt-1 size-4 accent-[var(--color-accent)]" aria-describedby="is_public-hint">
                    <span>
                        <span class="flex items-center gap-1.5 font-medium"><x-icon name="globe" class="size-4" /> انشره للجميع</span>
                        <span id="is_public-hint" class="mt-1 block text-xs leading-relaxed text-muted">
                            مسموح فقط إن تُوفّي الشاعر قبل أكثر من ٥٠ سنة (قبل {{ now()->year - \App\Models\Diwan::PROTECTION_YEARS }}م)؛
                            فشعره حينها ملكٌ عام (نظام حماية حقوق المؤلف، المادة ١٩). غير ذلك يبقى دفتراً خاصاً بك، ولك أن تقتبس منه بيتاً أو بيتين مع المصدر.
                        </span>
                    </span>
                </label>
                @error('is_public')
                    <p class="mt-2 text-sm font-medium text-accent">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <x-button type="submit">{{ $diwan->exists ? 'حفظ التعديلات' : 'أنشئ الديوان' }}</x-button>
                <x-button variant="ghost" :href="$diwan->exists ? route('diwans.show', $diwan) : route('diwans.index')">إلغاء</x-button>
            </div>
        </form>

        @if ($diwan->exists)
            <form method="POST" action="{{ route('diwans.destroy', $diwan) }}" class="mt-6 text-center">
                @csrf
                @method('DELETE')
                <button type="submit" class="inline-flex items-center gap-1.5 text-sm text-muted transition hover:text-accent">
                    <x-icon name="trash" class="size-4" /> حذف الديوان وكل أبياته
                </button>
            </form>
        @endif
    </div>
@endsection
