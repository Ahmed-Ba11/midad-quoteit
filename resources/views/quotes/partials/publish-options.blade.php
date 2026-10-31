{{--
    "من يرى الاقتباس؟" + «أضفه إلى» — for every quote, typed or picked from the library.
    $defaults carries the draft from «شارك اقتباساً»; $useOld refills the form after a failed submit.
--}}
@php
    $useOld ??= true;
    $anthologies ??= collect();
    $defaults ??= [];
    $old = fn (string $field, $default) => $useOld ? old($field, $defaults[$field] ?? $default) : ($defaults[$field] ?? $default);
    $chip = 'inline-flex h-11 cursor-pointer items-center gap-1.5 rounded-full border border-line bg-paper px-4 text-sm transition hover:border-accent has-[:checked]:border-accent has-[:checked]:bg-accent has-[:checked]:text-paper has-[:focus-visible]:ring-3 has-[:focus-visible]:ring-accent/30';
    $choice = 'flex h-full cursor-pointer flex-col gap-1 rounded-2xl border border-line bg-paper p-3 transition hover:border-accent has-[:checked]:border-accent has-[:checked]:bg-accent-soft has-[:focus-visible]:ring-3 has-[:focus-visible]:ring-accent/30';
@endphp

{{-- Who sees it: public (everyone, everywhere) or private (my corner only). --}}
<fieldset class="mt-5">
    <legend class="mb-2 text-sm font-medium">من يرى الاقتباس؟</legend>
    <div class="grid grid-cols-2 gap-2">
        <label class="{{ $choice }}">
            <input type="radio" name="is_public" value="1" class="sr-only" @checked((string) $old('is_public', (int) $quote->is_public) === '1')>
            <span class="flex items-center gap-1.5 text-sm font-medium"><x-icon name="globe" class="size-4 text-accent" /> عام</span>
            <span class="text-xs leading-snug text-muted">في الرئيسية ورفوف القرّاء وركنك؛ يراه أي قارئ ويعجب به ويحفظه ويشاركه.</span>
        </label>
        <label class="{{ $choice }}">
            <input type="radio" name="is_public" value="0" class="sr-only" @checked((string) $old('is_public', (int) $quote->is_public) === '0')>
            <span class="flex items-center gap-1.5 text-sm font-medium"><x-icon name="lock" class="size-4 text-accent" /> خاص</span>
            <span class="text-xs leading-snug text-muted">في ركنك وحدك؛ لا يظهر في الرئيسية ولا في صفحة الكتاب، ولا يراه غيرك.</span>
        </label>
    </div>
</fieldset>

{{-- «أضفه إلى»: file it straight into one of my anthologies, or nowhere; each is a clear chip. --}}
@if ($anthologies->isNotEmpty())
    @php $chosenAnthology = (string) $old('anthology_id', ''); @endphp
    <fieldset class="mt-5">
        <legend class="mb-2 flex items-center gap-1.5 text-sm font-medium"><x-icon name="layers" class="size-4 text-accent" /> أضفه إلى</legend>
        <div class="flex flex-wrap gap-2">
            <label class="{{ $chip }}">
                <input type="radio" name="anthology_id" value="" class="sr-only" @checked($chosenAnthology === '')>
                لا شيء
            </label>
            @foreach ($anthologies as $anthology)
                <label class="{{ $chip }}">
                    <input type="radio" name="anthology_id" value="{{ $anthology->id }}" class="sr-only" @checked($chosenAnthology === (string) $anthology->id)>
                    <x-icon name="layers" class="size-3.5 opacity-60" /> {{ $anthology->title }}
                </label>
            @endforeach
        </div>
        <p class="mt-1.5 text-xs text-muted">اختياري: يُحفظ في مختاراتك التي تختارها، ويبقى في ركنك على كل حال</p>
    </fieldset>
@endif
