<?php

namespace App\Http\Requests\Audit;

use App\Models\Pka;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PkaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('template_pka_id') && ($this->input('template_pka_id') === '' || $this->input('template_pka_id') === 'null')) {
            $this->merge(['template_pka_id' => null]);
        }

        if ($this->has('anggaran') && $this->input('anggaran') === '') {
            $this->merge(['anggaran' => 0]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $pka = $this->route('pka');
        $pkaId = $pka instanceof Pka ? $pka->id : $pka;
        $existingPka = $pkaId ? Pka::query()->find($pkaId) : null;

        $sprinRules = ['required', 'integer'];
        if (! $existingPka || (int) $this->input('sprin_id') !== (int) $existingPka->sprin_id) {
            $sprinRules[] = Rule::exists('sprins', 'id')->whereNotNull('approved_by_daltu_date');
        } else {
            $sprinRules[] = Rule::exists('sprins', 'id');
        }

        return [
            'sprin_id' => $sprinRules,
            'nomor_pka' => ['required', 'string', 'max:255'],
            'tanggal' => ['required', 'date'],
            'anggaran' => ['required', 'numeric', 'min:0'],
            'bidang' => ['required', 'string', Rule::in(Pka::BIDANGS)],
            'jenis_audit' => ['required', 'string', Rule::in(Pka::JENIS_AUDITS)],
            'sasaran_auditi' => ['required', 'string', 'max:255'],
            'status' => ['required', 'string', Rule::in(Pka::STATUSES)],
            'lampiran' => ['nullable', 'file', 'max:10240', 'mimes:pdf,doc,docx,xls,xlsx,zip,rar,png,jpg,jpeg'],
            'template_pka_id' => ['nullable', 'integer', Rule::exists('template_pkas', 'id')],
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
            'sprin_id.required' => 'Nomor Sprin wajib dipilih.',
            'sprin_id.exists' => 'Nomor Sprin yang dipilih harus sudah disetujui / diapprove.',
            'nomor_pka.required' => 'Nomor PKA wajib diisi.',
            'tanggal.required' => 'Tanggal PKA wajib diisi.',
            'tanggal.date' => 'Format tanggal tidak valid.',
            'anggaran.required' => 'Anggaran wajib diisi.',
            'anggaran.numeric' => 'Anggaran harus berupa angka.',
            'bidang.required' => 'Bidang audit wajib dipilih.',
            'bidang.in' => 'Bidang audit yang dipilih tidak valid.',
            'jenis_audit.required' => 'Jenis audit wajib dipilih.',
            'jenis_audit.in' => 'Jenis audit yang dipilih tidak valid.',
            'sasaran_auditi.required' => 'Sasaran auditi wajib diisi.',
            'status.required' => 'Status PKA (Draft / Final) wajib dipilih.',
            'status.in' => 'Status PKA harus berupa Draft atau Final.',
            'lampiran.max' => 'Ukuran file lampiran maksimal 10MB.',
            'lampiran.mimes' => 'Format file lampiran harus PDF, Word, Excel, ZIP, atau Gambar.',
        ];
    }
}
