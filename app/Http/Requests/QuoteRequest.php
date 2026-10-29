<?php

namespace App\Http\Requests;

use App\Models\Quote;
use App\Models\Saying;
use App\Models\Verse;
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
        $editing = $this->route('quote');

        if ($editing instanceof Quote && $editing->isFromLibrary()) {
            $this->merge($editing->only(['content', 'author', 'book']));

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
            ]);
            $this->libraryLink = ['verse_id' => $verses->first()->id, 'saying_id' => null];

            return;
        }

        $sayingId = $this->input('saying');

        if (ctype_digit((string) $sayingId) && $saying = Saying::find((int) $sayingId)) {
            $this->merge(['content' => $saying->text, 'author' => $saying->source, 'book' => null]);
            $this->libraryLink = ['verse_id' => null, 'saying_id' => $saying->id];
        }
    }

    /**
     * @return array{verse_id?: int|null, saying_id?: int|null}
     */
    public function libraryLink(): array
    {
        return $this->libraryLink;
    }

    /**
     * The quick share/edit forms send only "content"; رواق الحرف sends the design fields too,
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
     * Quoting someone else is allowed with their name and the source (Saudi copyright law, Art. 15):
     * once "القائل" is filled, a book or a source is required. Library texts are exempt — their source
     * is خزانة الأدب itself.
     */
    public function withValidator(Validator $validator): void
    {
        $editing = $this->route('quote');
        $isLibraryText = $this->libraryLink !== [] || ($editing instanceof Quote && $editing->isFromLibrary());

        $validator->sometimes('source', 'required',
            fn ($input) => ! $isLibraryText && filled($input->author) && blank($input->book));
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
            'source.required' => 'اذكر مصدر الاقتباس (كتاباً أو صحيفة أو رابطاً) ما دمت تنقل كلام غيرك.',
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
