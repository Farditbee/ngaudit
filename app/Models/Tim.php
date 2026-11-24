<?php

namespace App\Models;

use Database\Factories\TimFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tim extends Model
{
    /** @use HasFactory<TimFactory> */
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
        'nama_tim',
        'lingkup',
        'plotting_id',
        'province_code',
        'daltu_id',
        'dalnis_id',
        'ketua_tim_id',
        'ketua_tim_2_id',
        'created_by',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $tim): void {
            if (empty($tim->created_by) && auth()->check()) {
                $tim->created_by = auth()->id();
            }
        });
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
        return $this->belongsTo(Wilayah::class, 'province_code', 'kode_wilayah');
    }

    public function daltu(): BelongsTo
    {
        return $this->belongsTo(User::class, 'daltu_id');
    }

    public function dalnis(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dalnis_id');
    }

    public function ketuaTim(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ketua_tim_id');
    }

    public function ketuaTim2(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ketua_tim_2_id');
    }

    public function auditors(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'tim_auditor',
            'tim_id',
            'user_id'
        )->withTimestamps()->orderBy('users.name');
    }
}
