<?php

namespace App\DataTables\Audit;

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
        $config = parent::config($pkas, $templates, $plottings, $provinces);
        $config['indexUrl'] = route('audit.tabulasi.index');

        return $config;
    }

    public function toJson(Request $request): JsonResponse
    {
        return parent::toJson($request);
    }
}
