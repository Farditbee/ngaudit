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
        'Nasional',
        'Wilayah',
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

        $query->whereHas('pka', function (Builder $pkaQuery) use ($user): void {
            $pkaQuery->visibleTo($user);
        });
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
        return $this->belongsTo(Plotting::class, 'auditee_plotting_id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(KkaReview::class)->latest('id');
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
