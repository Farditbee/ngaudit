<?php

namespace App\Http\Requests\Master;

use App\Models\User;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
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
        /** @var User|null $user */
        $user = $this->route('user');
        $isUpdate = $user !== null;

        $authUser = $this->user();
        $isAdminNasional = in_array($authUser?->role, ['Admin Nasional', 'Admin Dinas'], true);
        $isAdminWilayah = in_array($authUser?->role, ['Admin Wilayah', 'Admin Kecamatan'], true);
        $authProvinceCode = ($isAdminWilayah && $authUser?->kode_wilayah)
            ? substr(str_replace('.', '', (string) $authUser->kode_wilayah), 0, 2)
            : null;

        $role = (string) $this->input('role', '');
        $level = (string) $this->input('level', '');
        $isAuditTeamRole = in_array($role, [
            'Auditor',
            'Ketua Tim',
            'Koordinator dan Pengendali',
            'Pengawas Tim',
        ], true);
        $needsScope = in_array($role, [
            'Admin Dinas',
            'Admin Kecamatan',
            'Admin Nasional',
            'Admin Wilayah',
            'Auditor',
            'Auditee',
            'Ketua Tim',
            'Koordinator dan Pengendali',
            'Pengawas Tim',
        ], true);
        $isAuditee = $role === 'Auditee';

        $allowedRoles = match (true) {
            $isAdminNasional => array_values(array_diff(User::ROLES, ['Super Admin', 'Admin Kecamatan'])),
            $isAdminWilayah => array_values(array_diff(User::ROLES, ['Super Admin', 'Admin Dinas'])),
            default => User::ROLES,
        };

        $allowedLevels = match (true) {
            $isAdminNasional || $role === 'Admin Dinas' => User::ADMIN_NASIONAL_LEVELS,
            $isAdminWilayah || $role === 'Admin Kecamatan' => User::ADMIN_WILAYAH_LEVELS,
            $isAuditTeamRole => User::AUDITOR_LEVELS,
            default => User::LEVELS,
        };

        $plottingRules = [
            'nullable',
            'integer',
        ];

        $spdRules = [
            'nullable',
            'integer',
            Rule::exists('struktur_perangkat_daerah', 'id'),
        ];

        $provinceRules = [
            'nullable',
            'string',
        ];

        return [
            'name' => ['required', 'string', 'max:255'],
            'username' => [
                'required',
                'string',
                'max:100',
                Rule::unique('users', 'username')->ignore($user),
                function (string $attribute, mixed $value, Closure $fail) use ($isUpdate, $isAuditee): void {
                    if ($isUpdate || ! $isAuditee || $this->input('bidang') !== 'SEMUA_BIDANG') {
                        return;
                    }
                    $baseUsername = trim((string) $value);
                    $generated = array_map(
                        fn (string $b): string => $baseUsername.'_'.Str::slug($b, '_'),
                        User::BIDANGS
                    );
                    if (User::withTrashed()->whereIn('username', $generated)->exists()) {
                        $fail('Salah satu username turunan bidang sudah digunakan.');
                    }
                },
            ],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user),
                function (string $attribute, mixed $value, Closure $fail) use ($isUpdate, $isAuditee): void {
                    if ($isUpdate || ! $isAuditee || $this->input('bidang') !== 'SEMUA_BIDANG') {
                        return;
                    }
                    $baseEmail = trim((string) $value);
                    if (! str_contains($baseEmail, '@')) {
                        return;
                    }
                    [$local, $domain] = explode('@', $baseEmail, 2);
                    $generated = array_map(
                        fn (string $b): string => $local.'_'.Str::slug($b, '_').'@'.$domain,
                        User::BIDANGS
                    );
                    if (User::withTrashed()->whereIn('email', $generated)->exists()) {
                        $fail('Salah satu email turunan bidang sudah terdaftar.');
                    }
                },
            ],
            'phone' => ['required', 'string', 'max:30'],
            'role' => ['required', 'string', Rule::in($allowedRoles)],
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
                Rule::in($allowedLevels),
            ],
            'unit_level_1' => ['nullable', 'integer'],
            'unit_level_2' => ['nullable', 'integer'],
            'unit_level_3' => ['nullable', 'integer'],
            'struktur_perangkat_daerah_id' => $spdRules,
            'plotting_id' => $plottingRules,
            'province_code' => $provinceRules,
            'city_code' => [
                'nullable',
                'string',
            ],
            'district_code' => [
                'nullable',
                'string',
            ],
            'village_code' => [
                'nullable',
                'string',
            ],
            'bidang' => [
                Rule::requiredIf($isAuditee),
                'nullable',
                'string',
                Rule::in($isUpdate ? User::BIDANGS : ['SEMUA_BIDANG', ...User::BIDANGS]),
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
