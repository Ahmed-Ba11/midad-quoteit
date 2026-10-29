<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

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
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'content' => ['required', 'string', 'min:3', 'max:155'],
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
        ];
    }
}
