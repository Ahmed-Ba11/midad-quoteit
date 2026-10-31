{{--
    «النص» in «محرّر النص» and مرسم الحروف. When the details already came from «شارك اقتباساً» (or a
    quote is being edited) they fold into one line — the words and who said them. «تعديل» opens the
    words only; who said it, the source and who sees it wait in their own fold («المصدر والنشر»), so
    editing the words never brings the whole share form back. A fresh start opens both.
    Library text is locked: it is published exactly as in the diwan (QuoteRequest rebuilds it on the server).
    Needs: $quote, $draft, $myAnthologies (optional), $categories, $bookTitles; $withStory adds حكاية الاقتباس.
--}}
@php
    $isEditing = $quote->exists;
    $libraryVerses = $quote->relationLoaded('libraryVerses') ? $quote->getRelation('libraryVerses') : collect();
    $librarySaying = ! $isEditing && $quote->relationLoaded('saying') ? $quote->saying : null;
    $isLibrary = $libraryVerses->isNotEmpty() || $librarySaying || $quote->isFromLibrary();
    $hasDetails = filled(old('content', $quote->content)) && ! $errors->any();
    $detailErrors = $errors->hasAny(['author', 'source', 'source_url', 'signature', 'book', 'page', 'kind', 'is_public', 'category_id', 'story', 'anthology_id', 'book_genre']);
    $who = $quote->is_original ? ($draft['signature'] ?? $quote->author ?? 'بقلمك') : ($quote->author ?: 'مأثور');
@endphp

<details data-details class="group rounded-2xl border border-line bg-paper" @if (! $hasDetails) open @endif>
    <summary class="flex cursor-pointer list-none items-center gap-3 p-4 [&::-webkit-details-marker]:hidden">
        <x-icon :name="$isLibrary ? 'book-open' : 'quill'" class="size-5 shrink-0 text-accent" />
        <span class="min-w-0 flex-1">
            <span class="block text-xs text-muted">النص</span>
            <span data-details-summary class="block truncate font-text text-base">{{ $hasDetails ? \Illuminate\Support\Str::limit(str_replace(["\r", "\n", "\t"], ' ', $quote->content), 60) : 'اكتب النص ومن قاله' }}</span>
            @if ($hasDetails)
                <span class="block truncate text-xs text-muted">— {{ $who }}{{ $quote->book ? ' · '.$quote->book : '' }} · {{ $quote->is_public ? 'عام' : 'خاص' }}</span>
            @endif
        </span>
        <span class="shrink-0 rounded-full border border-line px-3 py-1 text-xs text-muted transition group-open:hidden">{{ $isLibrary ? 'عرض' : 'تعديل' }}</span>
        <x-icon name="chevron-left" class="hidden size-4 shrink-0 -rotate-90 text-muted group-open:block" />
    </summary>

    <div class="space-y-5 border-t border-line p-4">
        @if ($errors->any())
            <div role="alert" class="rounded-xl border border-accent/40 bg-accent-soft p-3 text-sm text-accent">
                راجع الحقول: {{ $errors->first() }}
            </div>
        @endif

        @if ($isLibrary)
            <div class="rounded-2xl border border-gold/40 bg-surface p-5">
                <p class="flex items-center gap-2 text-xs font-medium text-gold">
                    <x-ornament name="star" class="size-3.5" /> من المكتبة · يُنشر كما هو
                </p>
                <p class="mt-3 whitespace-pre-line text-center font-text text-lg leading-loose">{{ $quote->content }}</p>
                <p class="mt-2 text-center text-sm text-muted">— {{ collect([$quote->author, $quote->book])->filter()->implode('، ') }}</p>

                @if (! $isEditing)
                    <div class="mt-4 flex flex-wrap justify-center gap-2 text-sm">
                        <a href="{{ route('library.index') }}#shelf" class="rounded-full border border-line px-4 py-2 transition hover:border-accent hover:text-accent">اختر بيتاً آخر</a>
                        <a href="{{ route('quotes.create') }}" class="rounded-full border border-line px-4 py-2 transition hover:border-accent hover:text-accent">اكتب نصك الخاص</a>
                    </div>
                @endif
            </div>

            <textarea name="content" hidden readonly>{{ $quote->content }}</textarea>
            <input type="hidden" name="author" value="{{ $quote->author }}">
            <input type="hidden" name="book" value="{{ $quote->book }}">
            @foreach ($libraryVerses as $verse)
                <input type="hidden" name="verses[]" value="{{ $verse->id }}">
            @endforeach
            @if ($librarySaying)
                <input type="hidden" name="saying" value="{{ $librarySaying->id }}">
            @endif
        @else
            <x-textarea name="content" label="نص الاقتباس" hint="من ٣ إلى ١٥٥ حرفاً؛ للشعر: اكتب كل شطر في سطر، أو افصل الشطرين بـ * أو …" rows="4" minlength="3"
                maxlength="155" required :value="old('content', $quote->content)" />

        @endif

        {{-- Who said it, where from, who sees it: folded away once known. --}}
        <details class="group/more rounded-2xl border border-line bg-surface" @if (! $hasDetails || $detailErrors) open @endif>
            <summary class="flex cursor-pointer list-none items-center justify-between gap-2 px-4 py-3 text-sm font-medium [&::-webkit-details-marker]:hidden">
                <span class="flex items-center gap-2"><x-icon name="book-open" class="size-4 text-accent" /> المصدر والنشر</span>
                <x-icon name="chevron-left" class="size-4 text-muted transition group-open/more:-rotate-90" />
            </summary>
            <div class="space-y-5 border-t border-line p-4">
        @if ($isLibrary)
            @include('quotes.partials.publish-options', ['quote' => $quote, 'anthologies' => $myAnthologies ?? collect(), 'defaults' => $draft])
        @else
            @include('quotes.partials.text-origin', ['quote' => $quote, 'withBook' => true, 'anthologies' => $myAnthologies ?? collect(), 'defaults' => $draft])
        @endif

        @if ($withStory ?? false)
            <x-textarea name="story" label="حكاية الاقتباس" hint="اختياري، حتى ٥٠٠ حرف: أين قرأته؟ ولماذا بقي معك؟"
                rows="3" maxlength="500" :value="old('story', $draft['story'] ?? $quote->story)" />
        @endif

        <div>
            <label for="category_id" class="mb-1.5 block text-sm font-medium">التصنيف</label>
            <select id="category_id" name="category_id" aria-describedby="category_id-hint"
                class="block h-12 w-full rounded-xl border border-line bg-white px-4 text-ink focus:border-accent focus:outline-none focus:ring-3 focus:ring-accent/20 dark:bg-surface">
                <option value="">بدون تصنيف</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected((int) old('category_id', $draft['category_id'] ?? $quote->category_id) === $category->id)>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
            <p id="category_id-hint" class="mt-1.5 text-xs text-muted">اختياري، يساعد القرّاء على التصفية</p>
        </div>
            </div>
        </details>
    </div>
</details>
