<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PkaLangkahKerja extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'pka_id',
        'bidang',
        'uraian',
        'dilaksanakan_rencana_id',
        'dilaksanakan_realisasi_id',
        'waktu_rencana',
        'waktu_realisasi',
        'catatan',
        'created_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'waktu_rencana' => 'integer',
            'waktu_realisasi' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $item): void {
            if (empty($item->created_by) && auth()->check()) {
                $item->created_by = auth()->id();
            }
        });
    }

    public function pka(): BelongsTo
    {
        return $this->belongsTo(Pka::class);
    }

    public function dilaksanakanRencana(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dilaksanakan_rencana_id');
    }

    public function dilaksanakanRealisasi(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dilaksanakan_realisasi_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
