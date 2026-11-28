<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Wilayah extends Model
{
    use SoftDeletes;

    protected $table = 'struktur_perangkat_daerah';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'kode_wilayah',
        'nama_unit',
        'level',
        'jenis_unit',
        'provinsi',
        'kabupaten_kota',
        'kecamatan',
        'desa_kelurahan',
        'created_by',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $wilayah): void {
            if (empty($wilayah->nama_unit)) {
                $wilayah->nama_unit = $wilayah->desa_kelurahan
                    ?? $wilayah->kecamatan
                    ?? $wilayah->kabupaten_kota
                    ?? $wilayah->provinsi
                    ?? 'Unit Wilayah '.$wilayah->kode_wilayah;
            }
            if (empty($wilayah->level)) {
                $wilayah->level = 1;
            }
            if (empty($wilayah->jenis_unit)) {
                $wilayah->jenis_unit = 'Wilayah';
            }
            if (empty($wilayah->created_by) && auth()->check()) {
                $wilayah->created_by = auth()->id();
            }
        });
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
