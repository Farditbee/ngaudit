<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class TemplatePka extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * @var list<string>
     */
    public const LINGKUPS = [
        'Nasional',
        'Wilayah',
    ];

    /**
     * @var list<string>
     */
    protected $fillable = [
        'nama_template',
        'lingkup',
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

        if ($user->role === 'Admin Nasional' && $user->plotting_id) {
            $query->where('template_pkas.lingkup', 'Nasional')
                ->where('template_pkas.plotting_id', $user->plotting_id);
        } elseif ($user->role === 'Admin Wilayah' && $user->kode_wilayah) {
            $provinceCode = substr(str_replace('.', '', (string) $user->kode_wilayah), 0, 2);
            $query->where('template_pkas.lingkup', 'Wilayah')
                ->where('template_pkas.kode_wilayah', $provinceCode);
        }
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function plotting(): BelongsTo
    {
        return $this->belongsTo(Plotting::class);
    }

    public function province(): BelongsTo
    {
        return $this->belongsTo(Wilayah::class, 'kode_wilayah', 'kode_wilayah')
            ->whereNull('kabupaten_kota');
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
