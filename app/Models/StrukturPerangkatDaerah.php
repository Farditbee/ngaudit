<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class StrukturPerangkatDaerah extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'struktur_perangkat_daerah';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'parent_id',
        'nama_unit',
        'level',
        'jenis_unit',
        'pimpinan',
        'kode_unit',
        'kode_wilayah',
        'provinsi',
        'kabupaten_kota',
        'kecamatan',
        'desa_kelurahan',
        'created_by',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'level' => 'integer',
        'parent_id' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $model): void {
            if (empty($model->created_by) && auth()->check()) {
                $model->created_by = auth()->id();
            }
        });
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeLevel(Builder $query, int $level): Builder
    {
        return $query->where('level', $level);
    }

    public function scopeJenisUnit(Builder $query, string $jenis): Builder
    {
        return $query->where('jenis_unit', $jenis);
    }

    /**
     * Scope untuk mengambil Perangkat Daerah / OPD Tingkat Kabupaten (Dinas, Badan, dsb.)
     */
    public function scopeDinas(Builder $query): Builder
    {
        return $query->where('level', 1)
            ->whereIn('jenis_unit', ['Dinas', 'Badan', 'Inspektorat', 'Sekretariat Daerah', 'Sekretariat DPRD', 'Staf Ahli']);
    }

    /**
     * Scope untuk mengambil unit tingkat Kecamatan di Lampung Tengah.
     */
    public function scopeKecamatan(Builder $query): Builder
    {
        return $query->where('level', 1)
            ->where('jenis_unit', 'Kecamatan');
    }

    public function getPlotAttribute(): ?string
    {
        return $this->nama_unit;
    }

    /**
     * Ambil path hierarki lengkap dari unit ini sampai ke level atas.
     */
    public function getHierarchyPath(): string
    {
        $parts = collect([$this->nama_unit]);
        $current = $this;

        while ($current->parent) {
            $current = $current->parent;
            $parts->prepend($current->nama_unit);
        }

        return $parts->implode(' > ');
    }
}
