<?php

namespace App\Http\Controllers\Master;

use App\DataTables\Master\UserDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Master\UserRequest;
use App\Models\Plotting;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request, UserDataTable $dataTable): View|JsonResponse
    {
        if ($request->filled('options')) {
            return $this->options($request);
        }

        if ($request->ajax() || $request->wantsJson()) {
            return $dataTable->toJson($request);
        }

        $prefix = config('laravolt.indonesia.table_prefix', 'indonesia_');

        $plottings = Plotting::query()
            ->with('provinces:id,code,name')
            ->orderBy('plot')
            ->get()
            ->map(fn (Plotting $plotting): array => [
                'id' => $plotting->id,
                'code' => (string) $plotting->id,
                'name' => $plotting->plot,
                'provinces' => $plotting->provinces->map(fn (object $p): array => [
                    'code' => (string) $p->code,
                    'name' => (string) $p->name,
                ])->values()->all(),
            ]);

        $provinces = DB::table($prefix.'provinces')
            ->orderBy('code')
            ->get(['code', 'name']);

        $tableConfig = $dataTable->config($plottings, $provinces);

        return view('master.user.index', compact('tableConfig'));
    }

    public function store(UserRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $payload = $this->buildUserPayload($request, $validated);

        $user = User::query()->create($payload);

        return response()->json([
            'message' => 'Data user berhasil ditambahkan.',
            'data' => $user,
        ], 201);
    }

    public function update(UserRequest $request, User $user): JsonResponse
    {
        $validated = $request->validated();
        $payload = $this->buildUserPayload($request, $validated, $user);

        $user->update($payload);

        return response()->json([
            'message' => 'Data user berhasil diperbarui.',
            'data' => $user->fresh(),
        ]);
    }

    public function destroy(User $user): JsonResponse
    {
        if ($user->id === auth()->id()) {
            return response()->json([
                'message' => 'Anda tidak dapat menghapus akun yang sedang digunakan.',
            ], 422);
        }

        if ($user->ttd_path) {
            Storage::disk('public')->delete($user->ttd_path);
        }

        $user->delete();

        return response()->json([
            'message' => 'Data user berhasil dihapus.',
        ]);
    }

    public function resetPassword(User $user): JsonResponse
    {
        $user->update([
            'password' => 'Password123',
        ]);

        return response()->json([
            'message' => "Password user {$user->name} berhasil direset menjadi Password123.",
        ]);
    }

    public function impersonate(Request $request, User $user): JsonResponse
    {
        if ($user->id === Auth::id()) {
            return response()->json([
                'message' => 'Anda sedang login menggunakan akun ini.',
            ], 422);
        }

        if (! $request->session()->has('impersonator_id')) {
            $request->session()->put('impersonator_id', Auth::id());
        }

        Auth::login($user);

        return response()->json([
            'message' => "Berhasil masuk sebagai {$user->name}.",
            'redirect' => route('dashboard'),
        ]);
    }

    public function leaveImpersonate(Request $request): RedirectResponse
    {
        $impersonatorId = $request->session()->pull('impersonator_id');

        if ($impersonatorId) {
            $originalUser = User::query()->find($impersonatorId);
            if ($originalUser) {
                Auth::login($originalUser);
            }
        }

        return redirect()->route('master.user.index');
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    protected function buildUserPayload(UserRequest $request, array $validated, ?User $user = null): array
    {
        $role = (string) $validated['role'];
        $needsScope = in_array($role, [
            'Auditor',
            'Auditee',
            'Ketua Tim',
            'Koordinator dan Pengendali',
            'Pengawas Tim',
        ], true);
        $isAuditee = $role === 'Auditee';
        $level = $needsScope ? (string) ($validated['level'] ?? '') : null;

        $payload = [
            'name' => $validated['name'],
            'username' => $validated['username'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'role' => $role,
            'level' => $level,
            'plotting_id' => $needsScope && $level === 'Nasional'
                ? ($validated['plotting_id'] ?? null)
                : null,
            'province_code' => $needsScope && in_array($level, ['Provinsi', 'Kabupaten', 'Kecamatan', 'Desa'], true)
                ? ($validated['province_code'] ?? null)
                : null,
            'city_code' => $needsScope && in_array($level, ['Kabupaten', 'Kecamatan', 'Desa'], true)
                ? ($validated['city_code'] ?? null)
                : null,
            'district_code' => $needsScope && in_array($level, ['Kecamatan', 'Desa'], true)
                ? ($validated['district_code'] ?? null)
                : null,
            'village_code' => $needsScope && $level === 'Desa'
                ? ($validated['village_code'] ?? null)
                : null,
            'bidang' => $isAuditee ? ($validated['bidang'] ?? null) : null,
            'jabatan' => $validated['jabatan'] ?? null,
        ];

        if (! empty($validated['password'])) {
            $payload['password'] = $validated['password'];
        }

        if ($request->hasFile('ttd')) {
            if ($user?->ttd_path) {
                Storage::disk('public')->delete($user->ttd_path);
            }
            $payload['ttd_path'] = $request->file('ttd')->store('ttd', 'public');
        }

        return $payload;
    }

    protected function options(Request $request): JsonResponse
    {
        $prefix = config('laravolt.indonesia.table_prefix', 'indonesia_');
        $type = (string) $request->query('options');
        $parent = (string) $request->query('parent');

        $items = match ($type) {
            'cities' => DB::table($prefix.'cities')
                ->where('province_code', $parent)
                ->orderBy('code')
                ->get(['code', 'name']),
            'districts' => DB::table($prefix.'districts')
                ->where('city_code', $parent)
                ->orderBy('code')
                ->get(['code', 'name']),
            'villages' => DB::table($prefix.'villages')
                ->where('district_code', $parent)
                ->orderBy('code')
                ->get(['code', 'name']),
            default => collect(),
        };

        return response()->json($items);
    }
}
