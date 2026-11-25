<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SprinReview extends Model
{
    use HasFactory, SoftDeletes;

    public const STATUS_DISETUJUI = 'Disetujui';

    public const STATUS_KOREKSI = 'Koreksi';

    /**
     * @var list<string>
     */
    public const STATUSES = [
        self::STATUS_DISETUJUI,
        self::STATUS_KOREKSI,
    ];

    /**
     * @var list<string>
     */
    protected $fillable = [
        'sprin_id',
        'user_id',
        'status',
        'catatan',
    ];

    public function sprin(): BelongsTo
    {
        return $this->belongsTo(Sprin::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
