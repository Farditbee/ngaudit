<?php

namespace App\Http\Requests\Master;

use App\Models\SubKode;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SubKodeRequest extends FormRequest
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
        /** @var SubKode|null $current */
        $current = $this->route('subKode');

        return [
            'kode_id' => [
                'required',
                'integer',
                Rule::exists('kodes', 'id')
                    ->where('jenis', $this->route('jenis'))
                    ->whereNull('deleted_at'),
            ],
            'kode' => [
                'required',
                'string',
                'max:20',
                Rule::unique('sub_kodes', 'kode')
                    ->where('kode_id', $this->input('kode_id'))
                    ->whereNull('deleted_at')
                    ->ignore($current?->id),
            ],
            'nama' => ['required', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'kode_id.required' => 'Kode induk wajib dipilih.',
            'kode_id.exists' => 'Kode induk tidak valid.',
            'kode.required' => 'Sub kode wajib diisi.',
            'kode.unique' => 'Sub kode sudah digunakan pada kode induk ini.',
            'nama.required' => 'Nama sub kode wajib diisi.',
        ];
    }
}
