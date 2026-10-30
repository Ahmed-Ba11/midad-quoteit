<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BioRequest extends FormRequest
{
    /**
     * Logged-in users only ("auth" middleware); a user can only change their own bio.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'bio' => ['nullable', 'string', 'max:160'],
            'accent' => ['sometimes', 'nullable', Rule::in(array_keys(config('quotes.palettes')))],
            'badge' => ['sometimes', 'nullable', Rule::in(array_keys(config('quotes.badges')))],
            'show_poets' => ['sometimes', 'boolean'],
            'show_wall' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'bio.max' => 'النبذة حتى ١٦٠ حرفاً.',
            'accent.in' => 'اختر لوناً من القائمة.',
            'badge.in' => 'اختر شارة من القائمة.',
        ];
    }
}
