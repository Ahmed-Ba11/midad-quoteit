{{-- Create or edit an anthology. $quoteId (optional) adds that quote to a new one straight away. --}}
<form method="POST" action="{{ $action }}" class="space-y-5 border-t border-line p-6">
    @csrf
    @if ($anthology->exists)
        @method('PUT')
    @endif
    @isset($quoteId)
        <input type="hidden" name="quote_id" value="{{ $quoteId }}">
    @endisset

    <div class="grid gap-5 sm:grid-cols-2">
        <x-input name="title" label="اسم المختارات" hint="من حرفين إلى ٦٠ حرفاً" maxlength="60" required
            :value="$anthology->title" placeholder="أبيات الصبر" />
        <x-input name="description" label="وصف قصير" hint="اختياري، حتى ٢٠٠ حرف" maxlength="200"
            :value="$anthology->description" placeholder="ما أعود إليه حين تضيق" />
    </div>

    <x-palette-picker name="palette" label="لون الغلاف" :selected="$anthology->palette ?? 'sand'" hint="يظهر على غلاف المختارات في ركنك" />

    <label class="flex cursor-pointer items-start gap-3">
        <input type="checkbox" name="is_public" value="1" @checked(old('is_public', $anthology->exists ? $anthology->is_public : true))
            class="mt-1 size-4 accent-[var(--color-accent)]" aria-describedby="is_public-hint">
        <span>
            <span class="font-medium">عامة</span>
            <span id="is_public-hint" class="block text-xs text-muted">يراها زوّار ركنك؛ أزل العلامة لتبقى لك وحدك.</span>
        </span>
    </label>

    <x-button type="submit">{{ $anthology->exists ? 'حفظ التعديلات' : 'أنشئ المختارات' }}</x-button>
</form>
