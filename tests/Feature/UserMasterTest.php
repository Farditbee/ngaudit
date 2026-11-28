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
}
