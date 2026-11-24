<?php

namespace App\Http\Controllers\Master;

use App\DataTables\Master\TimDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Master\TimRequest;
use App\Models\Plotting;
use App\Models\Tim;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TimController extends Controller
{
    public function index(Request $request, TimDataTable $dataTable): View|JsonResponse
    {
        if ($request->ajax() || $request->wantsJson()) {
            return $dataTable->toJson($request);
        }

        $plottings = Plotting::query()
            ->orderBy('id')
            ->get(['id', 'plot'])
            ->map(fn (Plotting $plotting): array => [
                'id' => $plotting->id,
                'code' => (string) $plotting->id,
                'name' => $plotting->plot,
            ]);

        $provinces = DB::table('wilayah')
            ->whereRaw('CHAR_LENGTH(kode_wilayah) = 2')
            ->whereNull('deleted_at')
            ->orderBy('kode_wilayah')
            ->get(['kode_wilayah as code', 'provinsi as name']);

        $formatUserLabel = fn (User $u): string => $u->username ? "{$u->username} | {$u->name}" : $u->name;

        $daltuUsers = User::query()
            ->where('role', 'Koordinator dan Pengendali')
            ->orderBy('username')
            ->orderBy('name')
            ->get(['id', 'username', 'name'])
            ->map(fn (User $u): array => [
                'id' => $u->id,
                'code' => (string) $u->id,
                'name' => $formatUserLabel($u),
            ])
            ->values()
            ->all();

        $dalnisUsers = User::query()
            ->where('role', 'Pengawas Tim')
            ->orderBy('username')
            ->orderBy('name')
            ->get(['id', 'username', 'name'])
            ->map(fn (User $u): array => [
                'id' => $u->id,
                'code' => (string) $u->id,
                'name' => $formatUserLabel($u),
            ])
            ->values()
            ->all();

        $ketuaTimUsers = User::query()
            ->where('role', 'Ketua Tim')
            ->orderBy('username')
            ->orderBy('name')
            ->get(['id', 'username', 'name'])
            ->map(fn (User $u): array => [
                'id' => $u->id,
                'code' => (string) $u->id,
                'name' => $formatUserLabel($u),
            ])
            ->values()
            ->all();

        $auditorCandidates = User::query()
            ->where('role', 'Auditor')
            ->orderBy('username')
            ->orderBy('name')
            ->get(['id', 'username', 'name'])
            ->map(fn (User $u): array => [
                'id' => $u->id,
                'code' => (string) $u->id,
                'name' => $formatUserLabel($u),
            ])
            ->values()
            ->all();

        $tableConfig = $dataTable->config($plottings, $provinces, [
            'daltu' => $daltuUsers,
            'dalnis' => $dalnisUsers,
            'ketua_tim' => $ketuaTimUsers,
            'auditor_candidates' => $auditorCandidates,
        ]);

        return view('master.tim.index', compact('tableConfig'));
    }

    public function store(TimRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $tim = DB::transaction(function () use ($validated): Tim {
            $tim = Tim::query()->create($this->buildTimPayload($validated));
            $tim->auditors()->sync($validated['auditor_ids']);

            return $tim->load(['auditors:id,name,role']);
        });

        return response()->json([
            'message' => 'Data tim berhasil ditambahkan.',
            'data' => $tim,
        ], 201);
    }

    public function update(TimRequest $request, Tim $tim): JsonResponse
    {
        $validated = $request->validated();

        DB::transaction(function () use ($tim, $validated): void {
            $tim->update($this->buildTimPayload($validated));
            $tim->auditors()->sync($validated['auditor_ids']);
        });

        return response()->json([
            'message' => 'Data tim berhasil diperbarui.',
            'data' => $tim->fresh(['auditors:id,name,role']),
        ]);
    }

    public function destroy(Tim $tim): JsonResponse
    {
        $tim->delete();

        return response()->json([
            'message' => 'Data tim berhasil dihapus.',
        ]);
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    protected function buildTimPayload(array $validated): array
    {
        $lingkup = (string) $validated['lingkup'];

        return [
            'nama_tim' => $validated['nama_tim'],
            'lingkup' => $lingkup,
            'plotting_id' => $lingkup === 'Nasional' ? ($validated['plotting_id'] ?? null) : null,
            'province_code' => $lingkup === 'Wilayah' ? ($validated['province_code'] ?? null) : null,
            'daltu_id' => $validated['daltu_id'],
            'dalnis_id' => $validated['dalnis_id'],
            'ketua_tim_id' => $validated['ketua_tim_id'],
            'ketua_tim_2_id' => ! empty($validated['ketua_tim_2_id']) ? $validated['ketua_tim_2_id'] : null,
        ];
    }
}
