<?php

namespace App\Http\Requests\Master;

use App\Models\Kode;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class KodeRequest extends FormRequest
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
        /** @var Kode|null $current */
        $current = $this->route('kode');
        $jenis = (string) $this->route('jenis');
        $requiresTemuan = $jenis !== Kode::JENIS_TEMUAN;

        $uniqueRule = Rule::unique('kodes', 'kode')
            ->where('jenis', $jenis)
            ->whereNull('deleted_at')
            ->ignore($current?->id);

        if ($requiresTemuan) {
            $uniqueRule->where('kode_temuan_id', $this->input('kode_temuan_id'));
        }

        return [
            'kode_temuan_id' => $requiresTemuan
                ? [
                    'required',
                    'integer',
                    Rule::exists('kodes', 'id')
                        ->where('jenis', Kode::JENIS_TEMUAN)
                        ->whereNull('deleted_at'),
                ]
                : ['nullable'],
            'kode' => [
                'required',
                'string',
                'max:20',
                $uniqueRule,
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
            'kode_temuan_id.required' => 'Kode temuan wajib dipilih.',
            'kode_temuan_id.exists' => 'Kode temuan tidak valid.',
            'kode.required' => 'Kode wajib diisi.',
            'kode.unique' => 'Kode sudah digunakan.',
            'nama.required' => 'Nama kode wajib diisi.',
        ];
    }
}
