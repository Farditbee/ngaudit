<?php

namespace App\Models;

use Database\Factories\PlottingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Plotting extends Model
{
    /** @use HasFactory<PlottingFactory> */
    use HasFactory, SoftDeletes;

    protected $table = 'struktur_perangkat_daerah';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'plot',
        'nama_unit',
        'level',
        'jenis_unit',
        'created_by',
    ];

    public function getPlotAttribute(): ?string
    {
        return $this->attributes['nama_unit'] ?? null;
    }

    public function setPlotAttribute(mixed $value): void
    {
        $this->attributes['nama_unit'] = $value;
        $this->attributes['level'] = $this->attributes['level'] ?? 1;
        $this->attributes['jenis_unit'] = $this->attributes['jenis_unit'] ?? 'Dinas';
    }

    protected static function booted(): void
    {
        static::creating(function (self $plotting): void {
            if (empty($plotting->level)) {
                $plotting->level = 1;
            }
            if (empty($plotting->jenis_unit)) {
                $plotting->jenis_unit = 'Dinas';
            }
            if (empty($plotting->created_by) && auth()->check()) {
                $plotting->created_by = auth()->id();
            }
        });
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function provinces(): BelongsToMany
    {
        return $this->belongsToMany(
            Wilayah::class,
            'struktur_perangkat_daerah',
            'parent_id',
            'id'
        );
    }
}
