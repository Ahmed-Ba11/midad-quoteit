<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class SignupRequest extends FormRequest
{
    /**
     * Guests only; enforced by the "guest" route middleware.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * One account per address (emails compared in lower case); repeated spaces in the name collapse to one.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => mb_strtolower(trim((string) $this->input('email'))),
            'name' => preg_replace('/ {2,}/', ' ', trim((string) $this->input('name'))),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Letters only (Arabic or English) and single spaces: no digits, punctuation or line breaks.
            'name' => ['required', 'string', 'between:2,50', 'regex:/^[\x{0621}-\x{063A}\x{0641}-\x{0652}\x{0671}-\x{06D3}A-Za-z]+(?: [\x{0621}-\x{063A}\x{0641}-\x{0652}\x{0671}-\x{06D3}A-Za-z]+)*$/u'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'الاسم مطلوب.',
            'name.regex' => 'الاسم من ٢ إلى ٥٠ حرفاً، حروف عربية أو إنجليزية ومسافات فقط.',
            'name.between' => 'الاسم من ٢ إلى ٥٠ حرفاً، حروف عربية أو إنجليزية ومسافات فقط.',
            'email.required' => 'البريد الإلكتروني مطلوب.',
            'email.email' => 'أدخل بريداً إلكترونياً صحيحاً، مثل name@example.com.',
            'email.max' => 'البريد الإلكتروني طويل جداً.',
            'email.unique' => 'هذا البريد مسجّل مسبقاً.',
            'password.required' => 'كلمة المرور مطلوبة.',
            'password.min' => 'كلمة المرور ٨ أحرف على الأقل.',
        ];
    }
}
