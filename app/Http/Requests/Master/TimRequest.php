<?php

namespace App\Http\Requests\Master;

use App\Models\Tim;
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
        $isAdminNasional = $authUser?->role === 'Admin Nasional';
        $isAdminWilayah = $authUser?->role === 'Admin Wilayah';
        $authProvinceCode = ($isAdminWilayah && $authUser?->kode_wilayah)
            ? substr(str_replace('.', '', (string) $authUser->kode_wilayah), 0, 2)
            : null;

        $lingkup = match (true) {
            $isAdminNasional => 'Nasional',
            $isAdminWilayah => 'Wilayah',
            default => (string) $this->input('lingkup', ''),
        };

        $allowedLingkups = match (true) {
            $isAdminNasional => ['Nasional'],
            $isAdminWilayah => ['Wilayah'],
            default => Tim::LINGKUPS,
        };

        $userExistsRule = function (string $role) use ($isAdminNasional, $isAdminWilayah, $authUser, $authProvinceCode) {
            return Rule::exists('users', 'id')->where(function ($query) use ($role, $isAdminNasional, $isAdminWilayah, $authUser, $authProvinceCode): void {
                $query->where('role', $role)->whereNull('deleted_at');
                if ($isAdminNasional && $authUser?->plotting_id) {
                    $query->where('plotting_id', $authUser->plotting_id);
                } elseif ($isAdminWilayah && $authProvinceCode) {
                    $query->whereRaw("SUBSTRING(REPLACE(kode_wilayah, '.', ''), 1, 2) = ?", [$authProvinceCode]);
                }
            });
        };

        $plottingRules = [
            Rule::requiredIf($lingkup === 'Nasional'),
            'nullable',
            'integer',
            Rule::exists('plottings', 'id'),
        ];
        if ($isAdminNasional && $authUser?->plotting_id && $lingkup === 'Nasional') {
            $plottingRules[] = Rule::in([(int) $authUser->plotting_id]);
        }

        $provinceRules = [
            Rule::requiredIf($lingkup === 'Wilayah'),
            'nullable',
            'string',
            Rule::exists('wilayah', 'kode_wilayah'),
        ];
        if ($isAdminWilayah && $authProvinceCode && $lingkup === 'Wilayah') {
            $provinceRules[] = Rule::in([$authProvinceCode]);
        }

        return [
            'nama_tim' => ['required', 'string', 'max:255'],
            'lingkup' => ['required', 'string', Rule::in($allowedLingkups)],
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
