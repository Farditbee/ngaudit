<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TemplatePka extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * @var list<string>
     */
    public const LINGKUPS = [
        'Dinas',
        'Kecamatan',
    ];

    protected $appends = [
        'plotting_id',
    ];

    /**
     * @var list<string>
     */
    protected $fillable = [
        'nama_template',
        'lingkup',
        'struktur_perangkat_daerah_id',
        'plotting_id',
        'kode_wilayah',
        'pendahuluan',
        'tujuan',
        'ruang_lingkup',
        'sasaran',
        'waktu_pelaksanaan',
        'periode',
        'penutup',
        'created_by',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $template): void {
            if (empty($template->created_by) && auth()->check()) {
                $template->created_by = auth()->id();
            }
        });
    }

    /**
     * Batasi Template PKA sesuai hak akses user yang sedang login.
     *
     * @param  Builder<TemplatePka>  $query
     */
    public function scopeVisibleTo(Builder $query, ?User $user): void
    {
        if (! $user || $user->role === 'Super Admin') {
            return;
        }

        $spdId = $user->struktur_perangkat_daerah_id ?? $user->plotting_id;

        if (in_array($user->role, ['Admin Nasional', 'Admin Dinas'], true) || in_array($user->level, ['Nasional', 'Dinas'], true)) {
            $query->whereIn('template_pkas.lingkup', ['Nasional', 'Dinas'])
                ->where(function ($q) use ($spdId): void {
                    $q->whereNull('template_pkas.struktur_perangkat_daerah_id');
                    if ($spdId) {
                        $q->orWhere('template_pkas.struktur_perangkat_daerah_id', $spdId);
                    }
                });
        } elseif (in_array($user->role, ['Admin Wilayah', 'Admin Kecamatan'], true) || in_array($user->level, ['Wilayah', 'Kecamatan'], true)) {
            $query->whereIn('template_pkas.lingkup', ['Wilayah', 'Kecamatan'])
                ->where(function ($q) use ($spdId): void {
                    $q->whereNull('template_pkas.struktur_perangkat_daerah_id');
                    if ($spdId) {
                        $q->orWhere('template_pkas.struktur_perangkat_daerah_id', $spdId);
                    }
                });
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

    public function strukturPerangkatDaerah(): BelongsTo
    {
        return $this->belongsTo(StrukturPerangkatDaerah::class, 'struktur_perangkat_daerah_id');
    }

    public function plotName(): ?string
    {
        return $this->strukturPerangkatDaerah?->nama_unit;
    }

    public function provinceName(): ?string
    {
        return $this->strukturPerangkatDaerah?->nama_unit;
    }
}
