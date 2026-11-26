<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Pka extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * @var list<string>
     */
    public const BIDANGS = User::BIDANGS;

    /**
     * @var list<string>
     */
    public const JENIS_AUDITS = [
        'Audit Kinerja',
        'Audit PNBP',
        'Audit Tujuan Tertentu',
    ];

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
    protected $fillable = [
        'sprin_id',
        'nomor_pka',
        'tanggal',
        'anggaran',
        'bidang',
        'jenis_audit',
        'sasaran_auditi',
        'status',
        'approved_by_dalnis_date',
        'approved_by_daltu_date',
        'lampiran_path',
        'lampiran_name',
        'template_pka_id',
        'pendahuluan',
        'tujuan',
        'ruang_lingkup',
        'sasaran',
        'waktu_pelaksanaan',
        'periode',
        'penutup',
        'created_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'anggaran' => 'decimal:2',
            'approved_by_dalnis_date' => 'datetime',
            'approved_by_daltu_date' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $pka): void {
            if (auth()->check()) {
                $pka->created_by ??= auth()->id();
            }
        });
    }

    /**
     * Batasi PKA sesuai hak akses dan keterikatan user dengan Sprin.
     *
     * @param  Builder<Pka>  $query
     */
    public function scopeVisibleTo(Builder $query, ?User $user): void
    {
        if (! $user || $user->role === 'Super Admin') {
            return;
        }

        $query->whereHas('sprin', function (Builder $sprinQuery) use ($user): void {
            $sprinQuery->visibleTo($user);
        });
    }

    public function sprin(): BelongsTo
    {
        return $this->belongsTo(Sprin::class);
    }

    public function templatePka(): BelongsTo
    {
        return $this->belongsTo(TemplatePka::class, 'template_pka_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(PkaReview::class)->latest('id');
    }

    public function langkahKerjas(): HasMany
    {
        return $this->hasMany(PkaLangkahKerja::class, 'pka_id')->orderBy('id');
    }

    public function dalnisReviews(): HasMany
    {
        return $this->hasMany(PkaReview::class)
            ->where('role_type', PkaReview::TYPE_DALNIS)
            ->latest('id');
    }

    public function daltuReviews(): HasMany
    {
        return $this->hasMany(PkaReview::class)
            ->where('role_type', PkaReview::TYPE_DALTU)
            ->latest('id');
    }

    public function latestDalnisReview(): HasOne
    {
        return $this->hasOne(PkaReview::class)
            ->ofMany(['id' => 'max'], function (Builder $query): void {
                $query->where('role_type', PkaReview::TYPE_DALNIS);
            });
    }

    public function latestDaltuReview(): HasOne
    {
        return $this->hasOne(PkaReview::class)
            ->ofMany(['id' => 'max'], function (Builder $query): void {
                $query->where('role_type', PkaReview::TYPE_DALTU);
            });
    }

    public function lampiranUrl(): ?string
    {
        if (! $this->lampiran_path) {
            return null;
        }

        return Storage::disk('public')->url($this->lampiran_path);
    }

    public function formattedAnggaran(): string
    {
        return 'Rp '.number_format((float) $this->anggaran, 0, ',', '.');
    }
}
