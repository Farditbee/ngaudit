<?php

namespace App\Http\Requests\Master;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserRequest extends FormRequest
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
        $prefix = config('laravolt.indonesia.table_prefix', 'indonesia_');
        /** @var User|null $user */
        $user = $this->route('user');
        $isUpdate = $user !== null;

        $role = (string) $this->input('role', '');
        $level = (string) $this->input('level', '');
        $needsScope = in_array($role, [
            'Auditor',
            'Auditee',
            'Ketua Tim',
            'Koordinator dan Pengendali',
            'Pengawas Tim',
        ], true);
        $isAuditee = $role === 'Auditee';

        return [
            'name' => ['required', 'string', 'max:255'],
            'username' => [
                'required',
                'string',
                'max:100',
                Rule::unique('users', 'username')->ignore($user),
            ],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user),
            ],
            'phone' => ['required', 'string', 'max:30'],
            'role' => ['required', 'string', Rule::in(User::ROLES)],
            'password' => [
                $isUpdate ? 'nullable' : 'required',
                'string',
                'min:8',
                'confirmed',
            ],
            'level' => [
                Rule::requiredIf($needsScope),
                'nullable',
                'string',
                Rule::in(User::LEVELS),
            ],
            'plotting_id' => [
                Rule::requiredIf($needsScope && $level === 'Nasional'),
                'nullable',
                'integer',
                Rule::exists('plottings', 'id'),
            ],
            'province_code' => [
                Rule::requiredIf($needsScope && in_array($level, ['Provinsi', 'Kabupaten', 'Kecamatan', 'Desa'], true)),
                'nullable',
                'string',
                Rule::exists($prefix.'provinces', 'code'),
            ],
            'city_code' => [
                Rule::requiredIf($needsScope && in_array($level, ['Kabupaten', 'Kecamatan', 'Desa'], true)),
                'nullable',
                'string',
                Rule::exists($prefix.'cities', 'code'),
            ],
            'district_code' => [
                Rule::requiredIf($needsScope && in_array($level, ['Kecamatan', 'Desa'], true)),
                'nullable',
                'string',
                Rule::exists($prefix.'districts', 'code'),
            ],
            'village_code' => [
                Rule::requiredIf($needsScope && $level === 'Desa'),
                'nullable',
                'string',
                Rule::exists($prefix.'villages', 'code'),
            ],
            'bidang' => [
                Rule::requiredIf($isAuditee),
                'nullable',
                'string',
                Rule::in(User::BIDANGS),
            ],
            'jabatan' => [
                'nullable',
                'string',
                'max:255',
            ],
            'ttd' => [
                'nullable',
                'image',
                'mimes:png,jpg,jpeg,webp',
                'max:2048',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama lengkap wajib diisi.',
            'username.required' => 'Username wajib diisi.',
            'username.unique' => 'Username sudah digunakan.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah terdaftar.',
            'phone.required' => 'Nomor HP wajib diisi.',
            'role.required' => 'Peran wajib dipilih.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'level.required' => 'Level wilayah wajib dipilih.',
            'plotting_id.required' => 'Plot wilayah (WIL) wajib dipilih.',
            'province_code.required' => 'Provinsi wajib dipilih.',
            'city_code.required' => 'Kabupaten/Kota wajib dipilih.',
            'district_code.required' => 'Kecamatan wajib dipilih.',
            'village_code.required' => 'Desa/Kelurahan wajib dipilih.',
            'bidang.required' => 'Bidang wajib dipilih untuk Auditee.',
            'jabatan.required' => 'Jabatan wajib diisi untuk Auditee.',
            'ttd.required' => 'File tanda tangan (TTD) wajib diunggah untuk Auditee.',
            'ttd.image' => 'File TTD harus berupa gambar (PNG/JPG/WEBP).',
            'ttd.max' => 'Ukuran file TTD maksimal 2MB.',
        ];
    }
}
