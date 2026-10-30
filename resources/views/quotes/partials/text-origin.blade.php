{{--
    "ما نوع النص؟" + "من يرى الاقتباس؟" + "طريقة العرض" — shared by the quick-share window and مرسم الحروف.
    The text kind decides what is required (QuoteRequest::withValidator):
      quoted    — someone else's words: the author and the source's name are required; a link is optional;
      original  — my own writing: signed with my signature, no source;
      anonymous — a proverb or saying with no known author: source optional.
    $prefix keeps ids unique when both forms are on one page; $useOld refills the form after a failed submit.
--}}
@php
    $prefix ??= '';
    $useOld ??= true;
    $withBook ??= false;
    $old = fn (string $field, $default) => $useOld ? old($field, $default) : $default;
    $currentKind = $old('kind', match (true) {
        (bool) $quote->is_original => 'original',
        filled($quote->author) || ! $quote->exists => 'quoted',
        default => 'anonymous',
    });
    $mySignature = $quote->is_original && filled($quote->author) ? $quote->author : 'بقلم '.auth()->user()->name;
    $choice = 'flex h-full cursor-pointer flex-col gap-1 rounded-2xl border border-line bg-paper p-3 transition hover:border-accent has-[:checked]:border-accent has-[:checked]:bg-accent-soft has-[:focus-visible]:ring-3 has-[:focus-visible]:ring-accent/30';
    $kinds = [
        'quoted' => ['icon' => 'book-open', 'title' => 'نقلته عن غيري', 'text' => 'بيت شاعر أو جملة كاتب'],
        'original' => ['icon' => 'feather', 'title' => 'من تأليفي', 'text' => 'كتبته أنا بقلمي'],
        'anonymous' => ['icon' => 'message', 'title' => 'مأثور', 'text' => 'مثلٌ لا يُعرف قائله'],
    ];
@endphp

