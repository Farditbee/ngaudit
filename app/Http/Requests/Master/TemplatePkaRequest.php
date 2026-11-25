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
            default => TemplatePka::LINGKUPS,
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
            'nama_template' => ['required', 'string', 'max:255'],
            'lingkup' => ['required', 'string', Rule::in($allowedLingkups)],
            'plotting_id' => $plottingRules,
            'kode_wilayah' => $provinceRules,
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
