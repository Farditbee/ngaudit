<?php

namespace App\DataTables\Audit;

use App\Models\Kka;
use App\Models\KkaTabulasi;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class KkaTabulasiDataTable
{
    /**
     * @return list<array<string, mixed>>
     */
    public function columns(string $jenisTabulasi = 'Temuan'): array
    {
        if ($jenisTabulasi === 'Konsultasi') {
            return [
                ['data' => 'DT_RowIndex', 'name' => 'DT_RowIndex', 'label' => '#', 'sortable' => false, 'searchable' => false, 'align' => 'center', 'width' => 'w-8'],
                ['data' => 'nomor', 'name' => 'nomor', 'label' => 'No', 'sortable' => true, 'searchable' => true, 'align' => 'center', 'width' => 'w-12'],
                ['data' => 'uraian_temuan', 'name' => 'uraian_temuan', 'label' => 'Uraian Permasalahan', 'sortable' => false, 'searchable' => true],
                ['data' => 'uraian_kriteria', 'name' => 'uraian_kriteria', 'label' => 'Kriteria', 'sortable' => false, 'searchable' => true],
                ['data' => 'uraian_rekomendasi', 'name' => 'uraian_rekomendasi', 'label' => 'Rekomendasi', 'sortable' => false, 'searchable' => true],
                ['data' => 'status', 'name' => 'status', 'label' => 'Status', 'sortable' => true, 'searchable' => true, 'align' => 'center', 'width' => 'w-24'],
                ['data' => 'actions', 'name' => 'actions', 'label' => 'Aksi', 'sortable' => false, 'searchable' => false, 'align' => 'center', 'width' => 'w-16'],
            ];
        }

        if ($jenisTabulasi === 'Positif') {
            return [
                ['data' => 'DT_RowIndex', 'name' => 'DT_RowIndex', 'label' => '#', 'sortable' => false, 'searchable' => false, 'align' => 'center', 'width' => 'w-8'],
                ['data' => 'nomor', 'name' => 'nomor', 'label' => 'No', 'sortable' => true, 'searchable' => true, 'align' => 'center', 'width' => 'w-12'],
                ['data' => 'uraian_temuan', 'name' => 'uraian_temuan', 'label' => 'Deskripsi', 'sortable' => false, 'searchable' => true],
                ['data' => 'status', 'name' => 'status', 'label' => 'Status', 'sortable' => true, 'searchable' => true, 'align' => 'center', 'width' => 'w-24'],
                ['data' => 'actions', 'name' => 'actions', 'label' => 'Aksi', 'sortable' => false, 'searchable' => false, 'align' => 'center', 'width' => 'w-16'],
            ];
        }

        return [
            ['data' => 'DT_RowIndex', 'name' => 'DT_RowIndex', 'label' => '#', 'sortable' => false, 'searchable' => false, 'align' => 'center', 'width' => 'w-8'],
            ['data' => 'nomor', 'name' => 'nomor', 'label' => 'No', 'sortable' => true, 'searchable' => true, 'align' => 'center', 'width' => 'w-10'],
            ['data' => 'uraian_temuan', 'name' => 'uraian_temuan', 'label' => 'Temuan', 'sortable' => false, 'searchable' => true],
            ['data' => 'kode_temuan_label', 'name' => 'kode_temuan_label', 'label' => 'Kode Temuan', 'sortable' => false, 'searchable' => true, 'width' => 'w-20'],
            ['data' => 'nilai_temuan_formatted', 'name' => 'nilai_temuan', 'label' => 'Nilai Temuan', 'sortable' => true, 'searchable' => false, 'width' => 'w-32'],
            ['data' => 'uraian_kriteria', 'name' => 'uraian_kriteria', 'label' => 'Kriteria', 'sortable' => false, 'searchable' => true],
            ['data' => 'uraian_sebab', 'name' => 'uraian_sebab', 'label' => 'Sebab', 'sortable' => false, 'searchable' => true],
            ['data' => 'uraian_akibat', 'name' => 'uraian_akibat', 'label' => 'Akibat', 'sortable' => false, 'searchable' => true],
            ['data' => 'uraian_rekomendasi', 'name' => 'uraian_rekomendasi', 'label' => 'Rekomendasi', 'sortable' => false, 'searchable' => true],
            ['data' => 'status', 'name' => 'status', 'label' => 'Status', 'sortable' => true, 'searchable' => true, 'align' => 'center', 'width' => 'w-24'],
            ['data' => 'actions', 'name' => 'actions', 'label' => 'Aksi', 'sortable' => false, 'searchable' => false, 'align' => 'center', 'width' => 'w-16'],
        ];
    }

    /**
     * @param  array<string, mixed>  $masterData
     * @return array<string, mixed>
     */
    public function config(Kka $kka, string $tabSlug, string $jenisTabulasi, array $masterData): array
    {
        return [
            'indexUrl' => route('audit.kka.tabulasi.index', [$kka, $tabSlug]),
            'storeUrl' => route('audit.kka.tabulasi.store', $kka),
            'baseUrl' => url("/audit/kka/{$kka->id}/tabulasi"),
            'kkaId' => $kka->id,
            'tabSlug' => $tabSlug,
            'currentJenisTabulasi' => $jenisTabulasi,
            'tabUrls' => [
                'Temuan' => route('audit.kka.tabulasi.index', [$kka, 'tabulasi']),
                'Konsultasi' => route('audit.kka.tabulasi.index', [$kka, 'konsultasi']),
                'Positif' => route('audit.kka.tabulasi.index', [$kka, 'positif']),
            ],
            'bidang' => $kka->pka?->bidang ?? '-',
            'jenisTabulasis' => KkaTabulasi::JENIS_TABULASIS,
            'tipeTemuans' => KkaTabulasi::TIPE_TEMUANS,
            'statuses' => KkaTabulasi::STATUSES,
            'kodeWithNilai' => KkaTabulasi::KODE_TEMUAN_WITH_NILAI,
            'columns' => $this->columns($jenisTabulasi),
            'defaultSortCol' => 1,
            'defaultSortDir' => 'asc',
            ...$masterData,
        ];
    }

    public function toJson(Request $request, Kka $kka, string $jenisTabulasi = 'Temuan'): JsonResponse
    {
        $query = KkaTabulasi::query()
            ->where('kka_id', $kka->id)
            ->where('jenis_tabulasi', $jenisTabulasi)
            ->with([
                'kodeTemuan:id,kode,nama',
                'subKodeTemuan:id,kode_id,kode,nama',
                'kodeSebab:id,kode,nama',
                'subKodeSebab:id,kode_id,kode,nama',
                'kodeAkibat:id,kode,nama',
                'subKodeAkibat:id,kode_id,kode,nama',
                'kodeRekomendasi:id,kode,nama',
                'subKodeRekomendasi:id,kode_id,kode,nama',
                'auditor:id,name,username',
            ]);

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('kode_temuan_kode', fn (KkaTabulasi $t): ?string => $t->kodeTemuan?->kode)
            ->addColumn('sub_kode_temuan_kode', fn (KkaTabulasi $t): ?string => $t->subKodeTemuan?->kode)
            ->addColumn('kode_temuan_label', function (KkaTabulasi $t): string {
                $parts = array_filter([
                    $t->kodeTemuan?->kode,
                    $t->subKodeTemuan?->kode ? 'Sub kode : '.$t->subKodeTemuan->kode : null,
                ]);

                return ! empty($parts) ? implode("\n", $parts) : '-';
            })
            ->addColumn('nilai_temuan_formatted', function (KkaTabulasi $t): string {
                if ($t->nilai_temuan === null) {
                    return '-';
                }

                return 'Rp '.number_format((float) $t->nilai_temuan, 0, ',', '.');
            })
            ->addColumn('auditor_name', fn (KkaTabulasi $t): string => $t->auditor?->name ?? '-')
            ->addColumn('name', fn (KkaTabulasi $t): string => 'Tabulasi '.$t->jenis_tabulasi.' #'.$t->nomor)
            ->filterColumn('kode_temuan_label', function (Builder $query, string $keyword): void {
                $query->where(function (Builder $q) use ($keyword): void {
                    $q->whereHas('kodeTemuan', fn (Builder $b) => $b->where('kode', 'like', "%{$keyword}%")->orWhere('nama', 'like', "%{$keyword}%"))
                        ->orWhereHas('subKodeTemuan', fn (Builder $b) => $b->where('kode', 'like', "%{$keyword}%")->orWhere('nama', 'like', "%{$keyword}%"));
                });
            })
            ->rawColumns(['uraian_temuan', 'uraian_kriteria', 'uraian_sebab', 'uraian_akibat', 'uraian_rekomendasi'])
            ->toJson();
    }
}
