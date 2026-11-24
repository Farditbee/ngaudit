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
        $response->assertSee('Data Wilayah Indonesia');
    }

    public function test_wilayah_datatable_returns_hierarchical_json_data(): void
    {
        $user = User::factory()->create();

        $prefix = config('laravolt.indonesia.table_prefix', 'indonesia_');

        DB::table($prefix.'provinces')->insert([
            'code' => '11',
            'name' => 'ACEH',
        ]);

        DB::table($prefix.'cities')->insert([
            'code' => '1101',
            'province_code' => '11',
            'name' => 'KABUPATEN ACEH SELATAN',
        ]);

        DB::table($prefix.'districts')->insert([
            'code' => '110101',
            'city_code' => '1101',
            'name' => 'BAKONGAN',
        ]);

        DB::table($prefix.'villages')->insert([
            'code' => '1101012001',
            'district_code' => '110101',
            'name' => 'KEUDE BAKONGAN',
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
            ],
        ]));

        $response->assertOk()
            ->assertJsonPath('recordsTotal', 4)
            ->assertJsonPath('data.0.code', '11')
            ->assertJsonPath('data.0.province_name', 'ACEH')
            ->assertJsonPath('data.0.city_name', '')
            ->assertJsonPath('data.1.code', '11.01')
            ->assertJsonPath('data.1.city_name', 'KABUPATEN ACEH SELATAN')
            ->assertJsonPath('data.2.code', '11.01.01')
            ->assertJsonPath('data.2.district_name', 'BAKONGAN')
            ->assertJsonPath('data.3.code', '11.01.01.2001')
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
            ],
        ]));

        $filteredResponse->assertOk()
            ->assertJsonPath('recordsFiltered', 2)
            ->assertJsonPath('data.0.code', '11.01.01')
            ->assertJsonPath('data.1.code', '11.01.01.2001');
    }
}
