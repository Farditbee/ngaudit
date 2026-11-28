<?php

namespace Tests\Feature;

use App\Models\Plotting;
use App\Models\Tim;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TimMasterTest extends TestCase
{
    use RefreshDatabase;

    public function test_tim_master_page_is_displayed_for_authenticated_user(): void
    {
        $admin = User::factory()->create();

        $response = $this->actingAs($admin)->get(route('master.tim.index'));

        $response->assertOk();
        $response->assertSee('Data Tim');
        $response->assertSee('Tambah Tim');
    }

    public function test_can_create_update_and_delete_tim_with_role_validations(): void
    {
        $admin = User::factory()->create();
        DB::table('struktur_perangkat_daerah')->insert([
            'kode_wilayah' => '32',
            'nama_unit' => 'JAWA BARAT',
            'level' => 1,
            'jenis_unit' => 'Provinsi',
            'provinsi' => 'JAWA BARAT',
        ]);

        $plotting = Plotting::query()->create(['plot' => 'WIL I']);

        $daltu = User::factory()->create(['role' => 'Koordinator dan Pengendali', 'name' => 'Irwan Daltu']);
        $dalnis = User::factory()->create(['role' => 'Pengawas Tim', 'name' => 'Rahmat Dalnis']);
        $ketua1 = User::factory()->create(['role' => 'Ketua Tim', 'name' => 'Dedi Ketua 1']);
        $ketua2 = User::factory()->create(['role' => 'Ketua Tim', 'name' => 'Eko Ketua 2']);
        $auditor1 = User::factory()->create(['role' => 'Auditor', 'name' => 'Budi Auditor 1']);
        $auditor2 = User::factory()->create(['role' => 'Auditor', 'name' => 'Andi Auditor 2']);

        // 1. Validasi gagal jika Ketua Tim dimasukkan ke dalam Anggota (Auditor)
        $invalidResponse = $this->actingAs($admin)->postJson(route('master.tim.store'), [
            'nama_tim' => 'Tim Audit Invalid',
            'lingkup' => 'Nasional',
            'plotting_id' => $plotting->id,
            'daltu_id' => $daltu->id,
            'dalnis_id' => $dalnis->id,
            'ketua_tim_id' => $ketua1->id,
            'ketua_tim_2_id' => $ketua2->id,
            'auditor_ids' => [$ketua1->id, $auditor1->id],
        ]);

        $invalidResponse->assertUnprocessable()
            ->assertJsonValidationErrors(['auditor_ids.0']);

        // 2. Buat Tim Lingkup Nasional dengan WIL I, Ketua Tim 1, Ketua Tim 2, dan Anggota (2 Auditor)
        $storeResponse = $this->actingAs($admin)->postJson(route('master.tim.store'), [
            'nama_tim' => 'Tim Audit Nasional 1',
            'lingkup' => 'Nasional',
            'plotting_id' => $plotting->id,
            'daltu_id' => $daltu->id,
            'dalnis_id' => $dalnis->id,
            'ketua_tim_id' => $ketua1->id,
            'ketua_tim_2_id' => $ketua2->id,
            'auditor_ids' => [$auditor1->id, $auditor2->id],
        ]);

        $storeResponse->assertCreated()
            ->assertJsonPath('data.nama_tim', 'Tim Audit Nasional 1')
            ->assertJsonPath('data.lingkup', 'Nasional')
            ->assertJsonPath('data.plotting_id', $plotting->id)
            ->assertJsonCount(2, 'data.auditors');

        $tim = Tim::query()->firstOrFail();

        // 3. Update Tim menjadi Lingkup Wilayah (Provinsi Jawa Barat) dan tanpa Ketua Tim 2
        $updateResponse = $this->actingAs($admin)->putJson(route('master.tim.update', $tim), [
            'nama_tim' => 'Tim Audit Jabar',
            'lingkup' => 'Wilayah',
            'province_code' => '32',
            'daltu_id' => $daltu->id,
            'dalnis_id' => $dalnis->id,
            'ketua_tim_id' => $ketua1->id,
            'ketua_tim_2_id' => null,
            'auditor_ids' => [$auditor1->id],
        ]);

        $updateResponse->assertOk()
            ->assertJsonPath('data.nama_tim', 'Tim Audit Jabar')
            ->assertJsonPath('data.lingkup', 'Wilayah')
            ->assertJsonPath('data.plotting_id', null)
            ->assertJsonPath('data.province_code', '32')
            ->assertJsonPath('data.ketua_tim_2_id', null)
            ->assertJsonCount(1, 'data.auditors');

        // 4. Delete Tim
        $deleteResponse = $this->actingAs($admin)->deleteJson(route('master.tim.destroy', $tim));

        $deleteResponse->assertOk();
        $this->assertSoftDeleted('tims', ['id' => $tim->id]);
    }

    public function test_can_create_tim_without_lingkup_or_plotting(): void
    {
        $admin = User::factory()->create();

        $daltu = User::factory()->create(['role' => 'Koordinator dan Pengendali', 'name' => 'Irwan Daltu']);
        $dalnis = User::factory()->create(['role' => 'Pengawas Tim', 'name' => 'Rahmat Dalnis']);
        $ketua = User::factory()->create(['role' => 'Ketua Tim', 'name' => 'Dedi Ketua']);
        $auditor = User::factory()->create(['role' => 'Auditor', 'name' => 'Budi Auditor']);

        $response = $this->actingAs($admin)->postJson(route('master.tim.store'), [
            'nama_tim' => 'Tim Pemeriksa Inspektorat 1',
            'daltu_id' => $daltu->id,
            'dalnis_id' => $dalnis->id,
            'ketua_tim_id' => $ketua->id,
            'auditor_ids' => [$auditor->id],
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.nama_tim', 'Tim Pemeriksa Inspektorat 1')
            ->assertJsonPath('data.lingkup', 'Dinas')
            ->assertJsonPath('data.plotting_id', null);
    }

    public function test_tim_config_locks_lingkup_for_admin_dinas_and_admin_kecamatan(): void
    {
        $adminDinas = User::factory()->create(['role' => 'Admin Dinas']);
        $adminKecamatan = User::factory()->create(['role' => 'Admin Kecamatan']);
        $superAdmin = User::factory()->create(['role' => 'Super Admin']);

        // 1. Admin Dinas -> locked to Dinas
        $dinasResp = $this->actingAs($adminDinas)->get(route('master.tim.index'));
        $dinasResp->assertOk();
        $dinasConfig = $dinasResp->viewData('tableConfig');
        $this->assertEquals(['Dinas'], $dinasConfig['lingkups']);

        // 2. Admin Kecamatan -> locked to Kecamatan
        $kecResp = $this->actingAs($adminKecamatan)->get(route('master.tim.index'));
        $kecResp->assertOk();
        $kecConfig = $kecResp->viewData('tableConfig');
        $this->assertEquals(['Kecamatan'], $kecConfig['lingkups']);

        // 3. Super Admin -> both Dinas & Kecamatan
        $saResp = $this->actingAs($superAdmin)->get(route('master.tim.index'));
        $saResp->assertOk();
        $saConfig = $saResp->viewData('tableConfig');
        $this->assertEquals(['Dinas', 'Kecamatan'], $saConfig['lingkups']);
    }
}
