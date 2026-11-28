<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class WilayahTest extends TestCase
{
    use RefreshDatabase;

    public function test_wilayah_page_is_displayed_for_authenticated_user(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('master.wilayah.index'));

        $response->assertOk();
        $response->assertSee('Data Wilayah');
        $response->assertSee('Tambah Wilayah');
    }

    public function test_wilayah_datatable_returns_hierarchical_json_data(): void
    {
        $user = User::factory()->create();

        DB::table('struktur_perangkat_daerah')->insert([
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

        DB::table('struktur_perangkat_daerah')->insert([
            'kode_wilayah' => '110101',
            'nama_unit' => 'BAKONGAN',
            'level' => 3,
            'jenis_unit' => 'Kecamatan',
            'provinsi' => 'ACEH',
            'kabupaten_kota' => 'KABUPATEN ACEH SELATAN',
            'kecamatan' => 'BAKONGAN',
        ]);

        DB::table('struktur_perangkat_daerah')->insert([
            'kode_wilayah' => '1101012001',
            'nama_unit' => 'KEUDE BAKONGAN',
            'level' => 4,
            'jenis_unit' => 'Desa/Kelurahan',
            'provinsi' => 'ACEH',
            'kabupaten_kota' => 'KABUPATEN ACEH SELATAN',
            'kecamatan' => 'BAKONGAN',
            'desa_kelurahan' => 'KEUDE BAKONGAN',
        ]);

        $response = $this->actingAs($user)->getJson(route('master.wilayah.index', [
            'draw' => 1,
            'start' => 0,
            'length' => 10,
            'order' => [['column' => 1, 'dir' => 'asc']],
            'columns' => [
                ['data' => 'DT_RowIndex', 'name' => 'DT_RowIndex', 'searchable' => 'false', 'orderable' => 'false'],
                ['data' => 'code', 'name' => 'code', 'searchable' => 'true', 'orderable' => 'true'],
                ['data' => 'province_name', 'name' => 'province_name', 'searchable' => 'true', 'orderable' => 'true'],
                ['data' => 'city_name', 'name' => 'city_name', 'searchable' => 'true', 'orderable' => 'true'],
                ['data' => 'district_name', 'name' => 'district_name', 'searchable' => 'true', 'orderable' => 'true'],
                ['data' => 'village_name', 'name' => 'village_name', 'searchable' => 'true', 'orderable' => 'true'],
                ['data' => 'actions', 'name' => 'actions', 'searchable' => 'false', 'orderable' => 'false'],
            ],
        ]));

        $response->assertOk()
            ->assertJsonPath('recordsTotal', 4)
            ->assertJsonPath('data.0.code', '11')
            ->assertJsonPath('data.0.level', 'province')
            ->assertJsonPath('data.0.province_name', 'ACEH')
            ->assertJsonPath('data.0.city_name', '')
            ->assertJsonPath('data.1.code', '11.01')
            ->assertJsonPath('data.1.level', 'city')
            ->assertJsonPath('data.1.city_name', 'KABUPATEN ACEH SELATAN')
            ->assertJsonPath('data.2.code', '11.01.01')
            ->assertJsonPath('data.2.level', 'district')
            ->assertJsonPath('data.2.district_name', 'BAKONGAN')
            ->assertJsonPath('data.3.code', '11.01.01.2001')
            ->assertJsonPath('data.3.level', 'village')
            ->assertJsonPath('data.3.village_name', 'KEUDE BAKONGAN');

        $citiesResponse = $this->actingAs($user)->getJson(route('master.wilayah.index', [
            'options' => 'cities',
            'parent' => '11',
        ]));

        $citiesResponse->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.code', '1101');

        $filteredResponse = $this->actingAs($user)->getJson(route('master.wilayah.index', [
            'draw' => 2,
            'start' => 0,
            'length' => 10,
            'province_code' => '11',
            'city_code' => '1101',
            'district_code' => '110101',
            'order' => [['column' => 1, 'dir' => 'asc']],
            'columns' => [
                ['data' => 'DT_RowIndex', 'name' => 'DT_RowIndex', 'searchable' => 'false', 'orderable' => 'false'],
                ['data' => 'code', 'name' => 'code', 'searchable' => 'true', 'orderable' => 'true'],
                ['data' => 'province_name', 'name' => 'province_name', 'searchable' => 'true', 'orderable' => 'true'],
                ['data' => 'city_name', 'name' => 'city_name', 'searchable' => 'true', 'orderable' => 'true'],
                ['data' => 'district_name', 'name' => 'district_name', 'searchable' => 'true', 'orderable' => 'true'],
                ['data' => 'village_name', 'name' => 'village_name', 'searchable' => 'true', 'orderable' => 'true'],
                ['data' => 'actions', 'name' => 'actions', 'searchable' => 'false', 'orderable' => 'false'],
            ],
        ]));

        $filteredResponse->assertOk()
            ->assertJsonPath('recordsFiltered', 2)
            ->assertJsonPath('data.0.code', '11.01.01')
            ->assertJsonPath('data.1.code', '11.01.01.2001');
    }

    public function test_wilayah_can_be_created_updated_and_deleted_with_hierarchical_code_validation(): void
    {
        $user = User::factory()->create();

        DB::table('struktur_perangkat_daerah')->insert([
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

        DB::table('struktur_perangkat_daerah')->insert([
            'kode_wilayah' => '110101',
            'nama_unit' => 'BAKONGAN',
            'level' => 3,
            'jenis_unit' => 'Kecamatan',
            'provinsi' => 'ACEH',
            'kabupaten_kota' => 'KABUPATEN ACEH SELATAN',
            'kecamatan' => 'BAKONGAN',
        ]);

        // Reject village code that does not start with parent district code (110101)
        $invalidPrefixResponse = $this->actingAs($user)->postJson(route('master.wilayah.store'), [
            'level' => 'village',
            'province_code' => '11',
            'city_code' => '1101',
            'district_code' => '110101',
            'village_name' => 'DESA BARU',
            'code' => '11.01.02.2001',
        ]);

        $invalidPrefixResponse->assertStatus(422)
            ->assertJsonValidationErrors(['code']);

        // Create valid village under district 110101, city 1101, province 11
        $createResponse = $this->actingAs($user)->postJson(route('master.wilayah.store'), [
            'level' => 'village',
            'province_code' => '11',
            'city_code' => '1101',
            'district_code' => '110101',
            'village_name' => 'Desa Baru Bakongan',
            'code' => '11.01.01.2099',
        ]);

        $createResponse->assertCreated();

        $this->assertDatabaseHas('struktur_perangkat_daerah', [
            'kode_wilayah' => '1101012099',
            'kecamatan' => 'BAKONGAN',
            'desa_kelurahan' => 'DESA BARU BAKONGAN',
        ]);

        // Reject duplicate code
        $duplicateResponse = $this->actingAs($user)->postJson(route('master.wilayah.store'), [
            'level' => 'village',
            'province_code' => '11',
            'city_code' => '1101',
            'district_code' => '110101',
            'village_name' => 'Desa Duplikat',
            'code' => '1101012099',
        ]);

        $duplicateResponse->assertStatus(422)
            ->assertJsonValidationErrors(['code']);

        // Update village
        $updateResponse = $this->actingAs($user)->putJson(route('master.wilayah.update', [
            'level' => 'village',
            'code' => '1101012099',
        ]), [
            'level' => 'village',
            'province_code' => '11',
            'city_code' => '1101',
            'district_code' => '110101',
            'village_name' => 'Desa Baru Bakongan Raya',
            'code' => '1101012099',
        ]);

        $updateResponse->assertOk();

        $this->assertDatabaseHas('struktur_perangkat_daerah', [
            'kode_wilayah' => '1101012099',
            'desa_kelurahan' => 'DESA BARU BAKONGAN RAYA',
        ]);

        // Prevent deleting parent district while it still has child village
        $deleteParentResponse = $this->actingAs($user)->deleteJson(route('master.wilayah.destroy', [
            'level' => 'district',
            'code' => '110101',
        ]));

        $deleteParentResponse->assertStatus(422);

        // Delete child village succeeds
        $deleteVillageResponse = $this->actingAs($user)->deleteJson(route('master.wilayah.destroy', [
            'level' => 'village',
            'code' => '1101012099',
        ]));

        $deleteVillageResponse->assertOk();
        $this->assertDatabaseMissing('struktur_perangkat_daerah', [
            'kode_wilayah' => '1101012099',
        ]);
    }
}
