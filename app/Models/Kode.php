<?php

namespace App\Models;

use Database\Factories\KodeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Kode extends Model
{
    /** @use HasFactory<KodeFactory> */
    use HasFactory, SoftDeletes;

    public const JENIS_TEMUAN = 'temuan';

    public const JENIS_SEBAB = 'sebab';

    public const JENIS_AKIBAT = 'akibat';

    public const JENIS_REKOMENDASI = 'rekomendasi';

    /**
     * @var list<string>
     */
    public const JENIS = [
        self::JENIS_TEMUAN,
        self::JENIS_SEBAB,
        self::JENIS_AKIBAT,
        self::JENIS_REKOMENDASI,
    ];

    /**
     * @var list<string>
     */
    protected $fillable = [
        'jenis',
        'kode_temuan_id',
        'kode',
        'nama',
        'created_by',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $kode): void {
            if (empty($kode->created_by) && auth()->check()) {
                $kode->created_by = auth()->id();
            }
        });
    }

    public function kodeTemuan(): BelongsTo
    {
        return $this->belongsTo(self::class, 'kode_temuan_id');
    }

    public function dependentKodes(): HasMany
    {
        return $this->hasMany(self::class, 'kode_temuan_id')->orderBy('kode');
    }

    public function subKodes(): HasMany
    {
        return $this->hasMany(SubKode::class)->orderBy('kode');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function label(): string
    {
        return $this->kode.' - '.$this->nama;
    }

    public function fullLabel(): string
    {
        if ($this->kodeTemuan) {
            return '['.$this->kodeTemuan->kode.'] '.$this->kode.' - '.$this->nama;
        }

        return $this->label();
    }
}
