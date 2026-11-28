<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Kka extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * @var list<string>
     */
    public const STATUSES = [
        'Draft',
        'Final',
    ];

    /**
     * @var list<string>
     */
    public const AUDITEE_LEVELS = [
        'Dinas',
        'Kecamatan',
    ];

    protected $appends = [
        'auditee_plotting_id',
    ];

    /**
     * @var list<string>
     */
    protected $fillable = [
        'nomor_kka',
        'pka_id',
        'dibuat_oleh_id',
        'direviu_oleh_id',
        'tanggal_dibuat',
        'tanggal_direviu',
        'tanggal_kka',
        'tempat',
        'auditee_level',
        'struktur_perangkat_daerah_id',
        'auditee_plotting_id',
        'auditee_kode_wilayah',
        'template_kka_id',
        'tujuan',
        'langkah',
        'hasil',
        'status',
        'approved_by_katim_date',
        'approved_by_dalnis_date',
        'approved_by_daltu_date',
        'created_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tanggal_dibuat' => 'date',
            'tanggal_direviu' => 'date',
            'tanggal_kka' => 'date',
            'approved_by_katim_date' => 'datetime',
            'approved_by_dalnis_date' => 'datetime',
            'approved_by_daltu_date' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $kka): void {
            if (auth()->check()) {
                $kka->created_by ??= auth()->id();
            }
        });
    }

    /**
     * Batasi KKA sesuai hak akses user terhadap PKA (dan Sprin) terkait.
     *
     * @param  Builder<Kka>  $query
     */
    public function scopeVisibleTo(Builder $query, ?User $user): void
    {
        if (! $user || $user->role === 'Super Admin') {
            return;
        }

        if ($user->role === 'Auditor') {
            $query->where(function (Builder $q) use ($user): void {
                $q->where('kkas.dibuat_oleh_id', $user->id)
                    ->orWhere('kkas.created_by', $user->id);
            });

            return;
        }

        if ($user->role === 'Auditee') {
            if ($user->plotting_id) {
                $query->where('kkas.struktur_perangkat_daerah_id', $user->plotting_id);
            } elseif ($user->kode_wilayah) {
                $cleanCode = str_replace('.', '', (string) $user->kode_wilayah);
                $query->where('kkas.auditee_kode_wilayah', 'like', $cleanCode.'%');
            }

            return;
        }

        $query->where(function (Builder $q) use ($user): void {
            $q->where('kkas.created_by', $user->id)
                ->orWhereHas('pka', function (Builder $pkaQuery) use ($user): void {
                    $pkaQuery->visibleTo($user);
                });
        });
    }

    /**
     * Scope akses khusus modul Tindak Lanjut Auditee:
     * - Status KKA harus 'Final'
     * - Super Admin dapat melihat semua KKA Final
     * - Auditor hanya melihat KKA yang ditugaskan kepadanya (dibuat_oleh_id atau created_by)
     * - Auditee hanya melihat KKA dengan level yang sama (Dinas / Kecamatan)
     *   serta unit yang sesuai (unit itu sendiri atau sub-unit / turunannya)
     *
     * @param  Builder<Kka>  $query
     */
    public function scopeVisibleForTindakLanjut(Builder $query, ?User $user): void
    {
        if (! $user) {
            $query->whereRaw('1 = 0');

            return;
        }

        $query->where('kkas.status', 'Final');

        if ($user->role === 'Super Admin') {
            return;
        }

        if ($user->role === 'Auditor') {
            $query->where(function (Builder $q) use ($user): void {
                $q->where('kkas.dibuat_oleh_id', $user->id)
                    ->orWhere('kkas.created_by', $user->id);
            });

            return;
        }

        if (in_array($user->role, ['Auditee', 'Admin Dinas', 'Admin Kecamatan'], true)) {
            if (! empty($user->level)) {
                $query->where('kkas.auditee_level', $user->level);
            }

            if (! empty($user->bidang) && $user->bidang !== 'SEMUA_BIDANG') {
                $query->whereHas('pka', function (Builder $q) use ($user): void {
                    $q->where('pkas.bidang', $user->bidang);
                });
            }

            $userSpdId = $user->struktur_perangkat_daerah_id ?: $user->plotting_id;
            if ($userSpdId) {
                $query->where(function (Builder $q) use ($userSpdId, $user): void {
                    $q->where('kkas.struktur_perangkat_daerah_id', $userSpdId);
                    if ($user->kode_wilayah) {
                        $cleanCode = str_replace('.', '', (string) $user->kode_wilayah);
                        $q->orWhere('kkas.auditee_kode_wilayah', $cleanCode);
                    }
                });

                return;
            }

            if ($user->kode_wilayah) {
                $cleanCode = str_replace('.', '', (string) $user->kode_wilayah);
                $query->where('kkas.auditee_kode_wilayah', $cleanCode);

                return;
            }

            return;
        }

        if (in_array($user->role, ['Ketua Tim', 'Pengawas Tim', 'Koordinator dan Pengendali'], true)) {
            $query->whereHas('pka', function (Builder $pkaQuery) use ($user): void {
                $pkaQuery->visibleTo($user);
            });

            return;
        }

        $query->whereRaw('1 = 0');
    }

    public function setAuditeePlottingIdAttribute(mixed $value): void
    {
        $this->attributes['struktur_perangkat_daerah_id'] = $value;
        unset($this->attributes['auditee_plotting_id']);
    }

    public function getAuditeePlottingIdAttribute(): mixed
    {
        return $this->attributes['struktur_perangkat_daerah_id'] ?? null;
    }

    public function pka(): BelongsTo
    {
        return $this->belongsTo(Pka::class);
    }

    public function templateKka(): BelongsTo
    {
        return $this->belongsTo(TemplateKka::class, 'template_kka_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function dibuatOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dibuat_oleh_id');
    }

    public function direviuOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'direviu_oleh_id');
    }

    public function auditeePlotting(): BelongsTo
    {
        return $this->belongsTo(StrukturPerangkatDaerah::class, 'struktur_perangkat_daerah_id');
    }

    public function auditeePerangkatDaerah(): BelongsTo
    {
        return $this->belongsTo(StrukturPerangkatDaerah::class, 'struktur_perangkat_daerah_id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(KkaReview::class)->latest('id');
    }

    public function tabulasis(): HasMany
    {
        return $this->hasMany(KkaTabulasi::class)->orderBy('id');
    }

    public function latestKatimReview(): HasOne
    {
        return $this->latestReviewOfType(KkaReview::TYPE_KATIM);
    }

    public function latestDalnisReview(): HasOne
    {
        return $this->latestReviewOfType(KkaReview::TYPE_DALNIS);
    }

    public function latestDaltuReview(): HasOne
    {
        return $this->latestReviewOfType(KkaReview::TYPE_DALTU);
    }

    /**
     * Pecah kode wilayah auditee menjadi kode provinsi, kabupaten/kota, kecamatan, dan desa.
     *
     * @return array{province_code: string, city_code: string, district_code: string, village_code: string}
     */
    public function auditeeRegionCodes(): array
    {
        $code = (string) $this->auditee_kode_wilayah;

        return [
            'province_code' => strlen($code) >= 2 ? substr($code, 0, 2) : '',
            'city_code' => strlen($code) >= 4 ? substr($code, 0, 4) : '',
            'district_code' => strlen($code) >= 6 ? substr($code, 0, 6) : '',
            'village_code' => strlen($code) >= 10 ? substr($code, 0, 10) : '',
        ];
    }

    private function latestReviewOfType(string $type): HasOne
    {
        return $this->hasOne(KkaReview::class)
            ->ofMany(['id' => 'max'], function (Builder $query) use ($type): void {
                $query->where('role_type', $type);
            });
    }
}
