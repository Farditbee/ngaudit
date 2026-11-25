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

        if ($user->role === 'Admin Nasional' && $user->plotting_id) {
            $query->where('sprins.sprin_tipe', 'Nasional')
                ->where('sprins.plotting_id', $user->plotting_id);
        } elseif ($user->role === 'Admin Wilayah' && $user->kode_wilayah) {
            $provinceCode = substr(str_replace('.', '', (string) $user->kode_wilayah), 0, 2);
            $query->where('sprins.sprin_tipe', 'Wilayah')
                ->where('sprins.kode_wilayah', $provinceCode);
        } elseif ($user->role === 'Koordinator dan Pengendali') {
            if ($user->plotting_id) {
                $query->where('sprins.plotting_id', $user->plotting_id);
            } elseif ($user->kode_wilayah) {
                $provinceCode = substr(str_replace('.', '', (string) $user->kode_wilayah), 0, 2);
                $query->where('sprins.sprin_tipe', 'Wilayah')
                    ->where('sprins.kode_wilayah', $provinceCode);
            } else {
                $query->where('sprins.daltu', $user->id);
            }
        }
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
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
        return $this->belongsTo(Plotting::class);
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

    public function plotName(): ?string
    {
        return $this->plotting?->plot;
    }

    public function provinceName(): ?string
    {
        if ($this->province?->provinsi) {
            return $this->province->provinsi;
        }

        if ($this->kode_wilayah) {
            $province = DB::table('wilayah')
                ->where('kode_wilayah', $this->kode_wilayah)
                ->whereNull('kabupaten_kota')
                ->value('provinsi');

            if ($province) {
                return (string) $province;
            }
        }

        return null;
    }
}
