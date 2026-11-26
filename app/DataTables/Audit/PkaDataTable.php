<?php

namespace App\DataTables\Audit;

use App\Models\Pka;
use App\Models\User;
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
            ['data' => 'reviews', 'name' => 'reviews', 'label' => 'REVIU & VALIDASI', 'sortable' => false, 'searchable' => false, 'align' => 'center', 'width' => 'w-44'],
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

        $query = Pka::query()
            ->visibleTo($authUser)
            ->join('sprins', 'sprins.id', '=', 'pkas.sprin_id')
            ->leftJoin('template_pkas', 'template_pkas.id', '=', 'pkas.template_pka_id')
            ->with([
                'latestDalnisReview:id,pka_id,role_type,status,created_at',
                'latestDaltuReview:id,pka_id,role_type,status,created_at',
            ])
            ->select([
                'pkas.*',
                'sprins.no_sprin',
                'sprins.sprin_tipe',
                'sprins.tahap',
                'sprins.plotting_id as sprin_plotting_id',
                'sprins.kode_wilayah as sprin_kode_wilayah',
                'sprins.dalnis as sprin_dalnis',
                'sprins.daltu as sprin_daltu',
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
            ->order(function (Builder $builder) use ($request): void {
                $order = $request->input('order');
                if (! $request->has('order') || empty($order) || ! isset($order[0]['column'])) {
                    $builder->orderByDesc('pkas.created_at')->orderByDesc('pkas.id');

                    return;
                }

                $colIndex = $order[0]['column'];
                if ($colIndex === null || $colIndex === '' || (int) $colIndex < 0) {
                    $builder->orderByDesc('pkas.created_at')->orderByDesc('pkas.id');

                    return;
                }

                $dir = strtolower((string) data_get($order, '0.dir', 'desc')) === 'asc' ? 'asc' : 'desc';

                $colMap = [
                    1 => 'sprins.no_sprin',
                    2 => 'sprins.sprin_tipe',
                    3 => 'pkas.bidang',
                    4 => 'pkas.jenis_audit',
                    5 => 'pkas.status',
                    8 => 'pkas.tanggal',
                ];

                $colIndexInt = (int) $colIndex;
                if (isset($colMap[$colIndexInt])) {
                    $builder->orderBy($colMap[$colIndexInt], $dir)->orderByDesc('pkas.created_at')->orderByDesc('pkas.id');
                } else {
                    $builder->orderByDesc('pkas.created_at')->orderByDesc('pkas.id');
                }
            })
            ->addColumn('print_url', fn (Pka $p): string => route('audit.pka.print', $p))
            ->addColumn('langkah_kerja_url', fn (Pka $p): string => route('audit.pka.langkah-kerja.index', $p))
            ->addColumn('tanggal_formatted', fn (Pka $p): string => $p->tanggal?->format('d/m/Y') ?? '-')
            ->addColumn('formatted_anggaran', fn (Pka $p): string => $p->formattedAnggaran())
            ->addColumn('lampiran_url', fn (Pka $p): ?string => $p->lampiranUrl())
            ->addColumn('kka_count', fn (Pka $p): int => 0)
            ->editColumn('approved_by_dalnis_date', fn (Pka $p): ?string => $p->approved_by_dalnis_date?->timezone('Asia/Jakarta')->format('Y-m-d H:i'))
            ->editColumn('approved_by_daltu_date', fn (Pka $p): ?string => $p->approved_by_daltu_date?->timezone('Asia/Jakarta')->format('Y-m-d H:i'))
            ->addColumn('latest_dalnis_review_status', fn (Pka $p): ?string => $p->latestDalnisReview?->status ?? ($p->approved_by_dalnis_date ? 'Disetujui' : null))
            ->addColumn('latest_dalnis_review_date', fn (Pka $p): ?string => $p->latestDalnisReview?->created_at?->timezone('Asia/Jakarta')->format('d/m/Y H:i') ?? $p->approved_by_dalnis_date?->timezone('Asia/Jakarta')->format('d/m/Y H:i'))
            ->addColumn('can_review_dalnis', fn (Pka $p): bool => self::canUserReview($authUser, $p, 'dalnis'))
            ->addColumn('latest_daltu_review_status', fn (Pka $p): ?string => $p->latestDaltuReview?->status ?? ($p->approved_by_daltu_date ? 'Disetujui' : null))
            ->addColumn('latest_daltu_review_date', fn (Pka $p): ?string => $p->latestDaltuReview?->created_at?->timezone('Asia/Jakarta')->format('d/m/Y H:i') ?? $p->approved_by_daltu_date?->timezone('Asia/Jakarta')->format('d/m/Y H:i'))
            ->addColumn('can_review_daltu', fn (Pka $p): bool => self::canUserReview($authUser, $p, 'daltu'))
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

    public static function canUserReview(?User $user, Pka $pka, string $type): bool
    {
        if (! $user) {
            return false;
        }

        if ($user->role === 'Super Admin') {
            return true;
        }

        $sprin = $pka->relationLoaded('sprin') ? $pka->sprin : null;
        $sprinPlottingId = $pka->sprin_plotting_id ?? $sprin?->plotting_id ?? $pka->sprin?->plotting_id;
        $sprinKodeWilayah = $pka->sprin_kode_wilayah ?? $sprin?->kode_wilayah ?? $pka->sprin?->kode_wilayah;
        $sprinTipe = $pka->sprin_tipe ?? $sprin?->sprin_tipe ?? $pka->sprin?->sprin_tipe;
        $sprinDalnis = $pka->sprin_dalnis ?? $sprin?->dalnis ?? $pka->sprin?->dalnis;
        $sprinDaltu = $pka->sprin_daltu ?? $sprin?->daltu ?? $pka->sprin?->daltu;

        if ($type === 'dalnis') {
            if ((int) $user->id === (int) $sprinDalnis) {
                return true;
            }

            if ($user->role === 'Pengawas Tim') {
                if ($user->plotting_id) {
                    return (int) $sprinPlottingId === (int) $user->plotting_id;
                }

                if ($user->kode_wilayah) {
                    $authProvinceCode = substr(str_replace('.', '', (string) $user->kode_wilayah), 0, 2);

                    return $sprinTipe === 'Wilayah' && $sprinKodeWilayah === $authProvinceCode;
                }

                return (int) $user->id === (int) $sprinDalnis;
            }

            return false;
        }

        if ($type === 'daltu') {
            if ((int) $user->id === (int) $sprinDaltu) {
                return true;
            }

            if ($user->role === 'Koordinator dan Pengendali') {
                if ($user->plotting_id) {
                    return (int) $sprinPlottingId === (int) $user->plotting_id;
                }

                if ($user->kode_wilayah) {
                    $authProvinceCode = substr(str_replace('.', '', (string) $user->kode_wilayah), 0, 2);

                    return $sprinTipe === 'Wilayah' && $sprinKodeWilayah === $authProvinceCode;
                }

                return (int) $user->id === (int) $sprinDaltu;
            }

            return false;
        }

        return false;
    }
}
