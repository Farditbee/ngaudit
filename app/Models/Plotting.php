<?php

namespace App\Models;

use Database\Factories\PlottingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Laravolt\Indonesia\Models\Province;

class Plotting extends Model
{
    /** @use HasFactory<PlottingFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'plot',
    ];

    public function provinces(): BelongsToMany
    {
        return $this->belongsToMany(
            Province::class,
            'plotting_province',
            'plotting_id',
            'provinsi_id'
        )->withTimestamps()->orderBy('code');
    }
}
