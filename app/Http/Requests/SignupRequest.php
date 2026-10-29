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
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'regex:/^[\p{Arabic}A-Za-z\s]{2,50}$/u'],
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
            'email.required' => 'البريد الإلكتروني مطلوب.',
            'email.email' => 'أدخل بريداً إلكترونياً صحيحاً، مثل name@example.com.',
            'email.max' => 'البريد الإلكتروني طويل جداً.',
            'email.unique' => 'هذا البريد مسجّل مسبقاً.',
            'password.required' => 'كلمة المرور مطلوبة.',
            'password.min' => 'كلمة المرور ٨ أحرف على الأقل.',
        ];
    }
}
