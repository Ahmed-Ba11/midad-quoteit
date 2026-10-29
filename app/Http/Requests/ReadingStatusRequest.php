<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReadingStatusRequest extends FormRequest
{
    /**
     * Logged-in readers only ("auth" middleware); each reader changes only their own shelf.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * An empty status takes the book off the shelf.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'status' => ['nullable', Rule::in(array_keys(config('quotes.reading_statuses')))],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'status.in' => 'اختر حالة القراءة من القائمة.',
        ];
    }
}
