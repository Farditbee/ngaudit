<?php

namespace App\Http\Requests\Master;

use Illuminate\Foundation\Http\FormRequest;

class TemplateLangkahKerjaDetailRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'uraian' => [
                'required',
                'string',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $plainText = trim(html_entity_decode(strip_tags((string) $value)));
                    if ($plainText === '' && ! str_contains((string) $value, '<img') && ! str_contains((string) $value, '<table')) {
                        $fail('Uraian langkah kerja wajib diisi.');
                    }
                },
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'uraian.required' => 'Uraian langkah kerja wajib diisi.',
        ];
    }
}
