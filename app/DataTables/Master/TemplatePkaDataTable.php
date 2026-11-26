<?php

namespace App\DataTables\Master;

use App\Models\TemplatePka;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class TemplatePkaDataTable
{
    /**
     * @return list<array<string, mixed>>
     */
    public function columns(): array
    {
        return [
            ['data' => 'DT_RowIndex', 'name' => 'DT_RowIndex', 'label' => '#', 'sortable' => false, 'searchable' => false, 'align' => 'center', 'width' => 'w-12'],
            ['data' => 'nama_template', 'name' => 'template_pkas.nama_template', 'label' => 'NAMA TEMPLATE', 'sortable' => true, 'searchable' => true, 'width' => 'min-w-[14rem]'],
            ['data' => 'lingkup', 'name' => 'template_pkas.lingkup', 'label' => 'LINGKUP', 'sortable' => true, 'searchable' => true, 'align' => 'center', 'width' => 'w-36'],
            ['data' => 'created_at_formatted', 'name' => 'template_pkas.created_at', 'label' => 'DIBUAT PADA', 'sortable' => true, 'searchable' => false, 'align' => 'center', 'width' => 'w-36'],
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
        $isAdminNasional = $authUser?->role === 'Admin Nasional';
        $isAdminWilayah = $authUser?->role === 'Admin Wilayah';

        $lingkups = match (true) {
            $isAdminNasional => ['Nasional'],
            $isAdminWilayah => ['Wilayah'],
            default => TemplatePka::LINGKUPS,
        };

        $scopeLabel = null;
        $scopeName = null;
        if ($isAdminNasional) {
            $scopeLabel = 'WIL';
            $scopeName = $authUser->plotting?->plot;
        } elseif ($isAdminWilayah) {
            $scopeLabel = 'Provinsi';
            $provinceCode = substr(str_replace('.', '', (string) $authUser->kode_wilayah), 0, 2);
            $scopeName = DB::table('wilayah')->where('kode_wilayah', $provinceCode)->value('provinsi');
        }

        return [
            'indexUrl' => route('master.template.pka.index'),
            'storeUrl' => route('master.template.pka.store'),
            'baseUrl' => url('/master/template/pka'),
            'authRole' => $authUser?->role,
            'authPlottingId' => $isAdminNasional ? $authUser?->plotting_id : null,
            'authProvinceCode' => $isAdminWilayah && $authUser?->kode_wilayah
                ? substr(str_replace('.', '', (string) $authUser->kode_wilayah), 0, 2)
                : null,
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

        $query = TemplatePka::query()
            ->visibleTo($authUser)
            ->leftJoin('plottings', 'plottings.id', '=', 'template_pkas.plotting_id')
            ->leftJoin('wilayah as p', function ($join): void {
                $join->on('p.kode_wilayah', '=', 'template_pkas.kode_wilayah')
                    ->whereNull('p.kabupaten_kota');
            })
            ->select([
                'template_pkas.*',
                'plottings.plot as plot_name',
                'p.provinsi as province_name',
            ]);

        if ($authUser?->role === 'Admin Nasional' && $authUser->plotting_id) {
            $query->where('template_pkas.lingkup', 'Nasional')
                ->where('template_pkas.plotting_id', $authUser->plotting_id);
        } elseif ($authUser?->role === 'Admin Wilayah' && $authUser->kode_wilayah) {
            $authProvinceCode = substr(str_replace('.', '', (string) $authUser->kode_wilayah), 0, 2);
            $query->where('template_pkas.lingkup', 'Wilayah')
                ->where('template_pkas.kode_wilayah', $authProvinceCode);
        } elseif ($filterLingkup !== '') {
            $query->where('template_pkas.lingkup', $filterLingkup);
        }

        if (! $request->has('order')) {
            $query->orderByDesc('template_pkas.created_at')->orderByDesc('template_pkas.id');
        }

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('plot_name', fn (TemplatePka $t): ?string => $t->plot_name ?? $t->plotName())
            ->addColumn('province_name', fn (TemplatePka $t): ?string => $t->province_name ?? $t->provinceName())
            ->addColumn('created_at_formatted', fn (TemplatePka $t): string => $t->created_at?->timezone('Asia/Jakarta')->format('d/m/Y H:i') ?? '-')
            ->rawColumns(['nama_template', 'pendahuluan', 'tujuan', 'ruang_lingkup', 'sasaran', 'waktu_pelaksanaan', 'periode', 'penutup'])
            ->filterColumn('lingkup', function (Builder $query, string $keyword): void {
                $query->where(function (Builder $q) use ($keyword): void {
                    $q->where('template_pkas.lingkup', 'like', "%{$keyword}%")
                        ->orWhere('plottings.plot', 'like', "%{$keyword}%")
                        ->orWhere('p.provinsi', 'like', "%{$keyword}%");
                });
            })
            ->toJson();
    }
}
