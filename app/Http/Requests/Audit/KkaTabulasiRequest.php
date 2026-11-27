<?php

namespace App\Http\Requests\Audit;

use App\Models\KkaTabulasi;
use App\Models\Kode;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class KkaTabulasiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('nilai_temuan')) {
            $raw = $this->input('nilai_temuan');
            if (is_string($raw)) {
                $cleaned = preg_replace('/[^\d]/', '', $raw);
                $this->merge([
                    'nilai_temuan' => $cleaned !== '' ? $cleaned : null,
                ]);
            }
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'jenis_tabulasi' => ['required', 'string', Rule::in(KkaTabulasi::JENIS_TABULASIS)],
            'nomor' => ['required', 'string', 'max:100'],
            'tipe_temuan' => ['nullable', 'string', Rule::in(KkaTabulasi::TIPE_TEMUANS)],
            'kode_temuan_id' => [
                'nullable',
                'integer',
                Rule::exists('kodes', 'id')->where('jenis', Kode::JENIS_TEMUAN)->whereNull('deleted_at'),
            ],
            'sub_kode_temuan_id' => [
                'nullable',
                'integer',
                Rule::exists('sub_kodes', 'id')->whereNull('deleted_at'),
            ],
            'nilai_temuan' => ['nullable', 'numeric', 'min:0'],
            'uraian_temuan' => ['nullable', 'string'],
            'kriteria_id' => [
                'nullable',
                'integer',
                Rule::exists('kriterias', 'id')->whereNull('deleted_at'),
            ],
            'uraian_kriteria' => ['nullable', 'string'],
            'kode_sebab_id' => [
                'nullable',
                'integer',
                Rule::exists('kodes', 'id')->where('jenis', Kode::JENIS_SEBAB)->whereNull('deleted_at'),
            ],
            'sub_kode_sebab_id' => [
                'nullable',
                'integer',
                Rule::exists('sub_kodes', 'id')->whereNull('deleted_at'),
            ],
            'uraian_sebab' => ['nullable', 'string'],
            'kode_akibat_id' => [
                'nullable',
                'integer',
                Rule::exists('kodes', 'id')->where('jenis', Kode::JENIS_AKIBAT)->whereNull('deleted_at'),
            ],
            'sub_kode_akibat_id' => [
                'nullable',
                'integer',
                Rule::exists('sub_kodes', 'id')->whereNull('deleted_at'),
            ],
            'uraian_akibat' => ['nullable', 'string'],
            'kode_rekomendasi_id' => [
                'nullable',
                'integer',
                Rule::exists('kodes', 'id')->where('jenis', Kode::JENIS_REKOMENDASI)->whereNull('deleted_at'),
            ],
            'sub_kode_rekomendasi_id' => [
                'nullable',
                'integer',
                Rule::exists('sub_kodes', 'id')->whereNull('deleted_at'),
            ],
            'uraian_rekomendasi' => ['nullable', 'string'],
            'auditor_id' => [
                'nullable',
                'integer',
                Rule::exists('users', 'id')->whereNull('deleted_at'),
            ],
            'status' => ['required', 'string', Rule::in(KkaTabulasi::STATUSES)],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'jenis_tabulasi.required' => 'Jenis tabulasi wajib dipilih.',
            'jenis_tabulasi.in' => 'Jenis tabulasi tidak valid.',
            'nomor.required' => 'Nomor wajib diisi.',
            'tipe_temuan.in' => 'Tipe temuan tidak valid.',
            'status.required' => 'Status wajib dipilih.',
        ];
    }
}