<fieldset data-text-origin class="space-y-4">
    <legend class="mb-2 text-sm font-medium">ما نوع النص؟</legend>
    <div class="grid grid-cols-3 gap-2">
        @foreach ($kinds as $key => $kind)
            <label class="{{ $choice }}">
                <input type="radio" name="kind" value="{{ $key }}" class="sr-only" @checked($currentKind === $key)>
                <x-icon :name="$kind['icon']" class="size-4 text-accent" />
                <span class="text-sm font-medium">{{ $kind['title'] }}</span>
                <span class="text-[11px] leading-snug text-muted">{{ $kind['text'] }}</span>
            </label>
        @endforeach
    </div>

    <div data-show-for="quoted">
        <x-input name="author" :id="$prefix.'author'" label="القائل *" maxlength="100" :value="$quote->is_original ? null : $quote->author"
            :use-old="$useOld" :show-error="$useOld" data-required-for="quoted"
            hint="مطلوب: اسم الشاعر أو الكاتب، حتى ١٠٠ حرف" placeholder="بدر بن عبدالمحسن" />
    </div>

    <div data-show-for="original">
        <x-input name="signature" :id="$prefix.'signature'" label="توقيعك" maxlength="100" :value="$mySignature"
            :use-old="$useOld" :show-error="$useOld"
            hint="يظهر تحت النص؛ اتركه «بقلم {{ auth()->user()->name }}» أو اكتبه كما تحب، حتى ١٠٠ حرف" />
    </div>

    @if ($withBook)
        <div data-show-for="quoted original" class="space-y-4">
            <x-input name="book" :id="$prefix.'book'" label="الكتاب أو الديوان" maxlength="150" :value="$quote->book" list="book-titles"
                hint="اختياري، حتى ١٥٠ حرفاً؛ يجمع اقتباسك مع اقتباسات القرّاء من الكتاب نفسه في رفوف القرّاء" />
            <datalist id="book-titles">
                @foreach ($bookTitles as $title)
                    <option value="{{ $title }}"></option>
                @endforeach
            </datalist>

            <div class="grid grid-cols-2 gap-3">
                <x-input name="page" type="number" label="الصفحة" min="1" max="9999" inputmode="numeric"
                    :value="$quote->page" hint="اختياري، من ١ إلى ٩٩٩٩" />
                <div>
                    <label for="book_genre" class="mb-1.5 block text-sm font-medium">نوع الكتاب</label>
                    <select id="book_genre" name="book_genre" aria-describedby="book_genre-hint"
                        class="block h-12 w-full rounded-xl border border-line bg-white px-3 text-ink focus:border-accent focus:outline-none focus:ring-3 focus:ring-accent/20 dark:bg-surface">
                        <option value="">—</option>
                        @foreach (config('quotes.book_genres') as $key => $label)
                            <option value="{{ $key }}" @selected(old('book_genre', $quote->bookRecord?->genre) === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <p id="book_genre-hint" class="mt-1.5 text-xs text-muted">اختياري، يرتّب الكتاب في الرفوف</p>
                </div>
            </div>
        </div>
    @endif

    <div data-show-for="quoted anonymous">
        <x-input name="source" :id="$prefix.'source'" maxlength="200" :value="$quote->source"
            :label="$withBook ? 'المصدر' : 'المصدر *'" :use-old="$useOld" :show-error="$useOld"
            :data-required-for="$withBook ? null : 'quoted'"
            placeholder="ديوان، كتاب، صحيفة، موقع، حساب…"
            :hint="$withBook ? 'مطلوب لمن ينقل عن غيره إن لم يذكر الكتاب؛ اسمٌ لا رابط، حتى ٢٠٠ حرف' : 'مطلوب لمن ينقل عن غيره: اسم الديوان أو الكتاب أو الصحيفة، لا رابط'" />
    </div>

    <x-input name="source_url" :id="$prefix.'source_url'" type="url" dir="ltr" maxlength="255" :value="$quote->source_url"
        label="رابط لإكمال القراءة" :use-old="$useOld" :show-error="$useOld" placeholder="https://"
        hint="اختياري؛ يرى القرّاء زر «أكمل القراءة» باسم المصدر، لا الرابط نفسه" />
</fieldset>

{{-- Who sees it: public (everyone, everywhere) or private (my corner only). --}}
<fieldset class="mt-5">
    <legend class="mb-2 text-sm font-medium">من يرى الاقتباس؟</legend>
    <div class="grid grid-cols-2 gap-2">
        <label class="{{ $choice }}">
            <input type="radio" name="is_public" value="1" class="sr-only" @checked((string) $old('is_public', (int) $quote->is_public) === '1')>
            <span class="flex items-center gap-1.5 text-sm font-medium"><x-icon name="globe" class="size-4 text-accent" /> عام</span>
            <span class="text-[11px] leading-snug text-muted">في الرئيسية ورفوف القرّاء وركنك؛ يراه أي قارئ ويعجب به ويحفظه ويشاركه.</span>
        </label>
        <label class="{{ $choice }}">
            <input type="radio" name="is_public" value="0" class="sr-only" @checked((string) $old('is_public', (int) $quote->is_public) === '0')>
            <span class="flex items-center gap-1.5 text-sm font-medium"><x-icon name="lock" class="size-4 text-accent" /> خاص</span>
            <span class="text-[11px] leading-snug text-muted">في ركنك وحدك؛ لا يظهر في الرئيسية ولا في صفحة الكتاب، ولا يراه غيرك.</span>
        </label>
    </div>
</fieldset>

{{-- How it looks: a designed card, or the words alone between quotation marks. --}}
<fieldset class="mt-5">
    <legend class="mb-2 text-sm font-medium">طريقة العرض</legend>
    <div class="grid grid-cols-2 gap-2">
        <label class="{{ $choice }}">
            <input type="radio" name="presentation" value="card" class="sr-only" @checked($old('presentation', $quote->presentation) === 'card')>
            <span class="flex items-center gap-1.5 text-sm font-medium"><x-icon name="palette" class="size-4 text-accent" /> بطاقة مصمّمة</span>
            <span class="text-[11px] leading-snug text-muted">بالخط واللون اللذين تختارهما.</span>
        </label>
        <label class="{{ $choice }}">
            <input type="radio" name="presentation" value="text" class="sr-only" @checked($old('presentation', $quote->presentation) === 'text')>
            <span class="flex items-center gap-1.5 text-sm font-medium"><x-icon name="type" class="size-4 text-accent" /> نص مقتبس</span>
            <span class="text-[11px] leading-snug text-muted">الكلمات وحدها بين «علامتي تنصيص».</span>
        </label>
    </div>
</fieldset>
