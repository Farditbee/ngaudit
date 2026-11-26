<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TemplateLangkahKerjaDetail extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'template_langkah_kerja_id',
        'uraian',
        'created_by',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $detail): void {
            if (empty($detail->created_by) && auth()->check()) {
                $detail->created_by = auth()->id();
            }
        });

        static::created(function (self $detail): void {
            $detail->templateLangkahKerja?->refreshJumlahLangkahKerja();
        });

        static::deleted(function (self $detail): void {
            $detail->templateLangkahKerja?->refreshJumlahLangkahKerja();
        });

        static::restored(function (self $detail): void {
            $detail->templateLangkahKerja?->refreshJumlahLangkahKerja();
        });
    }

    public function templateLangkahKerja(): BelongsTo
    {
        return $this->belongsTo(TemplateLangkahKerja::class, 'template_langkah_kerja_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
