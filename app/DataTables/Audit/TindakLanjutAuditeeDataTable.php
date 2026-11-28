<?php

namespace App\DataTables\Audit;

use App\Models\Kka;
use App\Models\Pka;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class TindakLanjutAuditeeDataTable extends KkaDataTable
{
    /**
     * @return list<array<string, mixed>>
     */
    public function columns(): array
    {
        return [
            ['data' => 'DT_RowIndex', 'name' => 'DT_RowIndex', 'label' => '#', 'sortable' => false, 'searchable' => false, 'align' => 'center', 'width' => 'w-12'],
            ['data' => 'aksi', 'name' => 'aksi', 'label' => 'AKSI', 'sortable' => false, 'searchable' => false, 'width' => 'w-48'],
            ['data' => 'auditee_name', 'name' => 'auditee_name', 'label' => 'AUDITEE', 'sortable' => true, 'searchable' => true],
            ['data' => 'nomor_kka', 'name' => 'kkas.nomor_kka', 'label' => 'KKA / AUDITOR', 'sortable' => true, 'searchable' => true],
            ['data' => 'bidang', 'name' => 'pkas.bidang', 'label' => 'BIDANG & TAHAP', 'sortable' => true, 'searchable' => true, 'align' => 'center', 'width' => 'w-44'],
            ['data' => 'tanggal_kka', 'name' => 'kkas.tanggal_kka', 'label' => 'TANGGAL', 'sortable' => true, 'searchable' => false, 'width' => 'w-40'],
        ];
    }

    /**
     * @param  iterable<int, mixed>  $pkas
     * @param  iterable<int, mixed>  $templates
     * @param  iterable<int, mixed>  $plottings
     * @param  iterable<int, mixed>  $provinces
     * @return array<string, mixed>
     */
    public function config(iterable $pkas = [], iterable $templates = [], iterable $plottings = [], iterable $provinces = []): array
    {
        return [
            'indexUrl' => route('audit.tindak-lanjut.index'),
            'storeUrl' => route('audit.kka.store'),
            'baseUrl' => url('/audit/tindak-lanjut'),
            'bidangs' => Pka::BIDANGS,
            'jenisAudits' => Pka::JENIS_AUDITS,
            'statuses' => Kka::STATUSES,
            'columns' => $this->columns(),
            'defaultSortCol' => null,
            'defaultSortDir' => 'desc',
        ];
    }

    public function toJson(Request $request): JsonResponse
    {
        $authUser = $request->user();
        $filterBidang = (string) $request->query('bidang', '');
        $filterJenisAudit = (string) $request->query('jenis_audit', '');
        $filterStatus = (string) $request->query('status', '');

        $auditeeNameSql = "COALESCE(spd.nama_unit, '')";

        $query = Kka::query()
            ->visibleForTindakLanjut($authUser)
            ->join('pkas', 'pkas.id', '=', 'kkas.pka_id')
            ->whereNull('pkas.deleted_at')
            ->join('sprins', 'sprins.id', '=', 'pkas.sprin_id')
            ->leftJoin('users as auditor', 'auditor.id', '=', 'kkas.dibuat_oleh_id')
            ->leftJoin('users as pembuat', 'pembuat.id', '=', 'kkas.created_by')
            ->leftJoin('struktur_perangkat_daerah as spd', function ($join): void {
                $join->on('spd.id', '=', 'kkas.struktur_perangkat_daerah_id')
                    ->orOn('spd.kode_wilayah', '=', 'kkas.auditee_kode_wilayah')
                    ->whereNull('spd.deleted_at');
            })
            ->leftJoin('struktur_perangkat_daerah as spd_parent', 'spd_parent.id', '=', 'spd.parent_id')
            ->select([
                'kkas.id',
                'kkas.nomor_kka',
                'kkas.pka_id',
                'kkas.dibuat_oleh_id',
                'kkas.tanggal_dibuat',
                'kkas.tanggal_kka',
                'kkas.auditee_level',
                'kkas.struktur_perangkat_daerah_id',
                'kkas.auditee_kode_wilayah',
                'kkas.status',
                'kkas.created_by',
                'kkas.created_at',
                'pkas.nomor_pka',
                'pkas.bidang',
                'pkas.jenis_audit',
                'sprins.no_sprin',
                'sprins.tahap',
                DB::raw('COALESCE(auditor.name, pembuat.name) as auditor_name'),
                'spd.nama_unit as auditee_plot_name',
                'spd.nama_unit as spd_nama_unit',
                'spd.level as spd_level',
                'spd.jenis_unit as spd_jenis_unit',
                'spd_parent.nama_unit as spd_parent_name',
                DB::raw("COALESCE(spd.provinsi, '') as aw_provinsi"),
                DB::raw("COALESCE(spd.kabupaten_kota, '') as aw_kabupaten_kota"),
                DB::raw("COALESCE(spd.kecamatan, '') as aw_kecamatan"),
                DB::raw("COALESCE(spd.desa_kelurahan, '') as aw_desa_kelurahan"),
            ])
            ->withCount([
                'tabulasis as temuan_count' => fn (Builder $q) => $q->where('jenis_tabulasi', 'Temuan'),
                'tabulasis as sudah_ditinjut_count' => fn (Builder $q) => $q->where('jenis_tabulasi', 'Temuan')
                    ->whereNotNull('tindak_lanjut')
                    ->where('tindak_lanjut', '!=', ''),
                'tabulasis as belum_sesuai_count' => fn (Builder $q) => $q->where('jenis_tabulasi', 'Temuan')
                    ->where(function (Builder $b): void {
                        $b->where('status_tinjut', 'Dalam Proses')
                            ->orWhere('kesimpulan_tinjut', 'like', '%Belum Sesuai%');
                    }),
                'tabulasis as selesai_count' => fn (Builder $q) => $q->where('jenis_tabulasi', 'Temuan')
                    ->where('status_tinjut', 'Selesai'),
                'tabulasis as draft_count' => fn (Builder $q) => $q->where('jenis_tabulasi', 'Temuan')
                    ->where('status', 'Draft'),
            ]);

        if ($filterBidang !== '') {
            $query->where('pkas.bidang', $filterBidang);
        }

        if ($filterJenisAudit !== '') {
            $query->where('pkas.jenis_audit', $filterJenisAudit);
        }

        if ($filterStatus !== '') {
            $query->where('kkas.status', $filterStatus);
        }

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->order(function (Builder $builder) use ($request, $auditeeNameSql): void {
                $order = $request->input('order');
                $colIndex = data_get($order, '0.column');
                $dir = strtolower((string) data_get($order, '0.dir', 'desc')) === 'asc' ? 'asc' : 'desc';

                $colMap = [
                    2 => DB::raw($auditeeNameSql),
                    3 => 'kkas.nomor_kka',
                    4 => 'pkas.bidang',
                    5 => 'kkas.tanggal_kka',
                ];

                if ($colIndex !== null && $colIndex !== '' && isset($colMap[(int) $colIndex])) {
                    $builder->orderBy($colMap[(int) $colIndex], $dir);
                }

                $builder->orderByDesc('kkas.created_at')->orderByDesc('kkas.id');
            })
            ->addColumn('tanggal_diinput', fn (Kka $k): string => ($k->tanggal_kka ?? $k->created_at)?->format('d-m-Y') ?? '-')
            ->addColumn('auditee_name', fn (Kka $k): string => $this->auditeeName($k))
            ->addColumn('auditee_parent', fn (Kka $k): ?string => $this->auditeeParent($k))
            ->addColumn('auditee_sub_unit', fn (Kka $k): ?string => $this->auditeeSubUnit($k))
            ->addColumn('auditee_detail', fn (Kka $k): string => $this->auditeeDetail($k))
            ->addColumn('auditee_hierarchy', fn (Kka $k): array => $this->auditeeHierarchy($k))
            ->addColumn('tanggapan_url', fn (Kka $k): string => route('audit.tindak-lanjut.show', [$k, 'tabulasi']))
            ->filterColumn('auditee_name', function (Builder $query, string $keyword): void {
                $query->where(function (Builder $q) use ($keyword): void {
                    $q->where('spd.nama_unit', 'like', "%{$keyword}%")
                        ->orWhere('spd_parent.nama_unit', 'like', "%{$keyword}%")
                        ->orWhere('spd.provinsi', 'like', "%{$keyword}%")
                        ->orWhere('spd.kabupaten_kota', 'like', "%{$keyword}%")
                        ->orWhere('spd.kecamatan', 'like', "%{$keyword}%")
                        ->orWhere('spd.desa_kelurahan', 'like', "%{$keyword}%");
                });
            })
            ->filterColumn('kkas.nomor_kka', function (Builder $query, string $keyword): void {
                $query->where(function (Builder $q) use ($keyword): void {
                    $q->where('kkas.nomor_kka', 'like', "%{$keyword}%")
                        ->orWhere('auditor.name', 'like', "%{$keyword}%")
                        ->orWhere('pembuat.name', 'like', "%{$keyword}%");
                });
            })
            ->filterColumn('pkas.bidang', function (Builder $query, string $keyword): void {
                $query->where(function (Builder $q) use ($keyword): void {
                    $q->where('pkas.bidang', 'like', "%{$keyword}%")
                        ->orWhere('sprins.tahap', 'like', "%{$keyword}%");
                });
            })
            ->toJson();
    }
}
