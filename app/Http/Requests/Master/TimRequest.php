<?php

namespace App\Http\Requests\Master;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TimRequest extends FormRequest
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
        $authUser = $this->user();
        $isAdminDinas = in_array($authUser?->role, ['Admin Nasional', 'Admin Dinas'], true);
        $isAdminKecamatan = in_array($authUser?->role, ['Admin Wilayah', 'Admin Kecamatan'], true);

        $allowedLingkups = match (true) {
            $isAdminDinas => ['Dinas', 'Nasional'],
            $isAdminKecamatan => ['Kecamatan', 'Wilayah'],
            default => ['Dinas', 'Kecamatan', 'Nasional', 'Wilayah'],
        };

        $userExistsRule = function (string $role) {
            return Rule::exists('users', 'id')->where(function ($query) use ($role): void {
                $query->where('role', $role)->whereNull('deleted_at');
            });
        };

        $plottingRules = [
            'nullable',
            'integer',
        ];

        $provinceRules = [
            'nullable',
            'string',
        ];

        return [
            'nama_tim' => ['required', 'string', 'max:255'],
            'lingkup' => ['nullable', 'string', Rule::in($allowedLingkups)],
            'struktur_perangkat_daerah_id' => ['nullable', 'integer', Rule::exists('struktur_perangkat_daerah', 'id')],
            'plotting_id' => $plottingRules,
            'province_code' => $provinceRules,
            'daltu_id' => [
                'required',
                'integer',
                $userExistsRule('Koordinator dan Pengendali'),
            ],
            'dalnis_id' => [
                'required',
                'integer',
                $userExistsRule('Pengawas Tim'),
            ],
            'ketua_tim_id' => [
                'required',
                'integer',
                $userExistsRule('Ketua Tim'),
            ],
            'ketua_tim_2_id' => [
                'nullable',
                'integer',
                'different:ketua_tim_id',
                $userExistsRule('Ketua Tim'),
            ],
            'auditor_ids' => ['required', 'array', 'min:1'],
            'auditor_ids.*' => [
                'integer',
                'distinct',
                $userExistsRule('Auditor'),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nama_tim.required' => 'Nama tim wajib diisi.',
            'lingkup.required' => 'Lingkup tim wajib dipilih.',
            'plotting_id.required' => 'Plot wilayah (WIL) wajib dipilih untuk lingkup Nasional.',
            'province_code.required' => 'Provinsi wajib dipilih untuk lingkup Wilayah.',
            'daltu_id.required' => 'Pengendali Mutu (Daltu) wajib dipilih.',
            'dalnis_id.required' => 'Pengawas Tim (Dalnis) wajib dipilih.',
            'ketua_tim_id.required' => 'Ketua Tim wajib dipilih.',
            'ketua_tim_2_id.different' => 'Ketua Tim 2 tidak boleh sama dengan Ketua Tim 1.',
            'auditor_ids.required' => 'Minimal satu Anggota (Auditor) wajib dipilih.',
            'auditor_ids.min' => 'Minimal satu Anggota (Auditor) wajib dipilih.',
            'auditor_ids.*.exists' => 'Anggota yang dipilih harus memiliki peran Auditor.',
        ];
    }
}
