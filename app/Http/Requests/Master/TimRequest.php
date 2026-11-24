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
        $lingkup = (string) $this->input('lingkup', '');

        return [
            'nama_tim' => ['required', 'string', 'max:255'],
            'lingkup' => ['required', 'string', Rule::in(Tim::LINGKUPS)],
            'plotting_id' => [
                Rule::requiredIf($lingkup === 'Nasional'),
                'nullable',
                'integer',
                Rule::exists('plottings', 'id'),
            ],
            'province_code' => [
                Rule::requiredIf($lingkup === 'Wilayah'),
                'nullable',
                'string',
                Rule::exists('wilayah', 'kode_wilayah'),
            ],
            'daltu_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id')->where(fn ($query) => $query->where('role', 'Koordinator dan Pengendali')),
            ],
            'dalnis_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id')->where(fn ($query) => $query->where('role', 'Pengawas Tim')),
            ],
            'ketua_tim_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id')->where(fn ($query) => $query->where('role', 'Ketua Tim')),
            ],
            'ketua_tim_2_id' => [
                'nullable',
                'integer',
                'different:ketua_tim_id',
                Rule::exists('users', 'id')->where(fn ($query) => $query->where('role', 'Ketua Tim')),
            ],
            'auditor_ids' => ['required', 'array', 'min:1'],
            'auditor_ids.*' => [
                'integer',
                'distinct',
                Rule::exists('users', 'id')->where(fn ($query) => $query->where('role', 'Auditor')),
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
