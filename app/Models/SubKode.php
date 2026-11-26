<?php

namespace App\Models;

use Database\Factories\SubKodeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubKode extends Model
{
    /** @use HasFactory<SubKodeFactory> */
    use HasFactory, SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'kode_id',
        'kode',
        'nama',
        'created_by',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $subKode): void {
            if (empty($subKode->created_by) && auth()->check()) {
                $subKode->created_by = auth()->id();
            }
        });
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Kode::class, 'kode_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
