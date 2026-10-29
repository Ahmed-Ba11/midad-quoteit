<?php

namespace App\Http\Requests;

use App\Models\Quote;
use App\Models\Saying;
use App\Models\Verse;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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

        $verseIds = collect($this->input('verses', []))->filter(fn ($id) => ctype_digit((string) $id))
            ->map(fn ($id) => (int) $id)->unique()->take(config('library.max_selection'));

        if ($verseIds->isNotEmpty()) {
            $verses = Verse::with('work.poet')->findMany($verseIds)->sortBy(fn (Verse $verse) => $verseIds->search($verse->id))->values();

            if ($verses->isNotEmpty()) {
                $this->merge([
                    'content' => $verses->map->asText()->implode("\n"),
                    'author' => $verses->pluck('work.poet.name')->unique()->implode(' و'),
                    'book' => $verses->pluck('work.title')->unique()->count() === 1 ? $verses->first()->work->title : null,
                    'verse_id' => $verses->first()->id,
                    'saying_id' => null,
                ]);
            }

            return;
        }

        if ($this->filled('saying_id') && $saying = Saying::find($this->input('saying_id'))) {
            $this->merge([
                'content' => $saying->text,
                'author' => $saying->source,
                'book' => null,
                'verse_id' => null,
            ]);
        }
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
            'verse_id' => ['sometimes', 'nullable', 'integer', 'exists:verses,id'],
            'saying_id' => ['sometimes', 'nullable', 'integer', 'exists:sayings,id'],
            'author' => ['sometimes', 'nullable', 'string', 'max:100'],
            'book' => ['sometimes', 'nullable', 'string', 'max:150'],
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
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'content.required' => 'نص الاقتباس مطلوب.',
            'content.min' => 'الاقتباس من ٣ إلى ١٥٥ حرفاً.',
            'content.max' => 'الاقتباس من ٣ إلى ١٥٥ حرفاً.',
            'verse_id.exists' => 'هذا البيت غير موجود في المكتبة.',
            'saying_id.exists' => 'هذا المأثور غير موجود في المكتبة.',
            'category_id.exists' => 'اختر تصنيفاً من القائمة.',
            'author.max' => 'اسم القائل حتى ١٠٠ حرف.',
            'book.max' => 'اسم الكتاب حتى ١٥٠ حرفاً.',
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
