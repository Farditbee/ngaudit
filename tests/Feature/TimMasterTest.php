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
        $prefix = config('laravolt.indonesia.table_prefix', 'indonesia_');

        DB::table($prefix.'provinces')->insert([
            'code' => '32',
            'name' => 'JAWA BARAT',
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
        $this->assertDatabaseMissing('tims', ['id' => $tim->id]);
        $this->assertDatabaseMissing('tim_auditor', ['tim_id' => $tim->id]);
    }
}
