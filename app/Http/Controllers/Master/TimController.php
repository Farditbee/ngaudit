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

        $authUser = $request->user();
        $isAdminNasional = $authUser?->role === 'Admin Nasional';
        $isAdminWilayah = $authUser?->role === 'Admin Wilayah';
        $authProvinceCode = ($isAdminWilayah && $authUser?->kode_wilayah)
            ? substr(str_replace('.', '', (string) $authUser->kode_wilayah), 0, 2)
            : null;

        $plottingsQuery = Plotting::query()->orderBy('id');
        if ($isAdminNasional && $authUser?->plotting_id) {
            $plottingsQuery->whereKey($authUser->plotting_id);
        }

        $plottings = $plottingsQuery
            ->get(['id', 'plot'])
            ->map(fn (Plotting $plotting): array => [
                'id' => $plotting->id,
                'code' => (string) $plotting->id,
                'name' => $plotting->plot,
            ])
            ->values();

        $provincesQuery = DB::table('wilayah')
            ->whereRaw('CHAR_LENGTH(kode_wilayah) = 2')
            ->whereNull('deleted_at')
            ->orderBy('kode_wilayah');

        if ($isAdminWilayah && $authProvinceCode) {
            $provincesQuery->where('kode_wilayah', $authProvinceCode);
        }

        $provinces = $provincesQuery->get(['kode_wilayah as code', 'provinsi as name']);

        $formatUserLabel = fn (User $u): string => $u->username ? "{$u->username} | {$u->name}" : $u->name;

        $scopeUserQuery = function (string $role) use ($isAdminNasional, $isAdminWilayah, $authUser, $authProvinceCode, $formatUserLabel): array {
            $query = User::query()
                ->where('role', $role)
                ->orderBy('username')
                ->orderBy('name');

            if ($isAdminNasional && $authUser?->plotting_id) {
                $query->where('plotting_id', $authUser->plotting_id);
            } elseif ($isAdminWilayah && $authProvinceCode) {
                $query->whereRaw("SUBSTRING(REPLACE(kode_wilayah, '.', ''), 1, 2) = ?", [$authProvinceCode]);
            }

            return $query
                ->get(['id', 'username', 'name', 'level', 'plotting_id', 'kode_wilayah'])
                ->map(fn (User $u): array => [
                    'id' => $u->id,
                    'code' => (string) $u->id,
                    'name' => $formatUserLabel($u),
                    'level' => $u->level,
                    'plotting_id' => $u->plotting_id ? (string) $u->plotting_id : null,
                    'province_code' => $u->kode_wilayah
                        ? substr(str_replace('.', '', (string) $u->kode_wilayah), 0, 2)
                        : null,
                ])
                ->values()
                ->all();
        };

        $daltuUsers = $scopeUserQuery('Koordinator dan Pengendali');
        $dalnisUsers = $scopeUserQuery('Pengawas Tim');
        $ketuaTimUsers = $scopeUserQuery('Ketua Tim');
        $auditorCandidates = $scopeUserQuery('Auditor');

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
            $payload = $this->buildTimPayload($validated);
            $payload['jumlah_anggota'] = count($validated['auditor_ids']);

            $tim = Tim::query()->create($payload);
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
        $newCount = count($validated['auditor_ids']);
        $needsSync = (int) $tim->jumlah_anggota !== $newCount;

        DB::transaction(function () use ($tim, $validated): void {
            $tim->update($this->buildTimPayload($validated));
            $tim->auditors()->sync($validated['auditor_ids']);
        });

        return response()->json([
            'message' => $needsSync
                ? 'Data tim berhasil diperbarui. Silakan klik tombol Sinkronkan untuk memperbarui jumlah anggota.'
                : 'Data tim berhasil diperbarui.',
            'data' => $tim->fresh(['auditors:id,name,role']),
        ]);
    }

    public function sync(Tim $tim): JsonResponse
    {
        $count = $tim->auditors()->count();
        $tim->update(['jumlah_anggota' => $count]);

        return response()->json([
            'message' => "Jumlah anggota tim {$tim->nama_tim} berhasil disinkronkan ({$count} orang).",
            'data' => $tim->fresh(['auditors:id,name,role']),
        ]);
    }

    public function syncAll(): JsonResponse
    {
        $tims = Tim::query()->withCount('auditors')->get();
        foreach ($tims as $tim) {
            if ((int) $tim->jumlah_anggota !== (int) $tim->auditors_count) {
                $tim->update(['jumlah_anggota' => $tim->auditors_count]);
            }
        }

        return response()->json([
            'message' => 'Seluruh data anggota tim berhasil disinkronkan.',
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
        $authUser = auth()->user();
        $isAdminNasional = $authUser?->role === 'Admin Nasional';
        $isAdminWilayah = $authUser?->role === 'Admin Wilayah';
        $authProvinceCode = ($isAdminWilayah && $authUser?->kode_wilayah)
            ? substr(str_replace('.', '', (string) $authUser->kode_wilayah), 0, 2)
            : null;

        $lingkup = match (true) {
            $isAdminNasional => 'Nasional',
            $isAdminWilayah => 'Wilayah',
            default => (string) $validated['lingkup'],
        };

        $plottingId = null;
        if ($lingkup === 'Nasional') {
            $plottingId = ($isAdminNasional && $authUser?->plotting_id)
                ? $authUser->plotting_id
                : ($validated['plotting_id'] ?? null);
        }

        $provinceCode = null;
        if ($lingkup === 'Wilayah') {
            $provinceCode = ($isAdminWilayah && $authProvinceCode)
                ? $authProvinceCode
                : ($validated['province_code'] ?? null);
        }

        return [
            'nama_tim' => $validated['nama_tim'],
            'lingkup' => $lingkup,
            'plotting_id' => $plottingId,
            'province_code' => $provinceCode,
            'daltu_id' => $validated['daltu_id'],
            'dalnis_id' => $validated['dalnis_id'],
            'ketua_tim_id' => $validated['ketua_tim_id'],
            'ketua_tim_2_id' => ! empty($validated['ketua_tim_2_id']) ? $validated['ketua_tim_2_id'] : null,
        ];
    }
}
