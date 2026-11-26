<?php

namespace App\DataTables\Master;

use App\Models\TemplateLangkahKerja;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class TemplateLangkahKerjaDataTable
{
    /**
     * @return list<array<string, mixed>>
     */
    public function columns(): array
    {
        return [
            ['data' => 'DT_RowIndex', 'name' => 'DT_RowIndex', 'label' => '#', 'sortable' => false, 'searchable' => false, 'align' => 'center', 'width' => 'w-12'],
            ['data' => 'actions', 'name' => 'actions', 'label' => 'OPSI', 'sortable' => false, 'searchable' => false, 'align' => 'center', 'width' => 'w-24'],
            ['data' => 'nama_template', 'name' => 'template_langkah_kerjas.nama_template', 'label' => 'NAMA TEMPLATE', 'sortable' => true, 'searchable' => true, 'width' => 'min-w-[16rem]'],
            ['data' => 'lingkup', 'name' => 'template_langkah_kerjas.lingkup', 'label' => 'LEVEL / WILAYAH', 'sortable' => true, 'searchable' => true, 'align' => 'center', 'width' => 'w-40'],
            ['data' => 'jumlah_langkah_kerja', 'name' => 'template_langkah_kerjas.jumlah_langkah_kerja', 'label' => 'JUMLAH LANGKAH KERJA', 'sortable' => true, 'searchable' => false, 'align' => 'center', 'width' => 'w-48'],
            ['data' => 'created_at_formatted', 'name' => 'template_langkah_kerjas.created_at', 'label' => 'DIBUAT PADA', 'sortable' => true, 'searchable' => false, 'align' => 'center', 'width' => 'w-36'],
        ];
    }

    /**
     * @param  iterable<int, mixed>  $plottings
     * @param  iterable<int, mixed>  $provinces
     * @return array<string, mixed>
     */
    public function config(iterable $plottings, iterable $provinces): array
    {
        $authUser = auth()->user();
        $isSuperAdmin = ! $authUser || $authUser->role === 'Super Admin';

        $isLockedNasional = ! $isSuperAdmin && ($authUser->role === 'Admin Nasional' || ($authUser->level === 'Nasional' && $authUser->plotting_id));
        $isLockedWilayah = ! $isSuperAdmin && ! $isLockedNasional && ($authUser->role === 'Admin Wilayah' || (bool) $authUser->kode_wilayah);

        $lingkups = match (true) {
            $isLockedNasional => ['Nasional'],
            $isLockedWilayah => ['Wilayah'],
            default => TemplateLangkahKerja::LINGKUPS,
        };

        $authProvinceCode = ($isLockedWilayah && $authUser?->kode_wilayah)
            ? substr(str_replace('.', '', (string) $authUser->kode_wilayah), 0, 2)
            : null;

        $scopeLabel = null;
        $scopeName = null;
        if ($isLockedNasional) {
            $scopeLabel = 'WIL';
            $scopeName = $authUser->plotting?->plot;
        } elseif ($isLockedWilayah && $authProvinceCode) {
            $scopeLabel = 'Provinsi';
            $scopeName = DB::table('wilayah')->where('kode_wilayah', $authProvinceCode)->value('provinsi');
        }

        return [
            'indexUrl' => route('master.template.langkah-kerja.index'),
            'storeUrl' => route('master.template.langkah-kerja.store'),
            'baseUrl' => url('/master/template/langkah-kerja'),
            'authRole' => $authUser?->role,
            'authPlottingId' => $isLockedNasional ? $authUser?->plotting_id : null,
            'authProvinceCode' => $authProvinceCode,
            'isPlottingLocked' => $isLockedNasional && (bool) $authUser?->plotting_id,
            'isProvinceLocked' => $isLockedWilayah && (bool) $authProvinceCode,
            'scopeLabel' => $scopeLabel,
            'scopeName' => $scopeName,
            'lingkups' => $lingkups,
            'plottings' => $plottings,
            'provinces' => $provinces,
            'columns' => $this->columns(),
        ];
    }

    public function toJson(Request $request): JsonResponse
    {
        $filterLingkup = (string) $request->query('lingkup', '');
        $authUser = $request->user();

        $query = TemplateLangkahKerja::query()
            ->visibleTo($authUser)
            ->leftJoin('plottings', 'plottings.id', '=', 'template_langkah_kerjas.plotting_id')
            ->leftJoin('wilayah as p', function ($join): void {
                $join->on('p.kode_wilayah', '=', 'template_langkah_kerjas.kode_wilayah')
                    ->whereNull('p.kabupaten_kota');
            })
            ->leftJoin('users as creator', 'creator.id', '=', 'template_langkah_kerjas.created_by')
            ->select([
                'template_langkah_kerjas.*',
                'plottings.plot as plot_name',
                'p.provinsi as province_name',
                'creator.name as creator_name',
            ]);

        if ($filterLingkup !== '') {
            $query->where('template_langkah_kerjas.lingkup', $filterLingkup);
        }

        if (! $request->has('order')) {
            $query->orderByDesc('template_langkah_kerjas.created_at')->orderByDesc('template_langkah_kerjas.id');
        }

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('detail_url', fn (TemplateLangkahKerja $t): string => route('master.template.langkah-kerja.details.index', $t))
            ->addColumn('plot_name', fn (TemplateLangkahKerja $t): ?string => $t->plot_name ?? $t->plotName())
            ->addColumn('province_name', fn (TemplateLangkahKerja $t): ?string => $t->province_name ?? $t->provinceName())
            ->addColumn('creator_name', fn (TemplateLangkahKerja $t): string => $t->creator_name ?? $t->creator?->name ?? '-')
            ->addColumn('created_at_formatted', fn (TemplateLangkahKerja $t): string => $t->created_at?->timezone('Asia/Jakarta')->format('d/m/Y H:i') ?? '-')
            ->filterColumn('lingkup', function (Builder $query, string $keyword): void {
                $query->where(function (Builder $q) use ($keyword): void {
                    $q->where('template_langkah_kerjas.lingkup', 'like', "%{$keyword}%")
                        ->orWhere('plottings.plot', 'like', "%{$keyword}%")
                        ->orWhere('p.provinsi', 'like', "%{$keyword}%");
                });
            })
            ->filterColumn('creator_name', function (Builder $query, string $keyword): void {
                $query->where('creator.name', 'like', "%{$keyword}%");
            })
            ->toJson();
    }
}
