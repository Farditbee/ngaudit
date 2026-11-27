<?php

namespace App\DataTables\Audit;

use App\Models\Kka;
use App\Models\Pka;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TabulasiDataTable extends KkaDataTable
{
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
            'indexUrl' => route('audit.tabulasi.index'),
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
        return parent::toJson($request);
    }
}
