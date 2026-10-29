<?php

namespace App\Http\Requests;

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
