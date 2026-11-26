<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class KkaReview extends Model
{
    use HasFactory, SoftDeletes;

    public const TYPE_KATIM = 'katim';

    public const TYPE_DALNIS = 'dalnis';

    public const TYPE_DALTU = 'daltu';

    /**
     * @var list<string>
     */
    public const TYPES = [
        self::TYPE_KATIM,
        self::TYPE_DALNIS,
        self::TYPE_DALTU,
    ];

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
        'kka_id',
        'user_id',
        'role_type',
        'status',
        'catatan',
    ];

    public function kka(): BelongsTo
    {
        return $this->belongsTo(Kka::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
