<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable([
    'name',
    'username',
    'email',
    'phone',
    'role',
    'password',
    'level',
    'plotting_id',
    'province_code',
    'city_code',
    'district_code',
    'village_code',
    'bidang',
    'jabatan',
    'ttd_path',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * @var list<string>
     */
    public const ROLES = [
        'Super Admin',
        'Auditor',
        'Auditee',
        'Ketua Tim',
        'Koordinator dan Pengendali',
        'Pengawas Tim',
    ];

    /**
     * @var list<string>
     */
    public const LEVELS = [
        'Nasional',
        'Provinsi',
        'Kabupaten',
        'Kecamatan',
        'Desa',
    ];

    /**
     * @var list<string>
     */
    public const BIDANGS = [
        'Operasional',
        'Manajemen Anggaran dan Keuangan',
        'Logistik',
        'Sumber Daya Manusia',
    ];

    public function plotting(): BelongsTo
    {
        return $this->belongsTo(Plotting::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
