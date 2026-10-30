<?php

namespace App\Http\Requests;

use App\Models\Diwan;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class DiwanRequest extends FormRequest
{
    /**
     * Logged-in users only ("auth" middleware); ownership is checked by DiwanPolicy on the route.
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
            'poet_name' => ['required', 'string', 'min:2', 'max:60'],
            'poet_death_year' => ['nullable', 'integer', 'between:1,'.now()->year],
            'title' => ['required', 'string', 'min:2', 'max:80'],
            'about' => ['nullable', 'string', 'max:300'],
            'source' => ['required', 'string', 'min:3', 'max:200'],
            'color' => ['sometimes', 'required', Rule::in(array_keys(config('quotes.palettes')))],
            'is_public' => ['boolean'],
        ];
    }

    /**
     * Publishing for everyone is allowed only for a poet who died more than 50 years ago
     * (Saudi copyright law, Art. 19). Anything newer stays the reader's private notebook.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($this->boolean('is_public') && ! Diwan::isInPublicDomain($this->integer('poet_death_year') ?: null)) {
                $validator->errors()->add('is_public',
                    'لا يُنشر للجميع إلا ديوان شاعر تُوفّي قبل أكثر من ٥٠ سنة (نظام حماية حقوق المؤلف، المادة ١٩). احفظه خاصاً بك، واقتبس منه بيتاً أو بيتين مع المصدر.');
            }
        });
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'poet_name.required' => 'اسم الشاعر مطلوب.',
            'poet_name.min' => 'اسم الشاعر من حرفين إلى ٦٠ حرفاً.',
            'poet_name.max' => 'اسم الشاعر من حرفين إلى ٦٠ حرفاً.',
            'poet_death_year.integer' => 'سنة الوفاة أرقام فقط.',
            'poet_death_year.between' => 'سنة الوفاة بالميلادي، ولا تتجاوز السنة الحالية.',
            'title.required' => 'سمِّ الديوان.',
            'title.min' => 'اسم الديوان من حرفين إلى ٨٠ حرفاً.',
            'title.max' => 'اسم الديوان من حرفين إلى ٨٠ حرفاً.',
            'about.max' => 'النبذة حتى ٣٠٠ حرف.',
            'source.required' => 'اذكر المصدر الذي نقلت منه الأبيات (كتاباً مطبوعاً أو رابطاً).',
            'source.min' => 'المصدر من ٣ إلى ٢٠٠ حرف.',
            'source.max' => 'المصدر من ٣ إلى ٢٠٠ حرف.',
            'color.in' => 'اختر لوناً من القائمة.',
        ];
    }
}
