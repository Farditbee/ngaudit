<?php

namespace App\DataTables\Master;

use App\Models\TemplateLangkahKerja;
use App\Models\TemplateLangkahKerjaDetail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class TemplateLangkahKerjaDetailDataTable
{
    /**
     * @return list<array<string, mixed>>
     */
    public function columns(): array
    {
        return [
            ['data' => 'checkbox', 'name' => 'checkbox', 'label' => '', 'sortable' => false, 'searchable' => false, 'align' => 'center', 'width' => 'w-12'],
            ['data' => 'DT_RowIndex', 'name' => 'DT_RowIndex', 'label' => '#', 'sortable' => false, 'searchable' => false, 'align' => 'center', 'width' => 'w-12'],
            ['data' => 'uraian', 'name' => 'template_langkah_kerja_details.uraian', 'label' => 'URAIAN LANGKAH KERJA', 'sortable' => true, 'searchable' => true],
            ['data' => 'actions', 'name' => 'actions', 'label' => 'AKSI', 'sortable' => false, 'searchable' => false, 'align' => 'center', 'width' => 'w-28'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function config(TemplateLangkahKerja $template): array
    {
        return [
            'templateId' => $template->id,
            'namaTemplate' => $template->nama_template,
            'lingkup' => $template->lingkup,
            'scopeName' => $template->plotName(),
            'jumlahLangkahKerja' => (int) $template->jumlah_langkah_kerja,
            'backUrl' => route('master.template.langkah-kerja.index'),
            'indexUrl' => route('master.template.langkah-kerja.details.index', $template),
            'storeUrl' => route('master.template.langkah-kerja.details.store', $template),
            'bulkDeleteUrl' => route('master.template.langkah-kerja.details.bulk-destroy', $template),
            'baseUrl' => url("/master/template/langkah-kerja/{$template->id}/detail"),
            'columns' => $this->columns(),
            'defaultSortCol' => null,
            'defaultSortDir' => 'asc',
        ];
    }

    public function toJson(Request $request, TemplateLangkahKerja $template): JsonResponse
    {
        $query = TemplateLangkahKerjaDetail::query()
            ->where('template_langkah_kerja_id', $template->id)
            ->select(['template_langkah_kerja_details.*']);

        if (! $request->has('order')) {
            $query->orderByDesc('template_langkah_kerja_details.created_at')
                ->orderByDesc('template_langkah_kerja_details.id');
        }

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('created_at_formatted', fn (TemplateLangkahKerjaDetail $d): string => $d->created_at?->timezone('Asia/Jakarta')->format('d/m/Y H:i') ?? '-')
            ->rawColumns(['uraian'])
            ->toJson();
    }
}
