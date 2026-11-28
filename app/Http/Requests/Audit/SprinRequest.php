<?php

namespace App\Http\Requests\Audit;

use App\Http\Controllers\Audit\SprinController;
use App\Models\Sprin;
use App\Models\Tim;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SprinRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        if (! $user) {
            return false;
        }

        $sprin = $this->route('sprin');
        if ($sprin instanceof Sprin) {
            return app(SprinController::class)->canAccessSprin($user, $sprin);
        }

        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $isUpdate = $this->route('sprin') instanceof Sprin;
        $visibleTimIds = Tim::query()->visibleTo($this->user())->select('tims.id');

        $uploadRules = fn (bool $required): array => [
            $required ? 'required' : 'nullable',
            'file',
            'mimes:pdf,doc,docx,jpg,jpeg,png',
            'max:10240',
        ];

        return [
            'no_sprin' => [
                'required',
                'string',
                'max:255',
                Rule::unique('sprins', 'no_sprin')
                    ->whereNull('deleted_at')
                    ->ignore($isUpdate ? $this->route('sprin')->id : null),
            ],
            'file_surat_perintah' => $uploadRules(! $isUpdate),
            'no_surat_pemberitahuan' => ['required', 'string', 'max:255'],
            'file_surat_pemberitahuan' => $uploadRules(! $isUpdate),
            'tim_id' => ['required', 'integer', Rule::exists('tims', 'id')->whereIn('id', $visibleTimIds)->whereNull('deleted_at')],
            'tahap' => ['required', 'string', Rule::in(Sprin::TAHAPS)],
            'tgl_mulai' => ['required', 'date'],
            'tgl_akhir' => ['required', 'date', 'after_or_equal:tgl_mulai'],
            'keterangan' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'no_sprin.required' => 'Nomor Sprin wajib diisi.',
            'no_sprin.unique' => 'Nomor Sprin sudah digunakan.',
            'file_surat_perintah.required' => 'Dokumen Sprin wajib diunggah.',
            'file_surat_perintah.mimes' => 'Dokumen Sprin harus berformat PDF, DOC, DOCX, JPG, atau PNG.',
            'file_surat_perintah.max' => 'Ukuran dokumen Sprin maksimal 10MB.',
            'no_surat_pemberitahuan.required' => 'Nomor surat pemberitahuan wajib diisi.',
            'file_surat_pemberitahuan.required' => 'Dokumen surat pemberitahuan wajib diunggah.',
            'file_surat_pemberitahuan.mimes' => 'Dokumen surat pemberitahuan harus berformat PDF, DOC, DOCX, JPG, atau PNG.',
            'file_surat_pemberitahuan.max' => 'Ukuran dokumen surat pemberitahuan maksimal 10MB.',
            'tim_id.required' => 'Nama tim wajib dipilih.',
            'tim_id.exists' => 'Tim yang dipilih tidak valid.',
            'tahap.required' => 'Tahap wajib dipilih.',
            'tahap.in' => 'Tahap harus 1, 2, atau Khusus.',
            'tgl_mulai.required' => 'Tanggal awal Sprin wajib diisi.',
            'tgl_akhir.required' => 'Tanggal akhir Sprin wajib diisi.',
            'tgl_akhir.after_or_equal' => 'Tanggal akhir tidak boleh sebelum tanggal awal.',
        ];
    }
}
