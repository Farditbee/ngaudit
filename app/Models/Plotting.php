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

    /**
     * @var list<string>
     */
    protected $fillable = [
        'plot',
        'created_by',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $plotting): void {
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
            'plotting_province',
            'plotting_id',
            'provinsi_id'
        )->withTimestamps()->orderBy('kode_wilayah');
    }
}
