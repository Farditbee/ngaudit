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
            'struktur_perangkat_daerah_id',
        ];

        foreach ($nullableKeys as $key) {
            if ($this->has($key) && ($this->input($key) === '' || $this->input($key) === 'null')) {
                $this->merge([$key => null]);
            }
        }

        if (empty($this->input('struktur_perangkat_daerah_id')) && $this->filled('auditee_plotting_id')) {
            $this->merge(['struktur_perangkat_daerah_id' => (int) $this->input('auditee_plotting_id')]);
        }

        if (empty($this->input('auditee_plotting_id')) && $this->filled('struktur_perangkat_daerah_id')) {
            $this->merge(['auditee_plotting_id' => (int) $this->input('struktur_perangkat_daerah_id')]);
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

        return [
            'nomor_kka' => ['required', 'string', 'max:255'],
            'pka_id' => $pkaRules,
            'tanggal_dibuat' => ['nullable', 'date'],
            'tanggal_direviu' => ['nullable', 'date'],
            'tanggal_kka' => ['required', 'date'],
            'tempat' => ['required', 'string', 'max:255'],
            'auditee_level' => ['required', 'string', Rule::in(Kka::AUDITEE_LEVELS)],
            'struktur_perangkat_daerah_id' => ['required', 'integer', Rule::exists('struktur_perangkat_daerah', 'id')],
            'auditee_plotting_id' => ['nullable', 'integer'],
            'template_kka_id' => ['nullable', 'integer', Rule::exists('template_kkas', 'id')->whereNull('deleted_at')],
            'tujuan' => ['nullable', 'string'],
            'langkah' => ['nullable', 'string'],
            'hasil' => ['nullable', 'string'],
            'status' => ['required', 'string', Rule::in(Kka::STATUSES)],
        ];
    }

    public function withValidator(Validator $validator): void {}

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
            'struktur_perangkat_daerah_id.required' => 'Perangkat daerah / Kecamatan wajib dipilih.',
            'status.required' => 'Status KKA (Draft / Final) wajib dipilih.',
            'status.in' => 'Status KKA harus berupa Draft atau Final.',
        ];
    }
}
