<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KkaTabulasiLampiran extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'kka_tabulasi_id',
        'user_id',
        'jenis_unggahan',
        'file_path',
        'file_name',
        'link_url',
        'keterangan',
    ];

    public function tabulasi(): BelongsTo
    {
        return $this->belongsTo(KkaTabulasi::class, 'kka_tabulasi_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
