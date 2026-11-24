<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
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
    'kode_wilayah',
    'bidang',
    'jabatan',
    'ttd_path',
    'created_by',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * @var list<string>
     */
    public const ROLES = [
        'Super Admin',
        'Admin Nasional',
        'Admin Wilayah',
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
        'Wilayah',
        'Provinsi',
        'Kabupaten',
        'Kecamatan',
        'Desa',
    ];

    /**
     * @var list<string>
     */
    public const ADMIN_NASIONAL_LEVELS = [
        'Nasional',
    ];

    /**
     * @var list<string>
     */
    public const ADMIN_WILAYAH_LEVELS = [
        'Provinsi',
        'Kabupaten',
        'Kecamatan',
        'Desa',
    ];

    /**
     * @var list<string>
     */
    public const AUDITOR_LEVELS = [
        'Nasional',
        'Wilayah',
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

    protected static function booted(): void
    {
        static::creating(function (self $user): void {
            if (empty($user->created_by) && auth()->check()) {
                $user->created_by = auth()->id();
            }
        });
    }

    public function plotting(): BelongsTo
    {
        return $this->belongsTo(Plotting::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(self::class, 'created_by');
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
