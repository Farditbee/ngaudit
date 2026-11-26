<?php

namespace App\Http\Requests\Master;

use Illuminate\Foundation\Http\FormRequest;

class KriteriaRequest extends FormRequest
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
            'nama' => ['required', 'string', 'max:255'],
            'is_opsnal' => ['nullable', 'boolean'],
            'is_logistik' => ['nullable', 'boolean'],
            'is_sdm' => ['nullable', 'boolean'],
            'is_garkeu' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nama.required' => 'Nama kriteria wajib diisi.',
        ];
    }
}
