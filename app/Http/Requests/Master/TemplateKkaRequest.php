<?php

namespace App\Http\Requests\Master;

use App\Models\TemplateKka;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TemplateKkaRequest extends FormRequest
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
        $isSuperAdmin = ! $authUser || $authUser->role === 'Super Admin';

        $isLockedNasional = ! $isSuperAdmin && ($authUser->role === 'Admin Nasional' || ($authUser->level === 'Nasional' && $authUser->plotting_id));
        $isLockedWilayah = ! $isSuperAdmin && ! $isLockedNasional && ($authUser->role === 'Admin Wilayah' || (bool) $authUser->kode_wilayah);

        $authProvinceCode = ($isLockedWilayah && $authUser?->kode_wilayah)
            ? substr(str_replace('.', '', (string) $authUser->kode_wilayah), 0, 2)
            : null;

        $lingkup = match (true) {
            $isLockedNasional => 'Nasional',
            $isLockedWilayah => 'Wilayah',
            default => (string) $this->input('lingkup', ''),
        };

        $allowedLingkups = match (true) {
            $isLockedNasional => ['Nasional'],
            $isLockedWilayah => ['Wilayah'],
            default => TemplateKka::LINGKUPS,
        };

        $plottingRules = [
            Rule::requiredIf($lingkup === 'Nasional'),
            'nullable',
            'integer',
            Rule::exists('plottings', 'id'),
        ];
        if ($isLockedNasional && $authUser?->plotting_id && $lingkup === 'Nasional') {
            $plottingRules[] = Rule::in([(int) $authUser->plotting_id]);
        }

        $provinceRules = [
            Rule::requiredIf($lingkup === 'Wilayah'),
            'nullable',
            'string',
            Rule::exists('wilayah', 'kode_wilayah'),
        ];
        if ($isLockedWilayah && $authProvinceCode && $lingkup === 'Wilayah') {
            $provinceRules[] = Rule::in([$authProvinceCode]);
        }

        return [
            'nama_template' => ['required', 'string', 'max:255'],
            'lingkup' => ['required', 'string', Rule::in($allowedLingkups)],
            'plotting_id' => $plottingRules,
            'kode_wilayah' => $provinceRules,
            'tujuan' => ['nullable', 'string'],
            'langkah' => ['nullable', 'string'],
            'hasil' => ['nullable', 'string'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nama_template.required' => 'Nama template wajib diisi.',
            'lingkup.required' => 'Level lingkup wajib dipilih.',
            'plotting_id.required' => 'Wilayah plotting (WIL) wajib dipilih untuk level Nasional.',
            'kode_wilayah.required' => 'Provinsi wajib dipilih untuk level Wilayah.',
        ];
    }
}
