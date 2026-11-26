<?php

namespace App\Http\Requests\Audit;

use App\Models\Pka;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PkaLangkahKerjaRequest extends FormRequest
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
        /** @var Pka|null $pka */
        $pka = $this->route('pka');
        $allowedAuditorIds = [];

        if ($pka) {
            $pka->loadMissing(['sprin.tim.auditors:id']);
            $auditorIds = $pka->sprin?->tim?->auditors?->pluck('id')->map(fn (mixed $id): int => (int) $id)->all() ?? [];
            $katimIds = array_filter([
                $pka->sprin?->katim ? (int) $pka->sprin->katim : null,
                $pka->sprin?->katim_2 ? (int) $pka->sprin->katim_2 : null,
            ]);
            $allowedAuditorIds = array_values(array_unique(array_merge($auditorIds, $katimIds)));
        }

        $auditorRules = ['nullable', 'integer'];
        if (! empty($allowedAuditorIds)) {
            $auditorRules[] = Rule::in($allowedAuditorIds);
        } else {
            $auditorRules[] = Rule::exists('users', 'id');
        }

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
            'dilaksanakan_rencana_id' => $auditorRules,
            'dilaksanakan_realisasi_id' => $auditorRules,
            'waktu_rencana' => ['nullable', 'integer', 'min:0', 'max:3650'],
            'waktu_realisasi' => ['nullable', 'integer', 'min:0', 'max:3650'],
            'catatan' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'uraian.required' => 'Uraian langkah kerja wajib diisi.',
            'dilaksanakan_rencana_id.in' => 'Pelaksana rencana harus dipilih dari daftar auditor pada Sprin PKA ini.',
            'dilaksanakan_realisasi_id.in' => 'Pelaksana realisasi harus dipilih dari daftar auditor pada Sprin PKA ini.',
            'waktu_rencana.integer' => 'Waktu rencana harus berupa angka (hari).',
            'waktu_rencana.min' => 'Waktu rencana minimal 0 hari.',
            'waktu_realisasi.integer' => 'Waktu realisasi harus berupa angka (hari).',
            'waktu_realisasi.min' => 'Waktu realisasi minimal 0 hari.',
        ];
    }
}
