<?php

namespace App\Http\Requests\Audit;

use App\Models\Kka;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class KkaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $nullableKeys = [
            'template_kka_id',
            'tanggal_dibuat',
            'tanggal_direviu',
            'auditee_plotting_id',
            'province_code',
            'city_code',
            'district_code',
            'village_code',
        ];

        foreach ($nullableKeys as $key) {
            if ($this->has($key) && ($this->input($key) === '' || $this->input($key) === 'null')) {
                $this->merge([$key => null]);
            }
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $kka = $this->route('kka');
        $kkaId = $kka instanceof Kka ? $kka->id : $kka;
        $existingKka = $kkaId ? Kka::query()->find($kkaId) : null;

        $pkaRules = ['required', 'integer'];
        if (! $existingKka || (int) $this->input('pka_id') !== (int) $existingKka->pka_id) {
            $pkaRules[] = Rule::exists('pkas', 'id')->whereNull('deleted_at')->where('status', 'Final');
        } else {
            $pkaRules[] = Rule::exists('pkas', 'id')->whereNull('deleted_at');
        }

        $level = (string) $this->input('auditee_level', '');
        $regionExists = Rule::exists('wilayah', 'kode_wilayah')->whereNull('deleted_at');

        return [
            'nomor_kka' => ['required', 'string', 'max:255'],
            'pka_id' => $pkaRules,
            'tanggal_dibuat' => ['nullable', 'date'],
            'tanggal_direviu' => ['nullable', 'date'],
            'tanggal_kka' => ['required', 'date'],
            'tempat' => ['required', 'string', 'max:255'],
            'auditee_level' => ['required', 'string', Rule::in(Kka::AUDITEE_LEVELS)],
            'auditee_plotting_id' => [
                Rule::requiredIf($level === 'Nasional'),
                'nullable',
                'integer',
                Rule::exists('plottings', 'id'),
            ],
            'province_code' => [Rule::requiredIf($level === 'Wilayah'), 'nullable', 'string', 'size:2', $regionExists],
            'city_code' => ['nullable', 'string', 'size:4', 'required_with:district_code', $regionExists],
            'district_code' => ['nullable', 'string', 'size:6', 'required_with:village_code', $regionExists],
            'village_code' => ['nullable', 'string', 'size:10', $regionExists],
            'template_kka_id' => ['nullable', 'integer', Rule::exists('template_kkas', 'id')->whereNull('deleted_at')],
            'tujuan' => ['nullable', 'string'],
            'langkah' => ['nullable', 'string'],
            'hasil' => ['nullable', 'string'],
            'status' => ['required', 'string', Rule::in(Kka::STATUSES)],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($this->input('auditee_level') !== 'Wilayah') {
                return;
            }

            $hierarchy = [
                'city_code' => 'province_code',
                'district_code' => 'city_code',
                'village_code' => 'district_code',
            ];

            foreach ($hierarchy as $child => $parent) {
                $childCode = (string) $this->input($child);
                $parentCode = (string) $this->input($parent);

                if ($childCode !== '' && ! str_starts_with($childCode, $parentCode)) {
                    $validator->errors()->add($child, 'Wilayah yang dipilih tidak sesuai dengan tingkat di atasnya.');
                }
            }
        });
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nomor_kka.required' => 'Nomor KKA wajib diisi.',
            'pka_id.required' => 'Nomor PKA wajib dipilih.',
            'pka_id.exists' => 'Nomor PKA yang dipilih harus berstatus Final.',
            'tanggal_kka.required' => 'Tanggal pembuatan KKA wajib diisi.',
            'tanggal_kka.date' => 'Format tanggal pembuatan KKA tidak valid.',
            'tempat.required' => 'Tempat wajib diisi.',
            'auditee_level.required' => 'Level auditee wajib dipilih.',
            'auditee_plotting_id.required' => 'WIL auditee wajib dipilih untuk level Nasional.',
            'province_code.required' => 'Provinsi auditee wajib dipilih untuk level Wilayah.',
            'city_code.required_with' => 'Kabupaten/Kota wajib dipilih sebelum memilih Kecamatan.',
            'district_code.required_with' => 'Kecamatan wajib dipilih sebelum memilih Desa/Kelurahan.',
            'status.required' => 'Status KKA (Draft / Final) wajib dipilih.',
            'status.in' => 'Status KKA harus berupa Draft atau Final.',
        ];
    }
}
