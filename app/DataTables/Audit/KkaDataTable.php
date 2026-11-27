<?php

namespace App\DataTables\Audit;

use App\Models\Kka;
use App\Models\KkaReview;
use App\Models\Pka;
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
        return [
            'indexUrl' => route('audit.kka.index'),
            'storeUrl' => route('audit.kka.store'),
            'baseUrl' => url('/audit/kka'),
            'bidangs' => Pka::BIDANGS,
            'jenisAudits' => Pka::JENIS_AUDITS,
            'statuses' => Kka::STATUSES,
            'levels' => Kka::AUDITEE_LEVELS,
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

        $auditeeNameSql = 'COALESCE(auditee_plot.plot, aw.desa_kelurahan, aw.kecamatan, aw.kabupaten_kota, aw.provinsi)';

        $query = Kka::query()
            ->visibleTo($authUser)
            ->join('pkas', 'pkas.id', '=', 'kkas.pka_id')
            ->whereNull('pkas.deleted_at')
            ->join('sprins', 'sprins.id', '=', 'pkas.sprin_id')
            ->leftJoin('users as auditor', 'auditor.id', '=', 'kkas.dibuat_oleh_id')
            ->leftJoin('users as pembuat', 'pembuat.id', '=', 'kkas.created_by')
            ->leftJoin('plottings as auditee_plot', 'auditee_plot.id', '=', 'kkas.auditee_plotting_id')
            ->leftJoin('wilayah as aw', function ($join): void {
                $join->on('aw.kode_wilayah', '=', 'kkas.auditee_kode_wilayah')
                    ->whereNull('aw.deleted_at');
            })
            ->with([
                'pka:id,sprin_id',
                'pka.sprin:id,sprin_tipe,plotting_id,kode_wilayah,dalnis,daltu,katim,katim_2',
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
                'kkas.auditee_plotting_id',
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
                'auditee_plot.plot as auditee_plot_name',
                'aw.provinsi as aw_provinsi',
                'aw.kabupaten_kota as aw_kabupaten_kota',
                'aw.kecamatan as aw_kecamatan',
                'aw.desa_kelurahan as aw_desa_kelurahan',
            ])
            ->withCount([
                'tabulasis' => fn (Builder $q) => $q->where('jenis_tabulasi', 'Temuan'),
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
            ->addColumn('tanggal_kka_formatted', fn (Kka $k): string => $k->tanggal_kka?->format('d/m/Y') ?? '-')
            ->addColumn('auditee_name', fn (Kka $k): string => $this->auditeeName($k))
            ->addColumn('auditee_detail', fn (Kka $k): string => $this->auditeeDetail($k))
            ->addColumn('auditee_hierarchy', fn (Kka $k): array => $this->auditeeHierarchy($k))
            ->addColumn('tabulasi_count', fn (Kka $k): int => (int) ($k->tabulasis_count ?? 0))
            ->addColumn('tabulasi_url', fn (Kka $k): string => route('audit.kka.tabulasi.index', [$k, 'tabulasi']))
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
                    $q->where('auditee_plot.plot', 'like', "%{$keyword}%")
                        ->orWhere('aw.provinsi', 'like', "%{$keyword}%")
                        ->orWhere('aw.kabupaten_kota', 'like', "%{$keyword}%")
                        ->orWhere('aw.kecamatan', 'like', "%{$keyword}%")
                        ->orWhere('aw.desa_kelurahan', 'like', "%{$keyword}%");
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

        $kka->loadMissing('pka.sprin:id,sprin_tipe,plotting_id,kode_wilayah,dalnis,daltu,katim,katim_2');
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
        if ($kka->auditee_level === 'Nasional') {
            return (string) ($kka->auditee_plot_name ?? $kka->auditeePlotting?->plot ?? '-');
        }

        $name = $kka->aw_desa_kelurahan ?? $kka->aw_kecamatan ?? $kka->aw_kabupaten_kota ?? $kka->aw_provinsi;

        return $name ? Str::title(strtolower((string) $name)) : '-';
    }

    protected function auditeeDetail(Kka $kka): string
    {
        if ($kka->auditee_level === 'Nasional') {
            return 'Nasional';
        }

        $parts = collect([
            $kka->aw_kecamatan && $kka->aw_desa_kelurahan ? $kka->aw_kecamatan : null,
            $kka->aw_kabupaten_kota && ($kka->aw_kecamatan || $kka->aw_desa_kelurahan) ? $kka->aw_kabupaten_kota : null,
            $kka->aw_provinsi,
        ])->filter()->map(fn ($part): string => Str::title(strtolower((string) $part)));

        return $parts->isEmpty() ? 'Wilayah' : $parts->implode(', ');
    }

    /**
     * Susun hierarki wilayah auditee dari atas ke bawah (Provinsi -> Kabupaten/Kota -> Kecamatan -> Desa)
     * atau langsung nama WIL jika Nasional. Item terakhir ditandai sebagai target auditee (badge warning).
     *
     * @return list<array{label: string, level: string, is_target: bool}>
     */
    protected function auditeeHierarchy(Kka $kka): array
    {
        if ($kka->auditee_level === 'Nasional') {
            $plot = (string) ($kka->auditee_plot_name ?? $kka->auditeePlotting?->plot ?? '-');

            return [
                ['label' => $plot, 'level' => 'plot', 'is_target' => true],
            ];
        }

        $candidates = [
            'province' => $kka->aw_provinsi ? Str::title(strtolower((string) $kka->aw_provinsi)) : null,
            'city' => $kka->aw_kabupaten_kota ? Str::title(strtolower((string) $kka->aw_kabupaten_kota)) : null,
            'district' => $kka->aw_kecamatan ? Str::title(strtolower((string) $kka->aw_kecamatan)) : null,
            'village' => $kka->aw_desa_kelurahan ? Str::title(strtolower((string) $kka->aw_desa_kelurahan)) : null,
        ];

        $levels = [];
        foreach ($candidates as $levelKey => $label) {
            if ($label !== null) {
                $levels[] = ['label' => $label, 'level' => $levelKey];
            }
        }

        if (empty($levels)) {
            return [
                ['label' => '-', 'level' => 'unknown', 'is_target' => true],
            ];
        }

        $lastIndex = count($levels) - 1;
        $items = [];
        foreach ($levels as $index => $entry) {
            $items[] = [
                'label' => $entry['label'],
                'level' => $entry['level'],
                'is_target' => $index === $lastIndex,
            ];
        }

        return $items;
    }
}
