<?php

namespace Tests\Feature;

use App\Models\Kriteria;
use App\Models\User;
use Database\Seeders\KriteriaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KriteriaMasterTest extends TestCase
{
    use RefreshDatabase;

    public function test_kriteria_page_is_displayed_for_authenticated_user(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('master.kriteria.index'))
            ->assertOk()
            ->assertSee('Data Kriteria')
            ->assertSee('Tambah Kriteria');
    }

    public function test_kriteria_seeder_populates_boolean_bidang_flags(): void
    {
        $this->seed(KriteriaSeeder::class);

        $this->assertGreaterThanOrEqual(20, Kriteria::query()->count());
        $this->assertGreaterThan(0, Kriteria::query()->where('is_opsnal', true)->count());
        $this->assertGreaterThan(0, Kriteria::query()->where('is_logistik', true)->count());
        $this->assertGreaterThan(0, Kriteria::query()->where('is_sdm', true)->count());
        $this->assertGreaterThan(0, Kriteria::query()->where('is_garkeu', true)->count());

        $count = Kriteria::query()->count();
        $this->seed(KriteriaSeeder::class);
        $this->assertSame($count, Kriteria::query()->count());
    }

    public function test_user_can_create_filter_update_and_delete_kriteria(): void
    {
        $user = User::factory()->create();

        $store = $this->actingAs($user)->postJson(route('master.kriteria.store'), [
            'nama' => 'Peraturan Uji Opsnal & Garkeu',
            'is_opsnal' => true,
            'is_logistik' => false,
            'is_sdm' => false,
            'is_garkeu' => true,
        ])->assertCreated();

        $id = $store->json('data.id');

        $this->assertDatabaseHas('kriterias', [
            'id' => $id,
            'nama' => 'Peraturan Uji Opsnal & Garkeu',
            'is_opsnal' => 1,
            'is_logistik' => 0,
            'is_sdm' => 0,
            'is_garkeu' => 1,
        ]);

        // Filter datatable berdasarkan bidang
        $this->actingAs($user)->getJson(route('master.kriteria.index', ['bidang' => 'is_opsnal']))
            ->assertOk()
            ->assertJsonPath('recordsFiltered', 1);

        $this->actingAs($user)->getJson(route('master.kriteria.index', ['bidang' => 'is_sdm']))
            ->assertOk()
            ->assertJsonPath('recordsFiltered', 0);

        // Update checkbox: uncheck opsnal (0), check sdm (1)
        $this->actingAs($user)->putJson(route('master.kriteria.update', $id), [
            'nama' => 'Peraturan Uji SDM',
            'is_opsnal' => false,
            'is_logistik' => false,
            'is_sdm' => true,
            'is_garkeu' => false,
        ])->assertOk();

        $this->assertDatabaseHas('kriterias', [
            'id' => $id,
            'nama' => 'Peraturan Uji SDM',
            'is_opsnal' => 0,
            'is_logistik' => 0,
            'is_sdm' => 1,
            'is_garkeu' => 0,
        ]);

        $this->actingAs($user)->deleteJson(route('master.kriteria.destroy', $id))
            ->assertOk();

        $this->assertSoftDeleted('kriterias', ['id' => $id]);
    }
}
