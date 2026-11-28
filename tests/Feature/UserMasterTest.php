<?php

namespace Tests\Feature;

use App\Models\Plotting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UserMasterTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_master_page_is_displayed_for_authenticated_user(): void
    {
        $admin = User::factory()->create();

        $response = $this->actingAs($admin)->get(route('master.user.index'));

        $response->assertOk();
        $response->assertSee('Data User');
        $response->assertSee('Tambah User');

        $tableConfig = $response->viewData('tableConfig');
        $this->assertNotContains('Admin Nasional', $tableConfig['roles']);
        $this->assertNotContains('Admin Wilayah', $tableConfig['roles']);
        $this->assertContains('Admin Dinas', $tableConfig['roles']);
        $this->assertContains('Admin Kecamatan', $tableConfig['roles']);
    }

    public function test_can_create_super_admin_auditor_and_auditee_with_level_plotting_and_ttd(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create();
        $acehId = DB::table('struktur_perangkat_daerah')->insertGetId([
            'kode_wilayah' => '11',
            'nama_unit' => 'ACEH',
            'level' => 1,
            'jenis_unit' => 'Provinsi',
            'provinsi' => 'ACEH',
        ]);

        DB::table('struktur_perangkat_daerah')->insert([
            'kode_wilayah' => '1101',
            'nama_unit' => 'KABUPATEN ACEH SELATAN',
            'level' => 2,
            'jenis_unit' => 'Kabupaten/Kota',
            'provinsi' => 'ACEH',
            'kabupaten_kota' => 'KABUPATEN ACEH SELATAN',
        ]);

        $plotting = Plotting::query()->create(['plot' => 'WIL I']);

        // 1. Create Super Admin (no level/plotting required)
        $superAdminResponse = $this->actingAs($admin)->postJson(route('master.user.store'), [
            'name' => 'Budi Super Admin',
            'username' => 'budisuper',
            'email' => 'budi.super@example.com',
            'phone' => '081111111111',
            'role' => 'Super Admin',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $superAdminResponse->assertCreated()
            ->assertJsonPath('data.role', 'Super Admin');

        // 2. Create Auditor at Level Kecamatan
        $auditorResponse = $this->actingAs($admin)->postJson(route('master.user.store'), [
            'name' => 'Andi Auditor',
            'username' => 'andiauditor',
            'email' => 'andi.auditor@example.com',
            'phone' => '082222222222',
            'role' => 'Auditor',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'level' => 'Kecamatan',
            'city_code' => '1101',
        ]);

        $auditorResponse->assertCreated()
            ->assertJsonPath('data.role', 'Auditor')
            ->assertJsonPath('data.level', 'Kecamatan')
            ->assertJsonPath('data.kode_wilayah', '1101');

        // 3. Create Auditee at Level Dinas (requires bidang, jabatan, and ttd file)
        $ttdFile = UploadedFile::fake()->image('ttd.png', 300, 120);

        $auditeeResponse = $this->actingAs($admin)->post(route('master.user.store'), [
            'name' => 'Siti Auditee',
            'username' => 'sitiauditee',
            'email' => 'siti.auditee@example.com',
            'phone' => '083333333333',
            'role' => 'Auditee',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'level' => 'Dinas',
            'bidang' => 'Manajemen Anggaran dan Keuangan',
            'jabatan' => 'Kepala Bagian Keuangan',
            'ttd' => $ttdFile,
        ], ['Accept' => 'application/json']);

        $auditeeResponse->assertCreated()
            ->assertJsonPath('data.role', 'Auditee')
            ->assertJsonPath('data.bidang', 'Manajemen Anggaran dan Keuangan')
            ->assertJsonPath('data.jabatan', 'Kepala Bagian Keuangan');

        $auditee = User::query()->where('username', 'sitiauditee')->firstOrFail();
        $this->assertNotNull($auditee->ttd_path);
        Storage::disk('public')->assertExists($auditee->ttd_path);
    }

    public function test_user_options_endpoint_returns_dinas_and_kecamatan_options(): void
    {
        $admin = User::factory()->create();

        $bupati = DB::table('struktur_perangkat_daerah')->insertGetId([
            'nama_unit' => 'Bupati Lampung Tengah',
            'level' => 0,
            'jenis_unit' => 'Kepala Daerah',
        ]);

        $dinas = DB::table('struktur_perangkat_daerah')->insertGetId([
            'parent_id' => $bupati,
            'nama_unit' => 'Dinas Kesehatan',
            'level' => 1,
            'jenis_unit' => 'Dinas',
        ]);

        $kecamatan = DB::table('struktur_perangkat_daerah')->insertGetId([
            'parent_id' => $bupati,
            'nama_unit' => 'Kecamatan Terbanggi Besar',
            'level' => 1,
            'jenis_unit' => 'Kecamatan',
        ]);

        $subUnit = DB::table('struktur_perangkat_daerah')->insertGetId([
            'parent_id' => $dinas,
            'nama_unit' => 'Bidang Pelayanan Kesehatan',
            'level' => 2,
            'jenis_unit' => 'Bidang',
        ]);

        // 1. Options: dinas
        $dinasResponse = $this->actingAs($admin)->getJson(route('master.user.index', ['options' => 'dinas']));
        $dinasResponse->assertOk()
            ->assertJsonFragment(['name' => 'Dinas Kesehatan', 'code' => (string) $dinas]);

        // 2. Options: kecamatans
        $kecamatanResponse = $this->actingAs($admin)->getJson(route('master.user.index', ['options' => 'kecamatans']));
        $kecamatanResponse->assertOk()
            ->assertJsonFragment(['name' => 'Kecamatan Terbanggi Besar', 'code' => (string) $kecamatan]);

        // 3. Options: sub_units
        $subUnitsResponse = $this->actingAs($admin)->getJson(route('master.user.index', [
            'options' => 'sub_units',
            'parent' => (string) $dinas,
        ]));
        $subUnitsResponse->assertOk()
            ->assertJsonFragment(['name' => 'Bidang Pelayanan Kesehatan', 'code' => (string) $subUnit]);

        // 4. Options: unit_hierarchy
        $hierarchyResponse = $this->actingAs($admin)->getJson(route('master.user.index', [
            'options' => 'unit_hierarchy',
            'parent' => (string) $subUnit,
        ]));
        $hierarchyResponse->assertOk()
            ->assertJsonPath('0.id', $dinas)
            ->assertJsonPath('1.id', $subUnit);
    }

    public function test_user_datatable_returns_unit_parent_and_unit_sub_properly(): void
    {
        $admin = User::factory()->create(['role' => 'Super Admin']);

        $kecamatanId = DB::table('struktur_perangkat_daerah')->insertGetId([
            'nama_unit' => 'Kecamatan Bandar Surabaya',
            'level' => 1,
            'jenis_unit' => 'Kecamatan',
        ]);

        $seksiId = DB::table('struktur_perangkat_daerah')->insertGetId([
            'parent_id' => $kecamatanId,
            'nama_unit' => 'Seksi Ketentraman dan Ketertiban Umum - Kec. Bandar Surabaya',
            'level' => 2,
            'jenis_unit' => 'Seksi',
        ]);

        $userKec = User::factory()->create([
            'role' => 'Auditee',
            'level' => 'Kecamatan',
            'struktur_perangkat_daerah_id' => $kecamatanId,
        ]);

        $userSeksi = User::factory()->create([
            'role' => 'Auditee',
            'level' => 'Kecamatan',
            'struktur_perangkat_daerah_id' => $seksiId,
        ]);

        $response = $this->actingAs($admin)->getJson(route('master.user.index'), ['X-Requested-With' => 'XMLHttpRequest']);
        $response->assertOk();

        $data = collect($response->json('data'));

        $kecRow = $data->firstWhere('id', $userKec->id);
        $this->assertNotNull($kecRow);
        $this->assertNull($kecRow['unit_parent']);
        $this->assertNull($kecRow['unit_sub']);
        $this->assertEquals('Kecamatan Bandar Surabaya', $kecRow['perangkat_daerah_name']);

        $seksiRow = $data->firstWhere('id', $userSeksi->id);
        $this->assertNotNull($seksiRow);
        $this->assertEquals('Kecamatan Bandar Surabaya', $seksiRow['unit_parent']);
        $this->assertEquals('Seksi Ketentraman dan Ketertiban Umum', $seksiRow['unit_sub']);
    }

    public function test_admin_dinas_and_admin_kecamatan_cannot_see_or_manage_each_others_scope(): void
    {
        $superAdmin = User::factory()->create(['role' => 'Super Admin', 'level' => null]);

        $dinasUnit = DB::table('struktur_perangkat_daerah')->insertGetId([
            'nama_unit' => 'Dinas Perhubungan',
            'level' => 1,
            'jenis_unit' => 'Dinas',
        ]);

        $kecamatanUnit = DB::table('struktur_perangkat_daerah')->insertGetId([
            'nama_unit' => 'Kecamatan Bandar Surabaya',
            'level' => 1,
            'jenis_unit' => 'Kecamatan',
        ]);

        $adminDinas = User::factory()->create([
            'name' => 'Admin Dinas User',
            'role' => 'Admin Dinas',
            'level' => 'Dinas',
            'struktur_perangkat_daerah_id' => $dinasUnit,
        ]);

        $adminKecamatan = User::factory()->create([
            'name' => 'Admin Kecamatan User',
            'role' => 'Admin Kecamatan',
            'level' => 'Kecamatan',
            'struktur_perangkat_daerah_id' => $kecamatanUnit,
        ]);

        $userDinas = User::factory()->create([
            'name' => 'Auditee Dinas',
            'role' => 'Auditee',
            'level' => 'Dinas',
            'struktur_perangkat_daerah_id' => $dinasUnit,
            'bidang' => 'Operasional',
        ]);

        $userKecamatan = User::factory()->create([
            'name' => 'Auditee Kecamatan',
            'role' => 'Auditee',
            'level' => 'Kecamatan',
            'struktur_perangkat_daerah_id' => $kecamatanUnit,
            'bidang' => 'Operasional',
        ]);

        // 1. Admin Dinas views user datatable
        $resDinas = $this->actingAs($adminDinas)->getJson(route('master.user.index'), ['X-Requested-With' => 'XMLHttpRequest']);
        $resDinas->assertOk();
        $dinasRows = collect($resDinas->json('data'));

        $this->assertTrue($dinasRows->contains('id', $adminDinas->id));
        $this->assertTrue($dinasRows->contains('id', $userDinas->id));
        $this->assertFalse($dinasRows->contains('id', $superAdmin->id));
        $this->assertFalse($dinasRows->contains('id', $adminKecamatan->id));
        $this->assertFalse($dinasRows->contains('id', $userKecamatan->id));

        // Admin Dinas options: kecamatans returns empty, dinas returns data
        $optKecForDinas = $this->actingAs($adminDinas)->getJson(route('master.user.index', ['options' => 'kecamatans']));
        $optKecForDinas->assertOk();
        $this->assertEmpty($optKecForDinas->json());

        $optDinasForDinas = $this->actingAs($adminDinas)->getJson(route('master.user.index', ['options' => 'dinas']));
        $optDinasForDinas->assertOk();
        $this->assertNotEmpty($optDinasForDinas->json());

        // Admin Dinas cannot update, delete, or reset password of Kecamatan user
        $this->actingAs($adminDinas)->deleteJson(route('master.user.destroy', $userKecamatan))->assertForbidden();
        $this->actingAs($adminDinas)->deleteJson(route('master.user.destroy', $adminKecamatan))->assertForbidden();
        $this->actingAs($adminDinas)->postJson(route('master.user.reset-password', $userKecamatan))->assertForbidden();
        $this->actingAs($adminDinas)->putJson(route('master.user.update', $userKecamatan), [
            'name' => 'Modified by Admin Dinas',
            'username' => $userKecamatan->username,
            'email' => $userKecamatan->email,
            'phone' => '081234567890',
            'role' => 'Auditee',
            'level' => 'Kecamatan',
        ])->assertForbidden();

        // Admin Dinas cannot create user with role Admin Kecamatan or level Kecamatan
        $this->actingAs($adminDinas)->postJson(route('master.user.store'), [
            'name' => 'Unauthorized Kec Admin',
            'username' => 'unauthkecadmin',
            'email' => 'unauthkec@example.com',
            'phone' => '081234567891',
            'role' => 'Admin Kecamatan',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertUnprocessable();

        // 2. Admin Kecamatan views user datatable
        $resKec = $this->actingAs($adminKecamatan)->getJson(route('master.user.index'), ['X-Requested-With' => 'XMLHttpRequest']);
        $resKec->assertOk();
        $kecRows = collect($resKec->json('data'));

        $this->assertTrue($kecRows->contains('id', $adminKecamatan->id));
        $this->assertTrue($kecRows->contains('id', $userKecamatan->id));
        $this->assertFalse($kecRows->contains('id', $superAdmin->id));
        $this->assertFalse($kecRows->contains('id', $adminDinas->id));
        $this->assertFalse($kecRows->contains('id', $userDinas->id));

        // Admin Kecamatan options: dinas returns empty, kecamatans returns data
        $optDinasForKec = $this->actingAs($adminKecamatan)->getJson(route('master.user.index', ['options' => 'dinas']));
        $optDinasForKec->assertOk();
        $this->assertEmpty($optDinasForKec->json());

        $optKecForKec = $this->actingAs($adminKecamatan)->getJson(route('master.user.index', ['options' => 'kecamatans']));
        $optKecForKec->assertOk();
        $this->assertNotEmpty($optKecForKec->json());

        // Admin Kecamatan cannot update, delete, or reset password of Dinas user
        $this->actingAs($adminKecamatan)->deleteJson(route('master.user.destroy', $userDinas))->assertForbidden();
        $this->actingAs($adminKecamatan)->deleteJson(route('master.user.destroy', $adminDinas))->assertForbidden();
        $this->actingAs($adminKecamatan)->postJson(route('master.user.reset-password', $userDinas))->assertForbidden();
        $this->actingAs($adminKecamatan)->putJson(route('master.user.update', $userDinas), [
            'name' => 'Modified by Admin Kecamatan',
            'username' => $userDinas->username,
            'email' => $userDinas->email,
            'phone' => '081234567890',
            'role' => 'Auditee',
            'level' => 'Dinas',
        ])->assertForbidden();

        // Admin Kecamatan cannot create user with role Admin Dinas or level Dinas
        $this->actingAs($adminKecamatan)->postJson(route('master.user.store'), [
            'name' => 'Unauthorized Dinas Admin',
            'username' => 'unauthdinasadmin',
            'email' => 'unauthdinas@example.com',
            'phone' => '081234567892',
            'role' => 'Admin Dinas',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertUnprocessable();

        // 3. Super Admin sees both
        $resSuper = $this->actingAs($superAdmin)->getJson(route('master.user.index'), ['X-Requested-With' => 'XMLHttpRequest']);
        $resSuper->assertOk();
        $superRows = collect($resSuper->json('data'));

        $this->assertTrue($superRows->contains('id', $superAdmin->id));
        $this->assertTrue($superRows->contains('id', $adminDinas->id));
        $this->assertTrue($superRows->contains('id', $userDinas->id));
        $this->assertTrue($superRows->contains('id', $adminKecamatan->id));
        $this->assertTrue($superRows->contains('id', $userKecamatan->id));
    }
}
