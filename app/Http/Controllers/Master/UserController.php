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
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
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

        $authUser = $request->user();

        $plottingsQuery = Plotting::query()
            ->with('provinces:id,kode_wilayah,provinsi')
            ->orderBy('plot');

        if ($authUser?->role === 'Admin Nasional' && $authUser->plotting_id) {
            $plottingsQuery->whereKey($authUser->plotting_id);
        }

        $plottings = $plottingsQuery
            ->get()
            ->map(fn (Plotting $plotting): array => [
                'id' => $plotting->id,
                'code' => (string) $plotting->id,
                'name' => $plotting->plot,
                'provinces' => $plotting->provinces->map(fn (object $p): array => [
                    'code' => (string) $p->kode_wilayah,
                    'name' => (string) $p->provinsi,
                ])->values()->all(),
            ])
            ->values();

        $provincesQuery = DB::table('wilayah')
            ->whereRaw('CHAR_LENGTH(kode_wilayah) = 2')
            ->whereNull('deleted_at')
            ->orderBy('kode_wilayah');

        if ($authUser?->role === 'Admin Wilayah' && $authUser->kode_wilayah) {
            $authProvinceCode = substr(str_replace('.', '', (string) $authUser->kode_wilayah), 0, 2);
            if ($authProvinceCode !== '') {
                $provincesQuery->where('kode_wilayah', $authProvinceCode);
            }
        }

        $provinces = $provincesQuery->get(['kode_wilayah as code', 'provinsi as name']);

        $tableConfig = $dataTable->config($plottings, $provinces);

        return view('master.user.index', compact('tableConfig'));
    }

    public function store(UserRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $payload = $this->buildUserPayload($request, $validated);

        if ($payload['role'] === 'Auditee' && ($validated['bidang'] ?? null) === 'SEMUA_BIDANG') {
            $baseName = trim((string) $payload['name']);
            $baseUsername = trim((string) $payload['username']);
            $baseEmail = trim((string) $payload['email']);
            [$emailLocal, $emailDomain] = explode('@', $baseEmail, 2);
            $hashedPassword = Hash::make((string) $validated['password']);

            $createdUsers = DB::transaction(function () use ($payload, $baseName, $baseUsername, $emailLocal, $emailDomain, $hashedPassword): array {
                $users = [];
                foreach (User::BIDANGS as $bidang) {
                    $slug = Str::slug($bidang, '_');
                    $rowPayload = $payload;
                    $rowPayload['name'] = "{$baseName} {$bidang}";
                    $rowPayload['username'] = "{$baseUsername}_{$slug}";
                    $rowPayload['email'] = "{$emailLocal}_{$slug}@{$emailDomain}";
                    $rowPayload['bidang'] = $bidang;
                    $rowPayload['password'] = $hashedPassword;

                    $users[] = User::query()->create($rowPayload);
                }

                return $users;
            });

            return response()->json([
                'message' => '4 akun Auditee untuk semua bidang berhasil ditambahkan.',
                'data' => $createdUsers,
            ], 201);
        }

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
        if (Auth::user()?->role !== 'Super Admin' || $request->session()->has('impersonator_id')) {
            return response()->json([
                'message' => 'Hanya Super Admin yang dapat melakukan impersonate (tidak dapat impersonate saat sedang dalam sesi impersonate).',
            ], 403);
        }

        if ($user->id === Auth::id()) {
            return response()->json([
                'message' => 'Anda sedang login menggunakan akun ini.',
            ], 422);
        }

        $request->session()->put('impersonator_id', Auth::id());

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
        $authUser = $request->user();
        $isAdminNasional = $authUser?->role === 'Admin Nasional';
        $isAdminWilayah = $authUser?->role === 'Admin Wilayah';
        $authProvinceCode = ($isAdminWilayah && $authUser?->kode_wilayah)
            ? substr(str_replace('.', '', (string) $authUser->kode_wilayah), 0, 2)
            : null;

        $role = (string) $validated['role'];
        $isAuditTeamRole = in_array($role, [
            'Auditor',
            'Ketua Tim',
            'Koordinator dan Pengendali',
            'Pengawas Tim',
        ], true);
        $needsScope = in_array($role, [
            'Admin Nasional',
            'Admin Wilayah',
            'Auditor',
            'Auditee',
            'Ketua Tim',
            'Koordinator dan Pengendali',
            'Pengawas Tim',
        ], true);
        $isAuditee = $role === 'Auditee';
        $level = $needsScope
            ? match (true) {
                $isAdminNasional || $role === 'Admin Nasional' => 'Nasional',
                $isAdminWilayah && $isAuditTeamRole => 'Wilayah',
                default => (string) ($validated['level'] ?? ''),
            }
        : null;

        $provinceCode = ($isAdminWilayah && $authProvinceCode)
            ? $authProvinceCode
            : ($validated['province_code'] ?? null);

        $kodeWilayah = match (true) {
            ! $needsScope || $level === 'Nasional' => null,
            in_array($level, ['Wilayah', 'Provinsi'], true) => $provinceCode,
            $level === 'Kabupaten' => $validated['city_code'] ?? null,
            $level === 'Kecamatan' => $validated['district_code'] ?? null,
            $level === 'Desa' => $validated['village_code'] ?? null,
            default => null,
        };

        $plottingId = null;
        if ($needsScope && $level === 'Nasional') {
            $plottingId = ($isAdminNasional && $authUser?->plotting_id)
                ? $authUser->plotting_id
                : ($validated['plotting_id'] ?? null);
        }

        $payload = [
            'name' => $validated['name'],
            'username' => $validated['username'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'role' => $role,
            'level' => $level,
            'plotting_id' => $plottingId,
            'kode_wilayah' => $kodeWilayah,
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
        $type = (string) $request->query('options');
        $parent = (string) $request->query('parent');

        $items = match ($type) {
            'cities' => DB::table('wilayah')
                ->whereRaw('CHAR_LENGTH(kode_wilayah) = 4')
                ->where('kode_wilayah', 'like', $parent.'%')
                ->whereNull('deleted_at')
                ->orderBy('kode_wilayah')
                ->get(['kode_wilayah as code', 'kabupaten_kota as name']),
            'districts' => DB::table('wilayah')
                ->whereRaw('CHAR_LENGTH(kode_wilayah) = 6')
                ->where('kode_wilayah', 'like', $parent.'%')
                ->whereNull('deleted_at')
                ->orderBy('kode_wilayah')
                ->get(['kode_wilayah as code', 'kecamatan as name']),
            'villages' => DB::table('wilayah')
                ->whereRaw('CHAR_LENGTH(kode_wilayah) = 10')
                ->where('kode_wilayah', 'like', $parent.'%')
                ->whereNull('deleted_at')
                ->orderBy('kode_wilayah')
                ->get(['kode_wilayah as code', 'desa_kelurahan as name']),
            default => collect(),
        };

        return response()->json($items);
    }
}
