<?php

namespace App\Http\Requests\Master;

use App\Models\TemplatePka;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TemplatePkaRequest extends FormRequest
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
        $isAdminDinas = in_array($authUser?->role, ['Admin Nasional', 'Admin Dinas'], true) || ($authUser?->level === 'Dinas');
        $isAdminKecamatan = in_array($authUser?->role, ['Admin Wilayah', 'Admin Kecamatan'], true) || ($authUser?->level === 'Kecamatan');

        $lingkup = match (true) {
            $isAdminDinas => 'Dinas',
            $isAdminKecamatan => 'Kecamatan',
            default => (string) $this->input('lingkup', 'Dinas'),
        };

        $allowedLingkups = match (true) {
            $isAdminDinas => ['Dinas'],
            $isAdminKecamatan => ['Kecamatan'],
            default => TemplatePka::LINGKUPS,
        };

        return [
            'nama_template' => ['required', 'string', 'max:255'],
            'lingkup' => ['required', 'string', Rule::in($allowedLingkups)],
            'struktur_perangkat_daerah_id' => [
                'nullable',
                'integer',
                Rule::exists('struktur_perangkat_daerah', 'id'),
            ],
            'plotting_id' => ['nullable', 'integer'],
            'kode_wilayah' => ['nullable', 'string'],
            'pendahuluan' => ['nullable', 'string'],
            'tujuan' => ['nullable', 'string'],
            'ruang_lingkup' => ['nullable', 'string'],
            'sasaran' => ['nullable', 'string'],
            'waktu_pelaksanaan' => ['nullable', 'string'],
            'periode' => ['nullable', 'string'],
            'penutup' => ['nullable', 'string'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nama_template.required' => 'Nama template wajib diisi.',
            'lingkup.required' => 'Lingkup audit wajib dipilih.',
            'plotting_id.required' => 'Wilayah plotting (WIL) wajib dipilih untuk lingkup Nasional.',
            'kode_wilayah.required' => 'Provinsi wajib dipilih untuk lingkup Wilayah.',
        ];
    }
}
