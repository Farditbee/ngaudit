<?php

namespace App\DataTables\Master;

use App\Models\StrukturPerangkatDaerah;
use App\Models\TemplatePka;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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
        $isSuperAdmin = ! $authUser || $authUser->role === 'Super Admin';
        $isAdminDinas = in_array($authUser?->role, ['Admin Nasional', 'Admin Dinas'], true) || ($authUser?->level === 'Dinas');
        $isAdminKecamatan = in_array($authUser?->role, ['Admin Wilayah', 'Admin Kecamatan'], true) || ($authUser?->level === 'Kecamatan');

        $lingkups = match (true) {
            $isAdminDinas => ['Dinas'],
            $isAdminKecamatan => ['Kecamatan'],
            default => TemplatePka::LINGKUPS,
        };

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

        $scopeName = null;
        if ($authUser?->struktur_perangkat_daerah_id) {
            $scopeName = $authUser->strukturPerangkatDaerah?->nama_unit ?? $authUser->plotting?->nama_unit;
        }

        return [
            'indexUrl' => route('master.template.pka.index'),
            'storeUrl' => route('master.template.pka.store'),
            'baseUrl' => url('/master/template/pka'),
            'authRole' => $authUser?->role,
            'authSpdId' => $authUser?->struktur_perangkat_daerah_id ? (string) $authUser->struktur_perangkat_daerah_id : null,
            'isSpdReadonly' => ! $isSuperAdmin && (bool) $authUser?->struktur_perangkat_daerah_id,
            'scopeName' => $scopeName,
            'lingkups' => $lingkups,
            'dinasOptions' => $dinasOptions,
            'kecamatanOptions' => $kecamatanOptions,
            'columns' => $this->columns(),
        ];
    }

    public function toJson(Request $request): JsonResponse
    {
        $filterLingkup = (string) $request->query('lingkup', '');
        $authUser = $request->user();
        $isAdminDinas = in_array($authUser?->role, ['Admin Nasional', 'Admin Dinas'], true) || ($authUser?->level === 'Dinas');
        $isAdminKecamatan = in_array($authUser?->role, ['Admin Wilayah', 'Admin Kecamatan'], true) || ($authUser?->level === 'Kecamatan');

        $query = TemplatePka::query()
            ->visibleTo($authUser)
            ->leftJoin('struktur_perangkat_daerah as spd', function ($join): void {
                $join->on('spd.id', '=', 'template_pkas.struktur_perangkat_daerah_id')
                    ->orOn('spd.kode_wilayah', '=', 'template_pkas.kode_wilayah');
            })
            ->select([
                'template_pkas.*',
                'spd.nama_unit as plot_name',
                'spd.nama_unit as province_name',
            ]);

        if ($isAdminDinas && $authUser?->struktur_perangkat_daerah_id) {
            $query->where('template_pkas.lingkup', 'Dinas')
                ->where(function ($q) use ($authUser): void {
                    $q->whereNull('template_pkas.struktur_perangkat_daerah_id')
                        ->orWhere('template_pkas.struktur_perangkat_daerah_id', $authUser->struktur_perangkat_daerah_id);
                });
        } elseif ($isAdminKecamatan && $authUser?->struktur_perangkat_daerah_id) {
            $query->where('template_pkas.lingkup', 'Kecamatan')
                ->where(function ($q) use ($authUser): void {
                    $q->whereNull('template_pkas.struktur_perangkat_daerah_id')
                        ->orWhere('template_pkas.struktur_perangkat_daerah_id', $authUser->struktur_perangkat_daerah_id);
                });
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
                        ->orWhere('spd.nama_unit', 'like', "%{$keyword}%");
                });
            })
            ->toJson();
    }
}
