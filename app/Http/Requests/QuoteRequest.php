<?php

namespace App\Http\Requests;

use App\Models\Quote;
use App\Models\Saying;
use App\Models\Verse;
use App\Support\CardDesign;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class QuoteRequest extends FormRequest
{
    /**
     * Logged-in users only ("auth" middleware); ownership is checked by QuotePolicy.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * The library link (verse_id / saying_id) of a new quote. Never read from the request
     * directly: it is set only from verses[] / saying that exist in خزانة الأدب.
     *
     * @var array{verse_id?: int|null, saying_id?: int|null}
     */
    private array $libraryLink = [];

    /**
     * Library text is never taken from the browser: it is rebuilt from the chosen verses / saying
     * (or kept from the quote being edited), and attributed to its poet or source automatically.
     */
    protected function prepareForValidation(): void
    {
        // مرسم الحروف's settings are whitelisted, never stored as sent (App\Support\CardDesign).
        if ($this->has('design')) {
            $this->merge(['design' => CardDesign::normalize(is_array($this->input('design')) ? $this->input('design') : [])]);
        }

        $editing = $this->route('quote');

        if ($editing instanceof Quote && $editing->isFromLibrary()) {
            $this->merge([...$editing->only(['content', 'author', 'book']), 'is_original' => false, 'kind' => null]);

            return;
        }

        $verseIds = collect((array) $this->input('verses', []))->filter(fn ($id) => ctype_digit((string) $id))
            ->map(fn ($id) => (int) $id)->unique()->take(config('library.max_selection'));

        $verses = $verseIds->isEmpty() ? collect() : Verse::with('work.poet')->findMany($verseIds)
            ->sortBy(fn (Verse $verse) => $verseIds->search($verse->id))->values();

        if ($verses->isNotEmpty()) {
            $this->merge([
                'content' => $verses->map->asText()->implode("\n"),
                'author' => $verses->pluck('work.poet.name')->unique()->implode(' و'),
                'book' => $verses->pluck('work.title')->unique()->count() === 1 ? $verses->first()->work->title : null,
                'is_original' => false,
            ]);
            $this->libraryLink = ['verse_id' => $verses->first()->id, 'saying_id' => null];

            return;
        }

        $sayingId = $this->input('saying');

        if (ctype_digit((string) $sayingId) && $saying = Saying::find((int) $sayingId)) {
            $this->merge(['content' => $saying->text, 'author' => $saying->attribution(), 'book' => null, 'is_original' => false]);
            $this->libraryLink = ['verse_id' => null, 'saying_id' => $saying->id];

            return;
        }

        $this->applyTextKind();
    }

    /**
     * "ما نوع النص؟" decides who the author is and whether a source is needed:
     * quoted — someone else's words: their name and where it came from are required;
     * original — my own writing (بقلمي): signed with my signature, no source;
     * anonymous — a proverb or saying with no known author: source optional.
     */
    private function applyTextKind(): void
    {
        // A link typed into the source's name goes to the link field; the name must be words.
        $source = trim((string) $this->input('source'));

        if (preg_match('#^https?://#i', $source)) {
            $this->merge(['source' => null, 'source_url' => $this->filled('source_url') ? $this->input('source_url') : $source]);
        }

        $kind = $this->input('kind') ?? ($this->boolean('is_original') ? 'original' : null);

        match ($kind) {
            'original' => $this->merge(['kind' => 'original', 'is_original' => true, 'source' => null,
                'author' => mb_substr(trim((string) $this->input('signature')), 0, 100) ?: null]),
            'anonymous' => $this->merge(['is_original' => false, 'author' => null]),
            // Always present, so "required" below cannot be skipped by a missing field.
            'quoted' => $this->merge(['is_original' => false, 'author' => $this->input('author')]),
            default => null,
        };
    }

    /**
     * @return array{verse_id?: int|null, saying_id?: int|null}
     */
    public function libraryLink(): array
    {
        return $this->libraryLink;
    }

    /**
     * The quick share/edit forms send only "content"; مرسم الحروف sends the design fields too,
     * so every design field is "sometimes" and falls back to the column default.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $hexColor = ['sometimes', 'required', 'regex:/^#[0-9A-Fa-f]{6}$/'];

        return [
            'content' => ['required', 'string', 'min:3', 'max:155'],
            'category_id' => ['sometimes', 'nullable', 'integer', 'exists:categories,id'],
            'author' => ['sometimes', 'nullable', 'string', 'max:100'],
            'book' => ['sometimes', 'nullable', 'string', 'max:150'],
            'source' => ['nullable', 'string', 'max:200'],
            'source_url' => ['sometimes', 'nullable', 'string', 'max:255', 'url:http,https'],
            'kind' => ['sometimes', 'nullable', Rule::in(['quoted', 'original', 'anonymous'])],
            'is_original' => ['sometimes', 'boolean'],
            'is_public' => ['sometimes', 'boolean'],
            'presentation' => ['sometimes', 'required', Rule::in(['card', 'text'])],
            'layout' => ['sometimes', 'required', Rule::in(array_keys(config('quotes.layouts')))],
            'design' => ['sometimes', 'array'],
            'mark_style' => ['sometimes', 'required', Rule::in(array_keys(config('quotes.marks')))],
            'anthology_id' => ['sometimes', 'nullable', 'integer'],
            'page' => ['sometimes', 'nullable', 'integer', 'between:1,9999'],
            'book_genre' => ['sometimes', 'nullable', Rule::in(array_keys(config('quotes.book_genres')))],
            'story' => ['sometimes', 'nullable', 'string', 'max:500'],
            'font' => ['sometimes', 'required', Rule::in(array_keys(config('quotes.fonts')))],
            'bg_color' => $hexColor,
            'text_color' => $hexColor,
            'highlight_color' => $hexColor,
            'highlight_style' => ['sometimes', 'required', Rule::in(['color', 'mark'])],
            'highlights' => ['sometimes', 'nullable', 'array', 'max:155'],
            'highlights.*' => ['integer', 'min:0', 'max:154', 'distinct'],
            'texture' => ['sometimes', 'boolean'],
            'ratio' => ['sometimes', 'required', Rule::in(array_keys(config('quotes.ratios')))],
            'font_size' => [
                'sometimes', 'required', 'integer',
                'between:'.config('quotes.font_size.min').','.config('quotes.font_size.max'),
            ],
        ];
    }

    /**
     * Quoting someone else is allowed with their name and the source (Saudi copyright law, Art. 15).
     * So "quoted" text needs the author, and the source's name — a book/diwan, or a newspaper, site
     * or account. A link alone is not enough: readers see the name, the link only lets them read on.
     * Library texts are exempt — their source is خزانة الأدب itself.
     */
    public function withValidator(Validator $validator): void
    {
        $editing = $this->route('quote');
        $isLibraryText = $this->libraryLink !== [] || ($editing instanceof Quote && $editing->isFromLibrary());

        if ($isLibraryText) {
            return;
        }

        $validator->sometimes('author', 'required', fn ($input) => $input->kind === 'quoted');

        $validator->after(function (Validator $validator) {
            $quotesSomeone = $this->input('kind') === 'quoted' || ($this->input('kind') === null && $this->filled('author'));

            if ($quotesSomeone && blank($this->input('book')) && blank($this->input('source'))) {
                $validator->errors()->add('source', 'اذكر من أين نقلته: اسم الكتاب أو الديوان، أو المصدر (صحيفة، موقع، حساب…). الرابط وحده لا يكفي.');
            }
        });
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'content.required' => 'نص الاقتباس مطلوب.',
            'content.min' => 'الاقتباس من ٣ إلى ١٥٥ حرفاً.',
            'content.max' => 'الاقتباس من ٣ إلى ١٥٥ حرفاً.',
            'category_id.exists' => 'اختر تصنيفاً من القائمة.',
            'author.max' => 'اسم القائل حتى ١٠٠ حرف.',
            'book.max' => 'اسم الكتاب حتى ١٥٠ حرفاً.',
            'source.max' => 'المصدر حتى ٢٠٠ حرف.',
            'page.between' => 'رقم الصفحة من ١ إلى ٩٩٩٩.',
            'page.integer' => 'رقم الصفحة أرقام فقط.',
            'book_genre.in' => 'اختر نوع الكتاب من القائمة.',
            'author.required' => 'اكتب اسم القائل: الشاعر أو الكاتب الذي تنقل كلامه.',
            'source_url.url' => 'الرابط يبدأ بـ https:// ويكون عنوان صفحة صحيحاً.',
            'source_url.max' => 'الرابط حتى ٢٥٥ حرفاً.',
            'kind.in' => 'اختر نوع النص من الخيارات.',
            'presentation.in' => 'اختر طريقة العرض من الخيارات.',
            'layout.in' => 'اختر التنسيق: تلقائي أو شعر أو نثر.',
            'story.max' => 'الحكاية حتى ٥٠٠ حرف.',
            'font.in' => 'اختر خطاً من القائمة.',
            '*_color.regex' => 'اللون يجب أن يكون بصيغة ‎#RRGGBB.',
            'highlight_style.in' => 'نوع الإبراز غير صحيح.',
            'highlights.*' => 'الكلمات المبرزة غير صحيحة.',
            'ratio.in' => 'اختر مقاساً من القائمة.',
            'font_size.between' => 'حجم الخط من ٢٠ إلى ٥٦.',
        ];
    }

    /**
     * Drop highlight indexes that point past the last word (e.g. after the text was shortened).
     */
    protected function passedValidation(): void
    {
        if (! $this->has('highlights')) {
            return;
        }

        $wordCount = count(preg_split('/\s+/u', trim($this->input('content')), -1, PREG_SPLIT_NO_EMPTY));

        $this->merge([
            'highlights' => collect($this->input('highlights') ?? [])
                ->map(fn ($index) => (int) $index)
                ->filter(fn (int $index) => $index < $wordCount)
                ->sort()->values()->all(),
        ]);
    }
}
