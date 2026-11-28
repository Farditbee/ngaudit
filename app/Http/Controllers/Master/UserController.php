<?php

namespace App\Http\Controllers\Master;

use App\DataTables\Master\UserDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Master\UserRequest;
use App\Models\StrukturPerangkatDaerah;
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

        $plottings = collect();
        $provinces = collect();

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
        $isAdminNasional = in_array($authUser?->role, ['Admin Nasional', 'Admin Dinas'], true);
        $isAdminWilayah = in_array($authUser?->role, ['Admin Wilayah', 'Admin Kecamatan'], true);
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
            'Admin Dinas',
            'Admin Kecamatan',
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
                $isAdminNasional || in_array($role, ['Admin Nasional', 'Admin Dinas'], true) => 'Dinas',
                $isAdminWilayah && $isAuditTeamRole => 'Kecamatan',
                default => (string) ($validated['level'] ?? ''),
            }
        : null;

        $strukturPerangkatDaerahId = $validated['struktur_perangkat_daerah_id']
            ?? $validated['unit_level_3']
            ?? $validated['unit_level_2']
            ?? $validated['unit_level_1']
            ?? null;

        $kodeWilayah = $validated['kode_wilayah']
            ?? $validated['city_code']
            ?? $validated['province_code']
            ?? null;

        if ($strukturPerangkatDaerahId && ! $kodeWilayah) {
            $unit = StrukturPerangkatDaerah::find($strukturPerangkatDaerahId);
            $kodeWilayah = $unit?->kode_wilayah ?? $unit?->kode_unit;
        }

        $payload = [
            'name' => $validated['name'],
            'username' => $validated['username'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'role' => $role,
            'level' => $level,
            'struktur_perangkat_daerah_id' => $strukturPerangkatDaerahId,
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

        $targetWilayahTable = DB::table('struktur_perangkat_daerah')->whereNotNull('kode_wilayah')->exists()
            ? 'struktur_perangkat_daerah'
            : 'wilayah';

        $items = match ($type) {
            'dinas' => StrukturPerangkatDaerah::dinas()
                ->orderBy('nama_unit')
                ->get()
                ->map(fn ($u): array => [
                    'id' => $u->id,
                    'code' => (string) $u->id,
                    'name' => $u->nama_unit,
                    'pimpinan' => $u->pimpinan,
                    'jenis_unit' => $u->jenis_unit,
                ]),
            'kecamatans' => StrukturPerangkatDaerah::kecamatan()
                ->orderBy('nama_unit')
                ->get()
                ->map(fn ($u): array => [
                    'id' => $u->id,
                    'code' => (string) $u->id,
                    'name' => $u->nama_unit,
                    'pimpinan' => $u->pimpinan,
                    'jenis_unit' => $u->jenis_unit,
                ]),
            'sub_units' => StrukturPerangkatDaerah::query()
                ->where(function ($q) use ($parent): void {
                    $q->where('parent_id', $parent)
                        ->orWhere('parent_id', function ($sub) use ($parent): void {
                            $sub->select('id')->from('struktur_perangkat_daerah')->where('kode_unit', $parent)->limit(1);
                        });
                })
                ->orderBy('nama_unit')
                ->get()
                ->map(fn ($u): array => [
                    'id' => $u->id,
                    'code' => (string) $u->id,
                    'name' => $u->nama_unit,
                    'pimpinan' => $u->pimpinan,
                    'jenis_unit' => $u->jenis_unit,
                ]),
            'unit_hierarchy' => $this->getUnitHierarchy($parent),
            'cities', 'districts', 'villages' => collect(),
            default => collect(),
        };

        return response()->json($items);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function getUnitHierarchy(string|int $unitId): array
    {
        $unit = StrukturPerangkatDaerah::find($unitId);
        if (! $unit) {
            return [];
        }

        $chain = [];
        $curr = $unit;
        while ($curr) {
            array_unshift($chain, [
                'id' => $curr->id,
                'code' => (string) $curr->id,
                'name' => $curr->nama_unit,
                'parent_id' => $curr->parent_id,
                'level' => $curr->level,
                'jenis_unit' => $curr->jenis_unit,
            ]);
            $curr = $curr->parent;
        }

        return array_values(array_filter($chain, fn ($item): bool => ($item['level'] ?? 0) >= 1));
    }
}
