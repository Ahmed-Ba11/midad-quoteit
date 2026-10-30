<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AnthologyRequest extends FormRequest
{
    /**
     * Logged-in users only ("auth" middleware); ownership is checked by AnthologyPolicy on the route.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * An unchecked "public" box sends nothing, which means private.
     */
    protected function prepareForValidation(): void
    {
        $this->merge(['is_public' => $this->boolean('is_public')]);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'min:2', 'max:60'],
            'description' => ['nullable', 'string', 'max:200'],
            'palette' => ['sometimes', 'required', Rule::in(array_keys(config('quotes.palettes')))],
            'is_public' => ['boolean'],
            'quote_id' => ['sometimes', 'nullable', 'integer', 'exists:quotes,id'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'سمِّ مختاراتك.',
            'title.min' => 'الاسم من حرفين إلى ٦٠ حرفاً.',
            'title.max' => 'الاسم من حرفين إلى ٦٠ حرفاً.',
            'description.max' => 'الوصف حتى ٢٠٠ حرف.',
            'palette.in' => 'اختر لوناً من القائمة.',
            'quote_id.exists' => 'الاقتباس غير موجود.',
        ];
    }
}
