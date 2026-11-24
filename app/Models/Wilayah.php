<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Wilayah extends Model
{
    use SoftDeletes;

    protected $table = 'wilayah';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'kode_wilayah',
        'provinsi',
        'kabupaten_kota',
        'kecamatan',
        'desa_kelurahan',
        'created_by',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $wilayah): void {
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
