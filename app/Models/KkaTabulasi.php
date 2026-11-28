<?php

namespace App\Models;

use Database\Factories\KkaTabulasiFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class KkaTabulasi extends Model
{
    /** @use HasFactory<KkaTabulasiFactory> */
    use HasFactory, SoftDeletes;

    /**
     * @var list<string>
     */
    public const JENIS_TABULASIS = [
        'Temuan',
        'Konsultasi',
        'Positif',
    ];

    /**
     * @var list<string>
     */
    public const TIPE_TEMUANS = [
        'Baru',
        'Berulang',
        'Menonjol',
    ];

    /**
     * @var list<string>
     */
    public const STATUSES = [
        'Draft',
        'Final',
    ];

    /**
     * @var list<string>
     */
    public const STATUS_TINJUTS = [
        'Belum Ditinjut',
        'Sudah Ditinjut',
        'Koreksi',
        'Selesai',
    ];

    /**
     * @var list<string>
     */
    public const KESIMPULAN_TINJUTS = [
        'Sesuai Rekomendasi',
        'Belum Sesuai / Dalam Proses',
        'Belum Ditindaklanjuti',
        'Tidak Dapat Ditindaklanjuti',
    ];

    /**
     * Kode temuan yang memunculkan isian Nilai Temuan (IDR).
     *
     * @var list<string>
     */
    public const KODE_TEMUAN_WITH_NILAI = [
        '01',
        '02',
        '03',
        '05',
    ];

    /**
     * @var list<string>
     */
    protected $fillable = [
        'kka_id',
        'jenis_tabulasi',
        'bidang',
        'nomor',
        'tipe_temuan',
        'kode_temuan_id',
        'sub_kode_temuan_id',
        'nilai_temuan',
        'uraian_temuan',
        'kriteria_id',
        'uraian_kriteria',
        'kode_sebab_id',
        'sub_kode_sebab_id',
        'uraian_sebab',
        'kode_akibat_id',
        'sub_kode_akibat_id',
        'uraian_akibat',
        'kode_rekomendasi_id',
        'sub_kode_rekomendasi_id',
        'uraian_rekomendasi',
        'auditor_id',
        'status',
        'status_tinjut',
        'kode_tinjut_id',
        'sub_kode_tinjut_id',
        'tindak_lanjut',
        'nilai_pengembalian',
        'kesimpulan_tinjut',
        'alasan_tinjut',
        'lampiran_tinjut_path',
        'lampiran_tinjut_name',
        'created_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'nilai_temuan' => 'decimal:2',
            'nilai_pengembalian' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $tabulasi): void {
            if (empty($tabulasi->created_by) && auth()->check()) {
                $tabulasi->created_by = auth()->id();
            }
        });
    }

    public function kka(): BelongsTo
    {
        return $this->belongsTo(Kka::class);
    }

    public function kodeTemuan(): BelongsTo
    {
        return $this->belongsTo(Kode::class, 'kode_temuan_id');
    }

    public function subKodeTemuan(): BelongsTo
    {
        return $this->belongsTo(SubKode::class, 'sub_kode_temuan_id');
    }

    public function kriteria(): BelongsTo
    {
        return $this->belongsTo(Kriteria::class, 'kriteria_id');
    }

    public function kodeSebab(): BelongsTo
    {
        return $this->belongsTo(Kode::class, 'kode_sebab_id');
    }

    public function subKodeSebab(): BelongsTo
    {
        return $this->belongsTo(SubKode::class, 'sub_kode_sebab_id');
    }

    public function kodeAkibat(): BelongsTo
    {
        return $this->belongsTo(Kode::class, 'kode_akibat_id');
    }

    public function subKodeAkibat(): BelongsTo
    {
        return $this->belongsTo(SubKode::class, 'sub_kode_akibat_id');
    }

    public function kodeRekomendasi(): BelongsTo
    {
        return $this->belongsTo(Kode::class, 'kode_rekomendasi_id');
    }

    public function subKodeRekomendasi(): BelongsTo
    {
        return $this->belongsTo(SubKode::class, 'sub_kode_rekomendasi_id');
    }

    public function kodeTinjut(): BelongsTo
    {
        return $this->belongsTo(Kode::class, 'kode_tinjut_id');
    }

    public function subKodeTinjut(): BelongsTo
    {
        return $this->belongsTo(SubKode::class, 'sub_kode_tinjut_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(KkaTabulasiMessage::class, 'kka_tabulasi_id')->orderBy('id');
    }

    public function lampirans(): HasMany
    {
        return $this->hasMany(KkaTabulasiLampiran::class, 'kka_tabulasi_id')->latest('id');
    }

    public function auditor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'auditor_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
