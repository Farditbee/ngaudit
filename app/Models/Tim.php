<?php

namespace App\Models;

use Database\Factories\TimFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tim extends Model
{
    /** @use HasFactory<TimFactory> */
    use HasFactory, SoftDeletes;

    /**
     * @var list<string>
     */
    public const LINGKUPS = [
        'Dinas',
        'Kecamatan',
        'Nasional',
        'Wilayah',
    ];

    protected $appends = [
        'plotting_id',
    ];

    /**
     * @var list<string>
     */
    protected $fillable = [
        'nama_tim',
        'lingkup',
        'struktur_perangkat_daerah_id',
        'plotting_id',
        'province_code',
        'daltu_id',
        'dalnis_id',
        'ketua_tim_id',
        'ketua_tim_2_id',
        'jumlah_anggota',
        'created_by',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $tim): void {
            if (empty($tim->created_by) && auth()->check()) {
                $tim->created_by = auth()->id();
            }
        });
    }

    /**
     * Batasi tim sesuai lingkup user yang sedang login.
     *
     * @param  Builder<Tim>  $query
     */
    public function scopeVisibleTo(Builder $query, ?User $user): void
    {
        if (! $user || $user->role === 'Super Admin') {
            return;
        }

        if (in_array($user->role, ['Admin Nasional', 'Admin Dinas'], true)) {
            $query->whereIn('tims.lingkup', ['Nasional', 'Dinas']);

            if ($user->struktur_perangkat_daerah_id) {
                $unitIds = StrukturPerangkatDaerah::getDescendantIds((int) $user->struktur_perangkat_daerah_id);
                $query->whereIn('tims.struktur_perangkat_daerah_id', $unitIds);
            }
        } elseif (in_array($user->role, ['Admin Wilayah', 'Admin Kecamatan'], true)) {
            $query->whereIn('tims.lingkup', ['Wilayah', 'Kecamatan']);

            if ($user->struktur_perangkat_daerah_id) {
                $unitIds = StrukturPerangkatDaerah::getDescendantIds((int) $user->struktur_perangkat_daerah_id);
                $query->whereIn('tims.struktur_perangkat_daerah_id', $unitIds);
            } elseif ($user->kode_wilayah) {
                $cleanAuthWilayah = str_replace('.', '', (string) $user->kode_wilayah);
                $query->whereRaw("REPLACE(tims.province_code, '.', '') LIKE ?", [$cleanAuthWilayah.'%']);
            }
        } elseif ($user->role === 'Koordinator dan Pengendali') {
            if ($user->struktur_perangkat_daerah_id) {
                $unitIds = StrukturPerangkatDaerah::getDescendantIds((int) $user->struktur_perangkat_daerah_id);
                $query->whereIn('tims.struktur_perangkat_daerah_id', $unitIds);
            } elseif ($user->kode_wilayah) {
                $cleanAuthWilayah = str_replace('.', '', (string) $user->kode_wilayah);
                $query->whereIn('tims.lingkup', ['Wilayah', 'Kecamatan'])
                    ->whereRaw("REPLACE(tims.province_code, '.', '') LIKE ?", [$cleanAuthWilayah.'%']);
            } else {
                $query->where('tims.daltu_id', $user->id);
            }
        }
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

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function plotting(): BelongsTo
    {
        return $this->belongsTo(StrukturPerangkatDaerah::class, 'struktur_perangkat_daerah_id');
    }

    public function province(): BelongsTo
    {
        return $this->belongsTo(Wilayah::class, 'province_code', 'kode_wilayah');
    }

    public function strukturPerangkatDaerah(): BelongsTo
    {
        return $this->belongsTo(StrukturPerangkatDaerah::class, 'struktur_perangkat_daerah_id');
    }

    public function daltu(): BelongsTo
    {
        return $this->belongsTo(User::class, 'daltu_id');
    }

    public function dalnis(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dalnis_id');
    }

    public function ketuaTim(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ketua_tim_id');
    }

    public function ketuaTim2(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ketua_tim_2_id');
    }

    public function auditors(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'tim_auditor',
            'tim_id',
            'user_id'
        )->withTimestamps()->orderBy('users.name');
    }
}
