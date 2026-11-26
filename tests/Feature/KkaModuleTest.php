<?php

namespace Tests\Feature;

use App\DataTables\Pelaksanaan\KkaDataTable;
use App\Models\Kka;
use App\Models\KkaReview;
use App\Models\Pka;
use App\Models\Plotting;
use App\Models\Sprin;
use App\Models\TemplateKka;
use App\Models\Tim;
use App\Models\User;
use App\Models\Wilayah;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KkaModuleTest extends TestCase
{
    use RefreshDatabase;

    protected function createPka(User $admin, ?Plotting $plotting = null, string $nomorPka = 'PKA/001/WIL-I/2026', string $status = 'Final'): Pka
    {
        $plotting ??= Plotting::query()->firstOrCreate(['plot' => 'WIL I']);

        $tim = Tim::query()->create([
            'nama_tim' => 'Tim Audit '.uniqid(),
            'lingkup' => 'Nasional',
            'plotting_id' => $plotting->id,
            'daltu_id' => $admin->id,
            'dalnis_id' => $admin->id,
            'ketua_tim_id' => $admin->id,
            'created_by' => $admin->id,
        ]);

        $sprin = Sprin::query()->create([
            'no_sprin' => 'SPRIN/'.uniqid().'/2026',
            'no_surat_pemberitahuan' => 'SPT/'.uniqid().'/2026',
            'tgl_mulai' => now()->toDateString(),
            'tgl_akhir' => now()->addDays(14)->toDateString(),
            'sprin_tipe' => 'Nasional',
            'tahap' => '1',
            'plotting_id' => $plotting->id,
            'tim_id' => $tim->id,
            'katim' => $admin->id,
            'dalnis' => $admin->id,
            'daltu' => $admin->id,
            'approved_by_daltu_date' => now(),
            'created_by' => $admin->id,
            'user_id' => $admin->id,
        ]);

        return Pka::query()->create([
            'sprin_id' => $sprin->id,
            'nomor_pka' => $nomorPka,
            'tanggal' => now()->toDateString(),
            'anggaran' => 150000000,
            'bidang' => 'Operasional',
            'jenis_audit' => 'Audit Kinerja',
            'sasaran_auditi' => 'Balai Wilayah Sungai',
            'status' => $status,
            'dibuat_oleh_id' => $admin->id,
            'direviu_oleh_id' => $admin->id,
            'created_by' => $admin->id,
        ]);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    protected function kkaPayload(Pka $pka, Plotting $plotting, array $overrides = []): array
    {
        return array_merge([
            'nomor_kka' => 'KKA/001/WIL-I/2026',
            'pka_id' => $pka->id,
            'tanggal_dibuat' => '2026-11-26',
            'tanggal_direviu' => '2026-11-27',
            'tanggal_kka' => '2026-11-28',
            'tempat' => 'Kantor Balai Wilayah Sungai',
            'auditee_level' => 'Nasional',
            'auditee_plotting_id' => $plotting->id,
            'tujuan' => '<p>Tujuan KKA</p>',
            'langkah' => '<p>Langkah KKA</p>',
            'hasil' => '<p>Hasil KKA</p>',
            'status' => 'Draft',
        ], $overrides);
    }

    protected function createKka(Pka $pka, User $admin, ?Plotting $plotting = null): Kka
    {
        $plotting ??= Plotting::query()->firstOrCreate(['plot' => 'WIL I']);

        return Kka::query()->create([
            'nomor_kka' => 'KKA/'.uniqid().'/2026',
            'pka_id' => $pka->id,
            'dibuat_oleh_id' => $admin->id,
            'direviu_oleh_id' => $admin->id,
            'tanggal_kka' => now()->toDateString(),
            'tempat' => 'Kantor Uji',
            'auditee_level' => 'Nasional',
            'auditee_plotting_id' => $plotting->id,
            'tujuan' => '<p>Tujuan</p>',
            'status' => 'Draft',
            'created_by' => $admin->id,
        ]);
    }

    public function test_kka_page_is_displayed_for_authenticated_user(): void
    {
        $admin = User::factory()->create();

        $response = $this->actingAs($admin)->get(route('pelaksanaan.kka.index'));

        $response->assertOk();
        $response->assertSee('Kertas Kerja Audit (KKA)');
        $response->assertSee('Tambah KKA');
    }

    public function test_kka_datatable_json_endpoint_returns_stacked_row_data(): void
    {
        $admin = User::factory()->create(['name' => 'Auditor Uji']);
        $plotting = Plotting::query()->create(['plot' => 'WIL I']);
        $pka = $this->createPka($admin, $plotting);
        $this->createKka($pka, $admin, $plotting);

        $response = $this->actingAs($admin)
            ->getJson(route('pelaksanaan.kka.index'), ['X-Requested-With' => 'XMLHttpRequest']);

        $response->assertOk()
            ->assertJsonStructure(['draw', 'recordsTotal', 'recordsFiltered', 'data']);

        $this->assertEquals(1, $response->json('recordsTotal'));
        $this->assertEquals($pka->nomor_pka, $response->json('data.0.nomor_pka'));
        $this->assertEquals($pka->sprin->no_sprin, $response->json('data.0.no_sprin'));
        $this->assertEquals('Auditor Uji', $response->json('data.0.auditor_name'));
        $this->assertEquals('WIL I', $response->json('data.0.auditee_name'));
        $this->assertEquals('Operasional', $response->json('data.0.bidang'));
        $this->assertEquals('1', $response->json('data.0.tahap'));
        $this->assertEquals(0, $response->json('data.0.tabulasi_count'));
        $this->assertTrue($response->json('data.0.can_review_katim'));
        $this->assertNull($response->json('data.0.latest_katim_review_status'));
    }

    public function test_kka_datatable_global_search_and_sorting_work_on_joined_columns(): void
    {
        $admin = User::factory()->create(['name' => 'Auditor Cari']);
        $plotting = Plotting::query()->create(['plot' => 'WIL I']);
        $pka = $this->createPka($admin, $plotting, 'PKA/CARI/2026');
        $this->createKka($pka, $admin, $plotting);

        $columns = (new KkaDataTable)->columns();
        $params = ['draw' => 1, 'start' => 0, 'length' => 10, 'search' => ['value' => 'PKA/CARI', 'regex' => 'false']];
        foreach ($columns as $i => $col) {
            $params['columns'][$i] = [
                'data' => $col['data'],
                'name' => $col['name'],
                'searchable' => $col['searchable'] ? 'true' : 'false',
                'orderable' => $col['sortable'] ? 'true' : 'false',
                'search' => ['value' => '', 'regex' => 'false'],
            ];
        }

        foreach ([1, 2, 3, 4, 7] as $sortIndex) {
            $params['order'] = [['column' => $sortIndex, 'dir' => 'asc']];

            $this->actingAs($admin)
                ->getJson(route('pelaksanaan.kka.index', $params), ['X-Requested-With' => 'XMLHttpRequest'])
                ->assertOk()
                ->assertJsonPath('recordsFiltered', 1);
        }

        $params['search']['value'] = 'Auditor Cari';
        $this->actingAs($admin)
            ->getJson(route('pelaksanaan.kka.index', $params), ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk()
            ->assertJsonPath('recordsFiltered', 1);

        $params['search']['value'] = 'tidak-ada-data';
        $this->actingAs($admin)
            ->getJson(route('pelaksanaan.kka.index', $params), ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk()
            ->assertJsonPath('recordsFiltered', 0);
    }

    public function test_kka_datatable_can_be_filtered_by_bidang_and_status(): void
    {
        $admin = User::factory()->create();
        $plotting = Plotting::query()->create(['plot' => 'WIL I']);
        $pka = $this->createPka($admin, $plotting);
        $this->createKka($pka, $admin, $plotting);

        $matching = $this->actingAs($admin)->getJson(
            route('pelaksanaan.kka.index', ['bidang' => 'Operasional', 'status' => 'Draft']),
            ['X-Requested-With' => 'XMLHttpRequest']
        );
        $this->assertEquals(1, $matching->json('recordsFiltered'));

        $other = $this->actingAs($admin)->getJson(
            route('pelaksanaan.kka.index', ['bidang' => 'Logistik']),
            ['X-Requested-With' => 'XMLHttpRequest']
        );
        $this->assertEquals(0, $other->json('recordsFiltered'));
    }

    public function test_can_create_kka_for_nasional_auditee_and_copy_creators_from_pka(): void
    {
        $admin = User::factory()->create();
        $reviewer = User::factory()->create();
        $plotting = Plotting::query()->create(['plot' => 'WIL II']);
        $pka = $this->createPka($admin, $plotting);
        $pka->update(['direviu_oleh_id' => $reviewer->id]);

        $response = $this->actingAs($admin)
            ->postJson(route('pelaksanaan.kka.store'), $this->kkaPayload($pka, $plotting));

        $response->assertCreated()
            ->assertJsonFragment(['message' => 'Kertas Kerja Audit (KKA) berhasil ditambahkan.']);

        $this->assertDatabaseHas('kkas', [
            'pka_id' => $pka->id,
            'dibuat_oleh_id' => $admin->id,
            'direviu_oleh_id' => $reviewer->id,
            'tempat' => 'Kantor Balai Wilayah Sungai',
            'auditee_level' => 'Nasional',
            'auditee_plotting_id' => $plotting->id,
            'auditee_kode_wilayah' => null,
            'tujuan' => '<p>Tujuan KKA</p>',
            'langkah' => '<p>Langkah KKA</p>',
            'hasil' => '<p>Hasil KKA</p>',
            'status' => 'Draft',
            'created_by' => $admin->id,
        ]);
    }

    public function test_can_create_kka_for_wilayah_auditee_with_deepest_selected_region(): void
    {
        $admin = User::factory()->create();
        $plotting = Plotting::query()->create(['plot' => 'WIL I']);
        $pka = $this->createPka($admin, $plotting);

        Wilayah::query()->create(['kode_wilayah' => '32', 'provinsi' => 'JAWA BARAT']);
        Wilayah::query()->create(['kode_wilayah' => '3209', 'provinsi' => 'JAWA BARAT', 'kabupaten_kota' => 'KABUPATEN CIREBON']);
        Wilayah::query()->create(['kode_wilayah' => '320919', 'provinsi' => 'JAWA BARAT', 'kabupaten_kota' => 'KABUPATEN CIREBON', 'kecamatan' => 'WERU']);

        $provinceOnly = $this->actingAs($admin)->postJson(route('pelaksanaan.kka.store'), $this->kkaPayload($pka, $plotting, [
            'auditee_level' => 'Wilayah',
            'auditee_plotting_id' => null,
            'province_code' => '32',
        ]));
        $provinceOnly->assertCreated();
        $this->assertDatabaseHas('kkas', ['id' => $provinceOnly->json('data.id'), 'auditee_kode_wilayah' => '32', 'auditee_plotting_id' => null]);

        $district = $this->actingAs($admin)->postJson(route('pelaksanaan.kka.store'), $this->kkaPayload($pka, $plotting, [
            'auditee_level' => 'Wilayah',
            'auditee_plotting_id' => null,
            'province_code' => '32',
            'city_code' => '3209',
            'district_code' => '320919',
        ]));
        $district->assertCreated();
        $this->assertDatabaseHas('kkas', ['id' => $district->json('data.id'), 'auditee_kode_wilayah' => '320919']);

        $json = $this->actingAs($admin)->getJson(route('pelaksanaan.kka.index'), ['X-Requested-With' => 'XMLHttpRequest']);
        $names = collect($json->json('data'))->pluck('auditee_name')->all();
        $this->assertContains('Weru', $names);
        $this->assertContains('Jawa Barat', $names);
    }

    public function test_kka_validation_rules(): void
    {
        $admin = User::factory()->create();
        $plotting = Plotting::query()->create(['plot' => 'WIL I']);
        $pka = $this->createPka($admin, $plotting);

        Wilayah::query()->create(['kode_wilayah' => '32', 'provinsi' => 'JAWA BARAT']);
        Wilayah::query()->create(['kode_wilayah' => '33', 'provinsi' => 'JAWA TENGAH']);
        Wilayah::query()->create(['kode_wilayah' => '3209', 'provinsi' => 'JAWA BARAT', 'kabupaten_kota' => 'KABUPATEN CIREBON']);
        Wilayah::query()->create(['kode_wilayah' => '320919', 'provinsi' => 'JAWA BARAT', 'kabupaten_kota' => 'KABUPATEN CIREBON', 'kecamatan' => 'WERU']);

        $this->actingAs($admin)->postJson(route('pelaksanaan.kka.store'), [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['nomor_kka', 'pka_id', 'tanggal_kka', 'tempat', 'auditee_level', 'status']);

        $this->actingAs($admin)->postJson(route('pelaksanaan.kka.store'), $this->kkaPayload($pka, $plotting, ['auditee_plotting_id' => null]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['auditee_plotting_id']);

        $this->actingAs($admin)->postJson(route('pelaksanaan.kka.store'), $this->kkaPayload($pka, $plotting, [
            'auditee_level' => 'Wilayah',
            'auditee_plotting_id' => null,
        ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['province_code']);

        $this->actingAs($admin)->postJson(route('pelaksanaan.kka.store'), $this->kkaPayload($pka, $plotting, [
            'auditee_level' => 'Wilayah',
            'auditee_plotting_id' => null,
            'province_code' => '32',
            'district_code' => '320919',
        ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['city_code']);

        $this->actingAs($admin)->postJson(route('pelaksanaan.kka.store'), $this->kkaPayload($pka, $plotting, [
            'auditee_level' => 'Wilayah',
            'auditee_plotting_id' => null,
            'province_code' => '33',
            'city_code' => '3209',
        ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['city_code']);

        $this->actingAs($admin)->postJson(route('pelaksanaan.kka.store'), $this->kkaPayload($pka, $plotting, ['status' => 'Lainnya']))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['status']);

        // PKA berstatus Draft tidak boleh dipilih untuk membuat KKA
        $draftPka = $this->createPka($admin, $plotting, 'PKA-DRAFT-2026', 'Draft');
        $finalPka = $this->createPka($admin, $plotting, 'PKA-FINAL-2026', 'Final');
        $this->actingAs($admin)->postJson(route('pelaksanaan.kka.store'), $this->kkaPayload($draftPka, $plotting))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['pka_id']);

        // Halaman KKA hanya menyediakan opsi PKA yang berstatus Final
        $page = $this->actingAs($admin)->get(route('pelaksanaan.kka.index'));
        $page->assertOk()
            ->assertSee('PKA-FINAL-2026')
            ->assertDontSee('PKA-DRAFT-2026');
    }

    public function test_can_show_update_duplicate_change_status_and_soft_delete_kka(): void
    {
        $admin = User::factory()->create();
        $plotting = Plotting::query()->create(['plot' => 'WIL I']);
        $pka = $this->createPka($admin, $plotting);
        $kka = $this->createKka($pka, $admin, $plotting);

        $this->actingAs($admin)->getJson(route('pelaksanaan.kka.show', $kka))
            ->assertOk()
            ->assertJsonFragment(['tempat' => 'Kantor Uji'])
            ->assertJsonFragment(['auditee_level' => 'Nasional'])
            ->assertJsonFragment(['pka_id' => (string) $pka->id]);

        $this->actingAs($admin)->putJson(route('pelaksanaan.kka.update', $kka), $this->kkaPayload($pka, $plotting, [
            'tempat' => 'Kantor Baru',
            'status' => 'Final',
        ]))
            ->assertOk()
            ->assertJsonFragment(['message' => 'Kertas Kerja Audit (KKA) berhasil diperbarui.']);

        $this->assertDatabaseHas('kkas', ['id' => $kka->id, 'tempat' => 'Kantor Baru', 'status' => 'Final']);

        $kka->update(['approved_by_katim_date' => now()]);

        $duplicate = $this->actingAs($admin)->postJson(route('pelaksanaan.kka.duplicate', $kka))
            ->assertCreated();
        $this->assertDatabaseHas('kkas', [
            'id' => $duplicate->json('data.id'),
            'tempat' => 'Kantor Baru',
            'status' => 'Draft',
            'approved_by_katim_date' => null,
        ]);

        $this->actingAs($admin)->postJson(route('pelaksanaan.kka.update-status', $kka), ['status' => 'Draft'])
            ->assertOk()
            ->assertJsonFragment(['status' => 'Draft']);
        $this->assertDatabaseHas('kkas', ['id' => $kka->id, 'status' => 'Draft']);

        $this->actingAs($admin)->postJson(route('pelaksanaan.kka.update-status', $kka), ['status' => 'Selesai'])
            ->assertUnprocessable();

        $this->actingAs($admin)->deleteJson(route('pelaksanaan.kka.destroy', $kka))
            ->assertOk()
            ->assertJsonFragment(['message' => 'Kertas Kerja Audit (KKA) berhasil dihapus.']);
        $this->assertSoftDeleted('kkas', ['id' => $kka->id]);
    }

    public function test_template_kka_options_are_provided_to_the_form(): void
    {
        $admin = User::factory()->create();
        $plotting = Plotting::query()->create(['plot' => 'WIL I']);

        TemplateKka::query()->create([
            'nama_template' => 'Template Uji KKA',
            'lingkup' => 'Nasional',
            'plotting_id' => $plotting->id,
            'tujuan' => '<p>Tujuan template</p>',
            'langkah' => '<p>Langkah template</p>',
            'hasil' => '<p>Hasil template</p>',
            'created_by' => $admin->id,
        ]);

        $this->actingAs($admin)->get(route('pelaksanaan.kka.index'))
            ->assertOk()
            ->assertSee('Template Uji KKA')
            ->assertSee('Langkah template');
    }

    public function test_kka_review_by_katim_updates_validation_status(): void
    {
        $admin = User::factory()->create();
        $stranger = User::factory()->create(['role' => 'Auditor']);
        $plotting = Plotting::query()->create(['plot' => 'WIL I']);
        $pka = $this->createPka($admin, $plotting);
        $kka = $this->createKka($pka, $admin, $plotting);

        $this->actingAs($stranger)->postJson(route('pelaksanaan.kka.reviews.store', [$kka, 'katim']), ['status' => 'Disetujui'])
            ->assertForbidden();

        $this->actingAs($admin)->postJson(route('pelaksanaan.kka.reviews.store', [$kka, 'katim']), ['status' => 'Tidak Valid'])
            ->assertUnprocessable();

        $this->actingAs($admin)->postJson(route('pelaksanaan.kka.reviews.store', [$kka, 'unknown']), ['status' => 'Disetujui'])
            ->assertUnprocessable();

        $this->actingAs($admin)->postJson(route('pelaksanaan.kka.reviews.store', [$kka, 'katim']), [
            'status' => 'Disetujui',
            'catatan' => 'KKA sudah sesuai.',
        ])
            ->assertCreated()
            ->assertJsonFragment(['latest_status' => 'Disetujui']);

        $this->assertDatabaseHas('kka_reviews', [
            'kka_id' => $kka->id,
            'user_id' => $admin->id,
            'role_type' => KkaReview::TYPE_KATIM,
            'status' => 'Disetujui',
        ]);
        $this->assertNotNull($kka->fresh()->approved_by_katim_date);

        $this->actingAs($admin)->getJson(route('pelaksanaan.kka.reviews.index', [$kka, 'katim']))
            ->assertOk()
            ->assertJsonPath('kka.latest_review_status', 'Disetujui')
            ->assertJsonPath('reviews.0.catatan', 'KKA sudah sesuai.');

        $this->actingAs($admin)->postJson(route('pelaksanaan.kka.reviews.store', [$kka, 'katim']), ['status' => 'Koreksi'])
            ->assertCreated();
        $this->assertNull($kka->fresh()->approved_by_katim_date);

        $row = $this->actingAs($admin)
            ->getJson(route('pelaksanaan.kka.index'), ['X-Requested-With' => 'XMLHttpRequest'])
            ->json('data.0');
        $this->assertEquals('Koreksi', $row['latest_katim_review_status']);
    }
}
