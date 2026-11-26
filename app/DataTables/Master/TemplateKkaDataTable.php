<?php

namespace App\DataTables\Master;

use App\Models\TemplateKka;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class TemplateKkaDataTable
{
    /**
     * @return list<array<string, mixed>>
     */
    public function columns(): array
    {
        return [
            ['data' => 'DT_RowIndex', 'name' => 'DT_RowIndex', 'label' => '#', 'sortable' => false, 'searchable' => false, 'align' => 'center', 'width' => 'w-12'],
            ['data' => 'nama_template', 'name' => 'template_kkas.nama_template', 'label' => 'NAMA TEMPLATE', 'sortable' => true, 'searchable' => true, 'width' => 'min-w-[14rem]'],
            ['data' => 'lingkup', 'name' => 'template_kkas.lingkup', 'label' => 'LEVEL / WILAYAH', 'sortable' => true, 'searchable' => true, 'align' => 'center', 'width' => 'w-36'],
            ['data' => 'created_at_formatted', 'name' => 'template_kkas.created_at', 'label' => 'DIBUAT PADA', 'sortable' => true, 'searchable' => false, 'align' => 'center', 'width' => 'w-36'],
            ['data' => 'actions', 'name' => 'actions', 'label' => 'AKSI', 'sortable' => false, 'searchable' => false, 'align' => 'center', 'width' => 'w-36'],
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
            default => TemplateKka::LINGKUPS,
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
            'indexUrl' => route('master.template.kka.index'),
            'storeUrl' => route('master.template.kka.store'),
            'baseUrl' => url('/master/template/kka'),
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

        $query = TemplateKka::query()
            ->visibleTo($authUser)
            ->leftJoin('plottings', 'plottings.id', '=', 'template_kkas.plotting_id')
            ->leftJoin('wilayah as p', function ($join): void {
                $join->on('p.kode_wilayah', '=', 'template_kkas.kode_wilayah')
                    ->whereNull('p.kabupaten_kota');
            })
            ->select([
                'template_kkas.*',
                'plottings.plot as plot_name',
                'p.provinsi as province_name',
            ]);

        if ($filterLingkup !== '') {
            $query->where('template_kkas.lingkup', $filterLingkup);
        }

        if (! $request->has('order')) {
            $query->orderByDesc('template_kkas.created_at')->orderByDesc('template_kkas.id');
        }

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('plot_name', fn (TemplateKka $t): ?string => $t->plot_name ?? $t->plotName())
            ->addColumn('province_name', fn (TemplateKka $t): ?string => $t->province_name ?? $t->provinceName())
            ->addColumn('created_at_formatted', fn (TemplateKka $t): string => $t->created_at?->timezone('Asia/Jakarta')->format('d/m/Y H:i') ?? '-')
            ->rawColumns(['nama_template', 'tujuan', 'langkah', 'hasil'])
            ->filterColumn('lingkup', function (Builder $query, string $keyword): void {
                $query->where(function (Builder $q) use ($keyword): void {
                    $q->where('template_kkas.lingkup', 'like', "%{$keyword}%")
                        ->orWhere('plottings.plot', 'like', "%{$keyword}%")
                        ->orWhere('p.provinsi', 'like', "%{$keyword}%");
                });
            })
            ->toJson();
    }
}
