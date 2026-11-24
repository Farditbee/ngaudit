<?php

namespace Tests\Feature;

use App\Models\Plotting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PlottingTest extends TestCase
{
    use RefreshDatabase;

    public function test_plotting_page_is_displayed_for_authenticated_user(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('master.plotting.index'));

        $response->assertOk();
        $response->assertSee('Data Plotting Wilayah');
    }

    public function test_user_can_create_update_list_and_delete_plotting_with_multiple_provinces(): void
    {
        $user = User::factory()->create();
        $prefix = config('laravolt.indonesia.table_prefix', 'indonesia_');

        $acehId = DB::table($prefix.'provinces')->insertGetId([
            'code' => '11',
            'name' => 'ACEH',
        ]);

        $sumutId = DB::table($prefix.'provinces')->insertGetId([
            'code' => '12',
            'name' => 'SUMATERA UTARA',
        ]);

        $sumbarId = DB::table($prefix.'provinces')->insertGetId([
            'code' => '13',
            'name' => 'SUMATERA BARAT',
        ]);

        $storeResponse = $this->actingAs($user)->postJson(route('master.plotting.store'), [
            'plot' => 'WIL I',
            'provinsi_ids' => [$acehId, $sumutId],
        ]);

        $storeResponse->assertCreated()
            ->assertJsonPath('data.plot', 'WIL I')
            ->assertJsonCount(2, 'data.provinces');

        $plotting = Plotting::query()->firstOrFail();

        $this->assertDatabaseHas('plottings', [
            'id' => $plotting->id,
            'plot' => 'WIL I',
        ]);

        $this->assertDatabaseHas('plotting_province', [
            'plotting_id' => $plotting->id,
            'provinsi_id' => $acehId,
        ]);

        $this->assertDatabaseHas('plotting_province', [
            'plotting_id' => $plotting->id,
            'provinsi_id' => $sumutId,
        ]);

        $datatableResponse = $this->actingAs($user)->getJson(route('master.plotting.index', [
            'draw' => 1,
            'start' => 0,
            'length' => 10,
            'provinsi_id' => $acehId,
            'order' => [['column' => 1, 'dir' => 'asc']],
            'columns' => [
                ['data' => 'DT_RowIndex', 'name' => 'DT_RowIndex', 'searchable' => 'false', 'orderable' => 'false'],
                ['data' => 'plot', 'name' => 'plot', 'searchable' => 'true', 'orderable' => 'true'],
                ['data' => 'provinces_names', 'name' => 'provinces_names', 'searchable' => 'true', 'orderable' => 'false'],
                ['data' => 'provinces_count', 'name' => 'provinces_count', 'searchable' => 'false', 'orderable' => 'true'],
                ['data' => 'actions', 'name' => 'actions', 'searchable' => 'false', 'orderable' => 'false'],
            ],
        ]));

        $datatableResponse->assertOk()
            ->assertJsonPath('recordsTotal', 1)
            ->assertJsonPath('data.0.plot', 'WIL I')
            ->assertJsonPath('data.0.provinces_count', 2)
            ->assertJsonPath('data.0.provinces_names', 'ACEH, SUMATERA UTARA');

        $updateResponse = $this->actingAs($user)->putJson(route('master.plotting.update', $plotting), [
            'plot' => 'WIL I UPDATED',
            'provinsi_ids' => [$sumbarId],
        ]);

        $updateResponse->assertOk()
            ->assertJsonPath('data.plot', 'WIL I UPDATED')
            ->assertJsonCount(1, 'data.provinces');

        $this->assertDatabaseMissing('plotting_province', [
            'plotting_id' => $plotting->id,
            'provinsi_id' => $acehId,
        ]);

        $this->assertDatabaseHas('plotting_province', [
            'plotting_id' => $plotting->id,
            'provinsi_id' => $sumbarId,
        ]);

        $deleteResponse = $this->actingAs($user)->deleteJson(route('master.plotting.destroy', $plotting));

        $deleteResponse->assertOk();
        $this->assertDatabaseMissing('plottings', ['id' => $plotting->id]);
        $this->assertDatabaseMissing('plotting_province', ['plotting_id' => $plotting->id]);
    }
}
