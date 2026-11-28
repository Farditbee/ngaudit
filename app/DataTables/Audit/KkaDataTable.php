<?php

namespace App\DataTables\Audit;

use App\Models\Kka;
use App\Models\KkaReview;
use App\Models\Pka;
use App\Models\StrukturPerangkatDaerah;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class KkaDataTable
{
    /**
     * @return list<array<string, mixed>>
     */
    public function columns(): array
    {
        return [
            ['data' => 'DT_RowIndex', 'name' => 'DT_RowIndex', 'label' => '#', 'sortable' => false, 'searchable' => false, 'align' => 'center', 'width' => 'w-12'],
            ['data' => 'no_sprin', 'name' => 'sprins.no_sprin', 'label' => 'NOMOR SPRIN, PKA & KKA', 'sortable' => true, 'searchable' => true, 'width' => 'min-w-[14rem]'],
            ['data' => 'auditor_name', 'name' => 'auditor_name', 'label' => 'AUDITOR', 'sortable' => true, 'searchable' => true, 'width' => 'min-w-[10rem]'],
            ['data' => 'auditee_name', 'name' => 'auditee_name', 'label' => 'AUDITEE', 'sortable' => true, 'searchable' => true, 'width' => 'min-w-[12rem]'],
            ['data' => 'bidang', 'name' => 'pkas.bidang', 'label' => 'BIDANG & TAHAP', 'sortable' => true, 'searchable' => true, 'align' => 'center', 'width' => 'w-40'],
            ['data' => 'reviews', 'name' => 'reviews', 'label' => 'REVIU & VALIDASI', 'sortable' => false, 'searchable' => false, 'align' => 'center', 'width' => 'w-44'],
            ['data' => 'tabulasi', 'name' => 'tabulasi', 'label' => 'TABULASI', 'sortable' => false, 'searchable' => false, 'align' => 'center', 'width' => 'w-32'],
            ['data' => 'status', 'name' => 'kkas.status', 'label' => 'STATUS & AKSI', 'sortable' => true, 'searchable' => true, 'align' => 'center', 'width' => 'w-44'],
        ];
    }

    /**
     * @param  iterable<int, mixed>  $pkas
     * @param  iterable<int, mixed>  $templates
     * @param  iterable<int, mixed>  $plottings
     * @param  iterable<int, mixed>  $provinces
     * @return array<string, mixed>
     */
    public function config(iterable $pkas, iterable $templates, iterable $plottings, iterable $provinces): array
    {
        $authUser = auth()->user();
        $isSuperAdmin = $authUser?->role === 'Super Admin';
        $levels = ['Dinas', 'Kecamatan'];

        $dinasOptions = StrukturPerangkatDaerah::dinas()
            ->orderBy('nama_unit')
            ->get(['id', 'nama_unit'])
            ->map(fn ($u): array => ['id' => $u->id, 'code' => (string) $u->id, 'name' => $u->nama_unit])
            ->values()
            ->all();

        $kecamatanOptions = StrukturPerangkatDaerah::kecamatan()
            ->orderBy('nama_unit')
            ->get(['id', 'nama_unit'])
            ->map(fn ($u): array => ['id' => $u->id, 'code' => (string) $u->id, 'name' => $u->nama_unit])
            ->values()
            ->all();

        return [
            'indexUrl' => route('audit.kka.index'),
            'storeUrl' => route('audit.kka.store'),
            'baseUrl' => url('/audit/kka'),
            'bidangs' => Pka::BIDANGS,
            'jenisAudits' => Pka::JENIS_AUDITS,
            'statuses' => Kka::STATUSES,
            'levels' => $levels,
            'dinasOptions' => $dinasOptions,
            'kecamatanOptions' => $kecamatanOptions,
            'authRole' => $authUser?->role,
            'authSpdId' => $authUser?->struktur_perangkat_daerah_id ? (string) $authUser->struktur_perangkat_daerah_id : null,
            'isSpdReadonly' => false,
            'pkas' => $pkas,
            'templates' => $templates,
            'plottings' => $plottings,
            'provinces' => $provinces,
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
            ->visibleTo($authUser)
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
            ->with([
                'pka:id,sprin_id,nomor_pka',
                'pka.sprin:id,sprin_tipe,struktur_perangkat_daerah_id,kode_wilayah,dalnis,daltu,katim,katim_2',
                'latestKatimReview:id,kka_id,role_type,status,created_at',
                'latestDalnisReview:id,kka_id,role_type,status,created_at',
                'latestDaltuReview:id,kka_id,role_type,status,created_at',
            ])
            ->select([
                'kkas.id',
                'kkas.nomor_kka',
                'kkas.pka_id',
                'kkas.dibuat_oleh_id',
                'kkas.direviu_oleh_id',
                'kkas.tanggal_dibuat',
                'kkas.tanggal_direviu',
                'kkas.tanggal_kka',
                'kkas.tempat',
                'kkas.auditee_level',
                'kkas.struktur_perangkat_daerah_id',
                'kkas.auditee_kode_wilayah',
                'kkas.template_kka_id',
                'kkas.status',
                'kkas.approved_by_katim_date',
                'kkas.approved_by_dalnis_date',
                'kkas.approved_by_daltu_date',
                'kkas.created_by',
                'kkas.created_at',
                'pkas.nomor_pka',
                'pkas.bidang',
                'pkas.jenis_audit',
                'sprins.no_sprin',
                'sprins.tahap',
                'sprins.sprin_tipe',
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
                'tabulasis as tabulasi_temuan_count' => fn (Builder $q) => $q->where('jenis_tabulasi', 'Temuan'),
                'tabulasis as tabulasi_konsultasi_count' => fn (Builder $q) => $q->where('jenis_tabulasi', 'Konsultasi'),
                'tabulasis as tabulasi_positif_count' => fn (Builder $q) => $q->where('jenis_tabulasi', 'Positif'),
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

        $datatable = DataTables::eloquent($query)
            ->addIndexColumn()
            ->order(function (Builder $builder) use ($request, $auditeeNameSql): void {
                $order = $request->input('order');
                $colIndex = data_get($order, '0.column');
                $dir = strtolower((string) data_get($order, '0.dir', 'desc')) === 'asc' ? 'asc' : 'desc';

                $colMap = [
                    1 => 'sprins.no_sprin',
                    2 => 'auditor_name',
                    3 => DB::raw($auditeeNameSql),
                    4 => 'pkas.bidang',
                    7 => 'kkas.status',
                ];

                if ($colIndex !== null && $colIndex !== '' && isset($colMap[(int) $colIndex])) {
                    $builder->orderBy($colMap[(int) $colIndex], $dir);
                }

                $builder->orderByDesc('kkas.created_at')->orderByDesc('kkas.id');
            })
            ->addColumn('nomor_pka', fn (Kka $k): string => (string) ($k->nomor_pka ?? $k->pka?->nomor_pka ?? ''))
            ->addColumn('no_sprin', fn (Kka $k): string => (string) ($k->no_sprin ?? $k->pka?->sprin?->no_sprin ?? ''))
            ->addColumn('auditor_name', fn (Kka $k): string => (string) ($k->auditor_name ?? ''))
            ->addColumn('bidang', fn (Kka $k): string => (string) ($k->bidang ?? ''))
            ->addColumn('tahap', fn (Kka $k): string => (string) ($k->tahap ?? ''))
            ->addColumn('tanggal_kka_formatted', fn (Kka $k): string => $k->tanggal_kka?->format('d/m/Y') ?? '-')
            ->addColumn('auditee_name', fn (Kka $k): string => $this->auditeeName($k))
            ->addColumn('auditee_parent', fn (Kka $k): ?string => $this->auditeeParent($k))
            ->addColumn('auditee_sub_unit', fn (Kka $k): ?string => $this->auditeeSubUnit($k))
            ->addColumn('auditee_detail', fn (Kka $k): string => $this->auditeeDetail($k))
            ->addColumn('auditee_hierarchy', fn (Kka $k): array => $this->auditeeHierarchy($k))
            ->addColumn('tabulasi_count', fn (Kka $k): int => (int) ($k->tabulasi_temuan_count ?? 0))
            ->addColumn('temuan_count', fn (Kka $k): int => (int) ($k->tabulasi_temuan_count ?? 0))
            ->addColumn('konsultasi_count', fn (Kka $k): int => (int) ($k->tabulasi_konsultasi_count ?? 0))
            ->addColumn('positif_count', fn (Kka $k): int => (int) ($k->tabulasi_positif_count ?? 0))
            ->addColumn('tabulasi_url', fn (Kka $k): string => route('audit.kka.tabulasi.index', [$k, 'tabulasi']))
            ->addColumn('konsultasi_url', fn (Kka $k): string => route('audit.kka.tabulasi.index', [$k, 'konsultasi']))
            ->addColumn('positif_url', fn (Kka $k): string => route('audit.kka.tabulasi.index', [$k, 'positif']))
            ->addColumn('tinjut_temuan_url', fn (Kka $k): string => route('audit.tindak-lanjut.show', [$k, 'tabulasi']))
            ->addColumn('tinjut_konsultasi_url', fn (Kka $k): string => route('audit.tindak-lanjut.show', [$k, 'konsultasi']))
            ->addColumn('tinjut_positif_url', fn (Kka $k): string => route('audit.tindak-lanjut.show', [$k, 'positif']))
            ->addColumn('print_url', fn (Kka $k): string => route('audit.kka.print', $k));

        foreach (KkaReview::TYPES as $type) {
            $relation = 'latest'.Str::studly($type).'Review';
            $approvedColumn = "approved_by_{$type}_date";

            $datatable
                ->addColumn("latest_{$type}_review_status", fn (Kka $k): ?string => $k->{$relation}?->status ?? ($k->{$approvedColumn} ? KkaReview::STATUS_DISETUJUI : null))
                ->addColumn("latest_{$type}_review_date", fn (Kka $k): ?string => $k->{$relation}?->created_at?->timezone('Asia/Jakarta')->format('d/m/Y H:i')
                    ?? $k->{$approvedColumn}?->timezone('Asia/Jakarta')->format('d/m/Y H:i'))
                ->addColumn("can_review_{$type}", fn (Kka $k): bool => self::canUserReview($authUser, $k, $type));
        }

        return $datatable
            ->rawColumns(['no_sprin', 'nomor_pka', 'nomor_kka', 'auditor_name', 'auditee_name', 'auditee_detail', 'bidang', 'tempat'])
            ->filterColumn('sprins.no_sprin', function (Builder $query, string $keyword): void {
                $query->where(function (Builder $q) use ($keyword): void {
                    $q->where('sprins.no_sprin', 'like', "%{$keyword}%")
                        ->orWhere('pkas.nomor_pka', 'like', "%{$keyword}%")
                        ->orWhere('kkas.nomor_kka', 'like', "%{$keyword}%")
                        ->orWhere('kkas.tempat', 'like', "%{$keyword}%");
                });
            })
            ->filterColumn('auditor_name', function (Builder $query, string $keyword): void {
                $query->where(function (Builder $q) use ($keyword): void {
                    $q->where('auditor.name', 'like', "%{$keyword}%")
                        ->orWhere('pembuat.name', 'like', "%{$keyword}%");
                });
            })
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
            ->filterColumn('pkas.bidang', function (Builder $query, string $keyword): void {
                $query->where(function (Builder $q) use ($keyword): void {
                    $q->where('pkas.bidang', 'like', "%{$keyword}%")
                        ->orWhere('sprins.tahap', 'like', "%{$keyword}%");
                });
            })
            ->filterColumn('kkas.status', function (Builder $query, string $keyword): void {
                $query->where('kkas.status', 'like', "%{$keyword}%");
            })
            ->toJson();
    }

    public static function canUserReview(?User $user, Kka $kka, string $type): bool
    {
        if (! $user) {
            return false;
        }

        if ($user->role === 'Super Admin') {
            return true;
        }

        $kka->loadMissing('pka.sprin:id,sprin_tipe,struktur_perangkat_daerah_id,kode_wilayah,dalnis,daltu,katim,katim_2');
        $pka = $kka->pka;

        if (! $pka) {
            return false;
        }

        return match ($type) {
            KkaReview::TYPE_KATIM => in_array(
                (int) $user->id,
                array_filter([(int) $pka->sprin?->katim, (int) $pka->sprin?->katim_2]),
                true
            ),
            KkaReview::TYPE_DALNIS => PkaDataTable::canUserReview($user, $pka, 'dalnis'),
            KkaReview::TYPE_DALTU => PkaDataTable::canUserReview($user, $pka, 'daltu'),
            default => false,
        };
    }

    protected function auditeeName(Kka $kka): string
    {
        return (string) ($kka->spd_nama_unit ?? $kka->auditeePerangkatDaerah?->nama_unit ?? '-');
    }

    protected function auditeeParent(Kka $kka): ?string
    {
        if (! empty($kka->spd_parent_name)) {
            $level = isset($kka->spd_level) ? (int) $kka->spd_level : null;
            if ($level !== null && $level > 1) {
                return (string) $kka->spd_parent_name;
            }

            if (! in_array($kka->spd_jenis_unit, ['Kecamatan', 'Dinas', 'Badan', 'Inspektorat', 'Sekretariat', 'Sekretariat Daerah', 'Sekretariat DPRD'], true)) {
                return (string) $kka->spd_parent_name;
            }
        }

        return null;
    }

    protected function auditeeSubUnit(Kka $kka): ?string
    {
        if (! $this->auditeeParent($kka)) {
            return null;
        }

        $rawName = (string) ($kka->spd_nama_unit ?? $kka->auditeePerangkatDaerah?->nama_unit ?? '');
        if ($rawName === '') {
            return null;
        }

        return preg_replace('/\s*-\s*Kec\.?\s+.*$/i', '', $rawName);
    }

    protected function auditeeDetail(Kka $kka): string
    {
        return $this->auditeeParent($kka) ?? ($kka->auditee_level === 'Kecamatan' ? 'Kecamatan' : 'Dinas / OPD');
    }

    /**
     * @return list<array{label: string, level: string, is_target: bool}>
     */
    protected function auditeeHierarchy(Kka $kka): array
    {
        $name = (string) ($kka->spd_nama_unit ?? $kka->auditeePerangkatDaerah?->nama_unit ?? '-');
        $level = $kka->auditee_level ?? 'Dinas';

        return [
            ['label' => $name, 'level' => $level, 'is_target' => true],
        ];
    }
}
