<?php

namespace App\Models;

use Database\Factories\SprinFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Sprin extends Model
{
    /** @use HasFactory<SprinFactory> */
    use HasFactory, SoftDeletes;

    /**
     * @var list<string>
     */
    public const TAHAPS = ['1', '2', 'Khusus'];

    protected $appends = [
        'plotting_id',
    ];

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'no_sprin',
        'no_surat_pemberitahuan',
        'file_surat_perintah_id',
        'file_surat_perintah_name',
        'file_surat_pemberitahuan_id',
        'file_surat_pemberitahuan_name',
        'tgl_mulai',
        'tgl_akhir',
        'struktur_perangkat_daerah_id',
        'plotting_id',
        'kode_wilayah',
        'tim_id',
        'katim',
        'katim_2',
        'dalnis',
        'daltu',
        'tahap',
        'sprin_tipe',
        'keterangan',
        'approved_by_daltu_date',
        'created_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tgl_mulai' => 'date',
            'tgl_akhir' => 'date',
            'approved_by_daltu_date' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $sprin): void {
            if (auth()->check()) {
                $sprin->created_by ??= auth()->id();
                $sprin->user_id ??= auth()->id();
            }
        });
    }

    /**
     * Batasi Sprin sesuai hak akses user yang sedang login.
     *
     * @param  Builder<Sprin>  $query
     */
    public function scopeVisibleTo(Builder $query, ?User $user): void
    {
        if (! $user || $user->role === 'Super Admin') {
            return;
        }

        if (in_array($user->role, ['Admin Nasional', 'Admin Dinas'], true) && $user->plotting_id) {
            $query->whereIn('sprins.sprin_tipe', ['Nasional', 'Dinas'])
                ->where('sprins.struktur_perangkat_daerah_id', $user->plotting_id);
        } elseif (in_array($user->role, ['Admin Wilayah', 'Admin Kecamatan'], true) && $user->kode_wilayah) {
            $provinceCode = substr(str_replace('.', '', (string) $user->kode_wilayah), 0, 2);
            $query->whereIn('sprins.sprin_tipe', ['Wilayah', 'Kecamatan'])
                ->where('sprins.kode_wilayah', $provinceCode);
        } elseif ($user->role === 'Koordinator dan Pengendali') {
            if ($user->plotting_id) {
                $query->where('sprins.struktur_perangkat_daerah_id', $user->plotting_id);
            } elseif ($user->kode_wilayah) {
                $provinceCode = substr(str_replace('.', '', (string) $user->kode_wilayah), 0, 2);
                $query->whereIn('sprins.sprin_tipe', ['Wilayah', 'Kecamatan'])
                    ->where('sprins.kode_wilayah', $provinceCode);
            } else {
                $query->where('sprins.daltu', $user->id);
            }
        } elseif (in_array($user->role, ['Ketua Tim', 'Pengawas Tim', 'Auditor', 'Auditee'])) {
            $query->where(function (Builder $q) use ($user): void {
                $q->where('sprins.katim', $user->id)
                    ->orWhere('sprins.katim_2', $user->id)
                    ->orWhere('sprins.dalnis', $user->id)
                    ->orWhere('sprins.daltu', $user->id)
                    ->orWhere('sprins.created_by', $user->id)
                    ->orWhere('sprins.user_id', $user->id)
                    ->orWhereHas('tim.auditors', function (Builder $auditorQuery) use ($user): void {
                        $auditorQuery->where('users.id', $user->id);
                    });
            });
        }
    }

    public function scopeApproved(Builder $query): void
    {
        $query->whereNotNull('sprins.approved_by_daltu_date');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tim(): BelongsTo
    {
        return $this->belongsTo(Tim::class);
    }

    public function plotting(): BelongsTo
    {
        return $this->belongsTo(StrukturPerangkatDaerah::class, 'struktur_perangkat_daerah_id');
    }

    public function province(): BelongsTo
    {
        return $this->belongsTo(Wilayah::class, 'kode_wilayah', 'kode_wilayah');
    }

    public function ketuaTim(): BelongsTo
    {
        return $this->belongsTo(User::class, 'katim');
    }

    public function ketuaTim2(): BelongsTo
    {
        return $this->belongsTo(User::class, 'katim_2');
    }

    public function pengawasTim(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dalnis');
    }

    public function pengendaliMutu(): BelongsTo
    {
        return $this->belongsTo(User::class, 'daltu');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(SprinReview::class)->latest('id');
    }

    public function pkas(): HasMany
    {
        return $this->hasMany(Pka::class)->latest('id');
    }

    public function latestReview(): HasOne
    {
        return $this->hasOne(SprinReview::class)->latestOfMany();
    }

    public function suratPerintahUrl(): ?string
    {
        return $this->file_surat_perintah_id
            ? asset('storage/'.$this->file_surat_perintah_id)
            : null;
    }

    public function suratPemberitahuanUrl(): ?string
    {
        return $this->file_surat_pemberitahuan_id
            ? asset('storage/'.$this->file_surat_pemberitahuan_id)
            : null;
    }

    public function strukturPerangkatDaerah(): BelongsTo
    {
        return $this->belongsTo(StrukturPerangkatDaerah::class, 'struktur_perangkat_daerah_id');
    }

    public function plotName(): ?string
    {
        if (! in_array($this->sprin_tipe, ['Nasional', 'Dinas'], true)) {
            return null;
        }

        return $this->strukturPerangkatDaerah?->nama_unit;
    }

    public function provinceName(): ?string
    {
        if ($this->province?->provinsi) {
            return $this->province->provinsi;
        }

        if ($this->kode_wilayah) {
            $province = DB::table('struktur_perangkat_daerah')
                ->where('kode_wilayah', $this->kode_wilayah)
                ->value('nama_unit');

            if ($province) {
                return (string) $province;
            }
        }

        return null;
    }
}
