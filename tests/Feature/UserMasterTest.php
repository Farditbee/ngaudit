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
    }

    public function test_can_create_super_admin_auditor_and_auditee_with_level_plotting_and_ttd(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create();
        $prefix = config('laravolt.indonesia.table_prefix', 'indonesia_');

        $acehId = DB::table($prefix.'provinces')->insertGetId([
            'code' => '11',
            'name' => 'ACEH',
        ]);

        DB::table($prefix.'cities')->insert([
            'code' => '1101',
            'province_code' => '11',
            'name' => 'KABUPATEN ACEH SELATAN',
        ]);

        $plotting = Plotting::query()->create(['plot' => 'WIL I']);
        $plotting->provinces()->sync([$acehId]);

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

        // 2. Create Auditor at Level Kabupaten (requires plotting_id, province_code, city_code)
        $auditorResponse = $this->actingAs($admin)->postJson(route('master.user.store'), [
            'name' => 'Andi Auditor',
            'username' => 'andiauditor',
            'email' => 'andi.auditor@example.com',
            'phone' => '082222222222',
            'role' => 'Auditor',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'level' => 'Kabupaten',
            'plotting_id' => $plotting->id,
            'province_code' => '11',
            'city_code' => '1101',
        ]);

        $auditorResponse->assertCreated()
            ->assertJsonPath('data.role', 'Auditor')
            ->assertJsonPath('data.level', 'Kabupaten')
            ->assertJsonPath('data.city_code', '1101');

        // 3. Create Auditee at Level Provinsi (requires bidang, jabatan, and ttd file)
        $ttdFile = UploadedFile::fake()->image('ttd.png', 300, 120);

        $auditeeResponse = $this->actingAs($admin)->post(route('master.user.store'), [
            'name' => 'Siti Auditee',
            'username' => 'sitiauditee',
            'email' => 'siti.auditee@example.com',
            'phone' => '083333333333',
            'role' => 'Auditee',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'level' => 'Provinsi',
            'plotting_id' => $plotting->id,
            'province_code' => '11',
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
}
