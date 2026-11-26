<?php

namespace App\DataTables\Audit;

use App\Models\Pka;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class PkaDataTable
{
    /**
     * @return list<array<string, mixed>>
     */
    public function columns(): array
    {
        return [
            ['data' => 'DT_RowIndex', 'name' => 'DT_RowIndex', 'label' => '#', 'sortable' => false, 'searchable' => false, 'align' => 'center', 'width' => 'w-12'],
            ['data' => 'no_sprin', 'name' => 'sprins.no_sprin', 'label' => 'NOMOR SPRIN & PKA', 'sortable' => true, 'searchable' => true, 'width' => 'min-w-[13rem]'],
            ['data' => 'sprin_tipe', 'name' => 'sprins.sprin_tipe', 'label' => 'TIPE & TAHAP', 'sortable' => true, 'searchable' => true, 'align' => 'center', 'width' => 'w-28'],
            ['data' => 'bidang', 'name' => 'pkas.bidang', 'label' => 'BIDANG', 'sortable' => true, 'searchable' => true, 'align' => 'center', 'width' => 'w-40'],
            ['data' => 'jenis_audit', 'name' => 'pkas.jenis_audit', 'label' => 'JENIS', 'sortable' => true, 'searchable' => true, 'align' => 'center', 'width' => 'w-28'],
            ['data' => 'status', 'name' => 'pkas.status', 'label' => 'STATUS', 'sortable' => true, 'searchable' => true, 'align' => 'center', 'width' => 'w-32'],
            ['data' => 'kka', 'name' => 'kka', 'label' => 'KKA', 'sortable' => false, 'searchable' => false, 'align' => 'center', 'width' => 'w-28'],
            ['data' => 'reviews', 'name' => 'reviews', 'label' => 'REVIU DALNIS & DALTU', 'sortable' => false, 'searchable' => false, 'align' => 'center', 'width' => 'w-36'],
            ['data' => 'tanggal_formatted', 'name' => 'pkas.tanggal', 'label' => 'TANGGAL', 'sortable' => true, 'searchable' => false, 'align' => 'center', 'width' => 'w-28'],
            ['data' => 'actions', 'name' => 'actions', 'label' => 'AKSI', 'sortable' => false, 'searchable' => false, 'align' => 'center', 'width' => 'w-36'],
        ];
    }

    /**
     * @param  iterable<int, mixed>  $sprins
     * @param  iterable<int, mixed>  $templates
     * @return array<string, mixed>
     */
    public function config(iterable $sprins, iterable $templates): array
    {
        return [
            'indexUrl' => route('audit.pka.index'),
            'storeUrl' => route('audit.pka.store'),
            'baseUrl' => url('/audit/pka'),
            'bidangs' => Pka::BIDANGS,
            'jenisAudits' => Pka::JENIS_AUDITS,
            'statuses' => Pka::STATUSES,
            'sprins' => $sprins,
            'templates' => $templates,
            'columns' => $this->columns(),
        ];
    }

    public function toJson(Request $request): JsonResponse
    {
        $authUser = $request->user();
        $filterBidang = (string) $request->query('bidang', '');
        $filterJenisAudit = (string) $request->query('jenis_audit', '');
        $filterStatus = (string) $request->query('status', '');

        $query = Pka::query()
            ->visibleTo($authUser)
            ->join('sprins', 'sprins.id', '=', 'pkas.sprin_id')
            ->leftJoin('template_pkas', 'template_pkas.id', '=', 'pkas.template_pka_id')
            ->select([
                'pkas.*',
                'sprins.no_sprin',
                'sprins.sprin_tipe',
                'sprins.tahap',
                'template_pkas.nama_template',
            ]);

        if ($filterBidang !== '') {
            $query->where('pkas.bidang', $filterBidang);
        }

        if ($filterJenisAudit !== '') {
            $query->where('pkas.jenis_audit', $filterJenisAudit);
        }

        if ($filterStatus !== '') {
            $query->where('pkas.status', $filterStatus);
        }

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('tanggal_formatted', fn (Pka $p): string => $p->tanggal?->format('d/m/Y') ?? '-')
            ->addColumn('formatted_anggaran', fn (Pka $p): string => $p->formattedAnggaran())
            ->addColumn('lampiran_url', fn (Pka $p): ?string => $p->lampiranUrl())
            ->addColumn('kka_count', fn (Pka $p): int => 0)
            ->rawColumns(['pendahuluan', 'tujuan', 'ruang_lingkup', 'sasaran', 'waktu_pelaksanaan', 'periode', 'penutup'])
            ->filterColumn('no_sprin', function (Builder $query, string $keyword): void {
                $query->where(function (Builder $q) use ($keyword): void {
                    $q->where('sprins.no_sprin', 'like', "%{$keyword}%")
                        ->orWhere('pkas.nomor_pka', 'like', "%{$keyword}%");
                });
            })
            ->filterColumn('sprin_tipe', function (Builder $query, string $keyword): void {
                $query->where(function (Builder $q) use ($keyword): void {
                    $q->where('sprins.sprin_tipe', 'like', "%{$keyword}%")
                        ->orWhere('sprins.tahap', 'like', "%{$keyword}%");
                });
            })
            ->toJson();
    }
}
