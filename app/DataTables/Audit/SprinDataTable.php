<?php

namespace App\DataTables\Audit;

use App\Models\Sprin;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class SprinDataTable
{
    /**
     * @return list<array<string, mixed>>
     */
    public function columns(): array
    {
        return [
            ['data' => 'DT_RowIndex', 'name' => 'DT_RowIndex', 'label' => '#', 'sortable' => false, 'searchable' => false, 'align' => 'center', 'width' => 'w-12'],
            ['data' => 'no_sprin', 'name' => 'sprins.no_sprin', 'label' => 'NO. SPRIN', 'sortable' => true, 'searchable' => true, 'width' => 'w-48'],
            ['data' => 'sprin_tipe', 'name' => 'sprins.sprin_tipe', 'label' => 'TIPE', 'sortable' => true, 'searchable' => true, 'align' => 'center', 'width' => 'w-32'],
            ['data' => 'nama_tim', 'name' => 'tims.nama_tim', 'label' => 'TIM', 'sortable' => true, 'searchable' => true, 'width' => 'min-w-[16rem]'],
            ['data' => 'tahap', 'name' => 'sprins.tahap', 'label' => 'TAHAP', 'sortable' => true, 'searchable' => true, 'align' => 'center', 'width' => 'w-20'],
            ['data' => 'tgl_mulai', 'name' => 'sprins.tgl_mulai', 'label' => 'PERIODE', 'sortable' => true, 'searchable' => false, 'align' => 'center', 'width' => 'w-32'],
            ['data' => 'approved_by_daltu_date', 'name' => 'sprins.approved_by_daltu_date', 'label' => 'REVIU DALTU', 'sortable' => true, 'searchable' => false, 'align' => 'center', 'width' => 'w-44'],
            ['data' => 'actions', 'name' => 'actions', 'label' => 'AKSI', 'sortable' => false, 'searchable' => false, 'align' => 'center', 'width' => 'w-24'],
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $tims
     * @return array<string, mixed>
     */
    public function config(array $tims): array
    {
        $authUser = auth()->user();
        $isAdminNasional = $authUser?->role === 'Admin Nasional';
        $isAdminWilayah = $authUser?->role === 'Admin Wilayah';
        $isDaltu = $authUser?->role === 'Koordinator dan Pengendali';

        $scopeLabel = null;
        $scopeName = null;
        if ($isAdminNasional || ($isDaltu && $authUser?->plotting_id)) {
            $scopeLabel = 'WIL';
            $scopeName = $authUser->plotting?->nama_unit ?? $authUser->plotting?->plot;
        } elseif ($isAdminWilayah || ($isDaltu && $authUser?->kode_wilayah)) {
            $scopeLabel = 'Provinsi';
            $provinceCode = substr(str_replace('.', '', (string) $authUser->kode_wilayah), 0, 2);
            $scopeName = DB::table('struktur_perangkat_daerah')->where('kode_wilayah', $provinceCode)->value('nama_unit');
        }

        return [
            'indexUrl' => route('audit.sprin.index'),
            'storeUrl' => route('audit.sprin.store'),
            'baseUrl' => url('/audit/sprin'),
            'scopeLabel' => $scopeLabel,
            'scopeName' => $scopeName,
            'tims' => $tims,
            'tahaps' => Sprin::TAHAPS,
            'columns' => $this->columns(),
            'defaultSortCol' => null,
            'defaultSortDir' => 'desc',
        ];
    }

    public function toJson(Request $request): JsonResponse
    {
        $filterTahap = (string) $request->query('tahap', '');
        $authUser = $request->user();

        $query = Sprin::query()
            ->visibleTo($authUser)
            ->join('tims', 'tims.id', '=', 'sprins.tim_id')
            ->leftJoin('struktur_perangkat_daerah as spd', function ($join): void {
                $join->on('spd.id', '=', 'sprins.struktur_perangkat_daerah_id')
                    ->orOn('spd.kode_wilayah', '=', 'sprins.kode_wilayah')
                    ->whereNull('spd.deleted_at');
            })
            ->with(['latestReview:id,sprin_id,status,created_at'])
            ->select([
                'sprins.*',
                'tims.nama_tim',
                'spd.nama_unit as plot_name',
                'spd.nama_unit as province_name',
                'spd.nama_unit as unit_name',
                'spd.jenis_unit',
                'spd.pimpinan',
            ]);

        if ($filterTahap !== '') {
            $query->where('sprins.tahap', $filterTahap);
        }

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->order(function (Builder $builder) use ($request): void {
                $order = $request->input('order');
                if (! $request->has('order') || empty($order) || ! isset($order[0]['column'])) {
                    $builder->orderByDesc('sprins.created_at')->orderByDesc('sprins.id');

                    return;
                }

                $colIndex = $order[0]['column'];
                if ($colIndex === null || $colIndex === '' || (int) $colIndex < 0) {
                    $builder->orderByDesc('sprins.created_at')->orderByDesc('sprins.id');

                    return;
                }

                $dir = strtolower((string) data_get($order, '0.dir', 'desc')) === 'asc' ? 'asc' : 'desc';

                $colMap = [
                    1 => 'sprins.no_sprin',
                    2 => 'sprins.sprin_tipe',
                    3 => 'tims.nama_tim',
                    4 => 'sprins.tahap',
                    5 => 'sprins.tgl_mulai',
                    6 => 'sprins.approved_by_daltu_date',
                ];

                $colIndexInt = (int) $colIndex;
                if (isset($colMap[$colIndexInt])) {
                    $builder->orderBy($colMap[$colIndexInt], $dir)->orderByDesc('sprins.created_at')->orderByDesc('sprins.id');
                } else {
                    $builder->orderByDesc('sprins.created_at')->orderByDesc('sprins.id');
                }
            })
            ->addColumn('plot_name', fn (Sprin $sprin): ?string => in_array($sprin->sprin_tipe, ['Nasional', 'Dinas'], true) ? ($sprin->plot_name ?? $sprin->plotName()) : null)
            ->addColumn('province_name', fn (Sprin $sprin): ?string => in_array($sprin->sprin_tipe, ['Wilayah', 'Kecamatan'], true) ? ($sprin->province_name ?? $sprin->provinceName()) : null)
            ->editColumn('tgl_mulai', fn (Sprin $sprin): string => $sprin->tgl_mulai->format('Y-m-d'))
            ->addColumn('tgl_akhir_value', fn (Sprin $sprin): string => $sprin->tgl_akhir->format('Y-m-d'))
            ->editColumn('approved_by_daltu_date', fn (Sprin $sprin): ?string => $sprin->approved_by_daltu_date?->timezone('Asia/Jakarta')->format('Y-m-d H:i'))
            ->addColumn('latest_review_status', fn (Sprin $sprin): ?string => $sprin->latestReview?->status ?? ($sprin->approved_by_daltu_date ? 'Disetujui' : null))
            ->addColumn('latest_review_date', fn (Sprin $sprin): ?string => $sprin->latestReview?->created_at?->timezone('Asia/Jakarta')->format('d/m/Y H:i') ?? $sprin->approved_by_daltu_date?->timezone('Asia/Jakarta')->format('d/m/Y H:i'))
            ->addColumn('can_review', function (Sprin $sprin) use ($authUser): bool {
                if (! $authUser) {
                    return false;
                }

                if ($authUser->role === 'Super Admin') {
                    return true;
                }

                if ($authUser->role === 'Koordinator dan Pengendali') {
                    if ($authUser->plotting_id) {
                        return (int) $sprin->plotting_id === (int) $authUser->plotting_id;
                    }

                    if ($authUser->kode_wilayah) {
                        $authProvinceCode = substr(str_replace('.', '', (string) $authUser->kode_wilayah), 0, 2);

                        return $sprin->sprin_tipe === 'Wilayah' && $sprin->kode_wilayah === $authProvinceCode;
                    }

                    return (int) $authUser->id === (int) $sprin->daltu;
                }

                return false;
            })
            ->addColumn('surat_perintah_url', fn (Sprin $sprin): ?string => $sprin->suratPerintahUrl())
            ->addColumn('surat_pemberitahuan_url', fn (Sprin $sprin): ?string => $sprin->suratPemberitahuanUrl())
            ->filterColumn('nama_tim', function (Builder $query, string $keyword): void {
                $query->where('tims.nama_tim', 'like', "%{$keyword}%");
            })
            ->filterColumn('sprin_tipe', function (Builder $query, string $keyword): void {
                $query->where(function (Builder $q) use ($keyword): void {
                    $q->where('sprins.sprin_tipe', 'like', "%{$keyword}%")
                        ->orWhere('spd.nama_unit', 'like', "%{$keyword}%");
                });
            })
            ->toJson();
    }
}
