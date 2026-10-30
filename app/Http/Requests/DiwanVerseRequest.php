<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class DiwanVerseRequest extends FormRequest
{
    /**
     * The most verses one reader's diwan can hold.
     */
    public const MAX_VERSES = 200;

    /**
     * Logged-in users only ("auth" middleware); ownership is checked by DiwanPolicy on the route.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'sadr' => ['required', 'string', 'min:2', 'max:120'],
            'ajuz' => ['nullable', 'string', 'max:120'],
            'note' => ['nullable', 'string', 'max:200'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($this->route('diwan')->verses()->count() >= self::MAX_VERSES) {
                $validator->errors()->add('sadr', 'امتلأ الديوان: يتّسع لـ '.self::MAX_VERSES.' بيت.');
            }
        });
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'sadr.required' => 'اكتب صدر البيت.',
            'sadr.min' => 'الصدر من حرفين إلى ١٢٠ حرفاً.',
            'sadr.max' => 'الصدر من حرفين إلى ١٢٠ حرفاً.',
            'ajuz.max' => 'العجز حتى ١٢٠ حرفاً.',
            'note.max' => 'الملاحظة حتى ٢٠٠ حرف.',
        ];
    }
}
