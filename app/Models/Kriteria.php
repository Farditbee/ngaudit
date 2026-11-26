<?php

namespace App\Models;

use Database\Factories\KriteriaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Kriteria extends Model
{
    /** @use HasFactory<KriteriaFactory> */
    use HasFactory, SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'nama',
        'is_opsnal',
        'is_logistik',
        'is_sdm',
        'is_garkeu',
        'created_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_opsnal' => 'boolean',
            'is_logistik' => 'boolean',
            'is_sdm' => 'boolean',
            'is_garkeu' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $kriteria): void {
            if (empty($kriteria->created_by) && auth()->check()) {
                $kriteria->created_by = auth()->id();
            }
        });
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
