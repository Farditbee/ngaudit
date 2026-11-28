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
    'struktur_perangkat_daerah_id',
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
        'Admin Dinas',
        'Admin Kecamatan',
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
        'Dinas',
        'Kecamatan',
    ];

    /**
     * @var list<string>
     */
    public const ADMIN_NASIONAL_LEVELS = [
        'Dinas',
    ];

    /**
     * @var list<string>
     */
    public const ADMIN_WILAYAH_LEVELS = [
        'Kecamatan',
    ];

    /**
     * @var list<string>
     */
    public const AUDITOR_LEVELS = [
        'Dinas',
        'Kecamatan',
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

    public function setPlottingIdAttribute(mixed $value): void
    {
        $this->attributes['struktur_perangkat_daerah_id'] = $value;
        unset($this->attributes['plotting_id']);
    }

    public function getPlottingIdAttribute(): mixed
    {
        return $this->attributes['struktur_perangkat_daerah_id'] ?? null;
    }

    public function plotting(): BelongsTo
    {
        return $this->belongsTo(StrukturPerangkatDaerah::class, 'struktur_perangkat_daerah_id');
    }

    public function strukturPerangkatDaerah(): BelongsTo
    {
        return $this->belongsTo(StrukturPerangkatDaerah::class, 'struktur_perangkat_daerah_id');
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
