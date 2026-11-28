<?php

namespace Tests\Feature;

use App\DataTables\Audit\KkaDataTable;
use App\Models\Kka;
use App\Models\KkaReview;
use App\Models\Pka;
use App\Models\Plotting;
use App\Models\Sprin;
use App\Models\StrukturPerangkatDaerah;
use App\Models\TemplateKka;
use App\Models\Tim;
use App\Models\User;
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
    protected function kkaPayload(Pka $pka, StrukturPerangkatDaerah|Plotting $unit, array $overrides = []): array
    {
        return array_merge([
            'nomor_kka' => 'KKA/001/WIL-I/2026',
            'pka_id' => $pka->id,
            'tanggal_dibuat' => '2026-11-26',
            'tanggal_direviu' => '2026-11-27',
            'tanggal_kka' => '2026-11-28',
            'tempat' => 'Kantor Balai Wilayah Sungai',
            'auditee_level' => 'Dinas',
            'struktur_perangkat_daerah_id' => $unit->id,
            'auditee_plotting_id' => $unit->id,
            'tujuan' => '<p>Tujuan KKA</p>',
            'langkah' => '<p>Langkah KKA</p>',
            'hasil' => '<p>Hasil KKA</p>',
            'status' => 'Draft',
        ], $overrides);
    }

    protected function createKka(Pka $pka, User $admin, StrukturPerangkatDaerah|Plotting|null $unit = null): Kka
    {
        $unit ??= StrukturPerangkatDaerah::query()->firstOrCreate(
            ['nama_unit' => 'Dinas Kesehatan'],
            ['level' => 1, 'jenis_unit' => 'Dinas']
        );

        return Kka::query()->create([
            'nomor_kka' => 'KKA/'.uniqid().'/2026',
            'pka_id' => $pka->id,
            'dibuat_oleh_id' => $admin->id,
            'direviu_oleh_id' => $admin->id,
            'tanggal_kka' => now()->toDateString(),
            'tempat' => 'Kantor Uji',
            'auditee_level' => 'Dinas',
            'struktur_perangkat_daerah_id' => $unit->id,
            'auditee_plotting_id' => $unit->id,
            'tujuan' => '<p>Tujuan</p>',
            'status' => 'Draft',
            'created_by' => $admin->id,
        ]);
    }

    public function test_kka_page_is_displayed_for_authenticated_user(): void
    {
        $admin = User::factory()->create();

        $response = $this->actingAs($admin)->get(route('audit.kka.index'));

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
            ->getJson(route('audit.kka.index'), ['X-Requested-With' => 'XMLHttpRequest']);

        $response->assertOk()
            ->assertJsonStructure(['draw', 'recordsTotal', 'recordsFiltered', 'data']);

        $this->assertEquals(1, $response->json('recordsTotal'));
        $this->assertEquals($pka->nomor_pka, $response->json('data.0.nomor_pka'));
        $this->assertEquals($pka->sprin->no_sprin, $response->json('data.0.no_sprin'));
        $this->assertEquals('Auditor Uji', $response->json('data.0.auditor_name'));
        $this->assertEquals('Dinas', $response->json('data.0.sprin_tipe'));
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
                ->getJson(route('audit.kka.index', $params), ['X-Requested-With' => 'XMLHttpRequest'])
                ->assertOk()
                ->assertJsonPath('recordsFiltered', 1);
        }

        $params['search']['value'] = 'Auditor Cari';
        $this->actingAs($admin)
            ->getJson(route('audit.kka.index', $params), ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk()
            ->assertJsonPath('recordsFiltered', 1);

        $params['search']['value'] = 'tidak-ada-data';
        $this->actingAs($admin)
            ->getJson(route('audit.kka.index', $params), ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk()
            ->assertJsonPath('recordsFiltered', 0);
    }

    public function test_kka_datatable_auditor_column_returns_sprin_tipe_for_dinas_and_kecamatan(): void
    {
        $admin = User::factory()->create();
        $plotting = Plotting::query()->create(['plot' => 'WIL I']);

        $pkaDinas = $this->createPka($admin, $plotting, 'PKA/DINAS/2026');
        $this->createKka($pkaDinas, $admin, $plotting);

        $timKec = Tim::query()->create([
            'nama_tim' => 'Tim Audit Kec '.uniqid(),
            'lingkup' => 'Kecamatan',
            'daltu_id' => $admin->id,
            'dalnis_id' => $admin->id,
            'ketua_tim_id' => $admin->id,
            'created_by' => $admin->id,
        ]);

        $sprinKec = Sprin::query()->create([
            'no_sprin' => 'SPRIN/KEC/'.uniqid().'/2026',
            'no_surat_pemberitahuan' => 'SPT/KEC/'.uniqid().'/2026',
            'tgl_mulai' => now()->toDateString(),
            'tgl_akhir' => now()->addDays(14)->toDateString(),
            'sprin_tipe' => 'Kecamatan',
            'tahap' => '1',
            'tim_id' => $timKec->id,
            'daltu' => $admin->id,
            'dalnis' => $admin->id,
            'katim' => $admin->id,
            'approved_by_daltu_date' => now(),
            'created_by' => $admin->id,
        ]);

        $pkaKec = Pka::query()->create([
            'sprin_id' => $sprinKec->id,
            'nomor_pka' => 'PKA/KEC/'.uniqid().'/2026',
            'tanggal' => now()->toDateString(),
            'anggaran' => 50000000,
            'bidang' => 'Operasional',
            'jenis_audit' => 'Audit Kinerja',
            'sasaran_auditi' => 'Sasaran Kec',
            'status' => 'Final',
            'dibuat_oleh_id' => $admin->id,
            'direviu_oleh_id' => $admin->id,
            'created_by' => $admin->id,
        ]);

        $this->createKka($pkaKec, $admin, $plotting);

        $response = $this->actingAs($admin)
            ->getJson(route('audit.kka.index'), ['X-Requested-With' => 'XMLHttpRequest']);

        $response->assertOk();
        $this->assertEquals(2, $response->json('recordsTotal'));
        $tipes = collect($response->json('data'))->pluck('sprin_tipe')->all();
        $this->assertContains('Dinas', $tipes);
        $this->assertContains('Kecamatan', $tipes);
    }

    public function test_kka_datatable_can_be_filtered_by_bidang_and_status(): void
    {
        $admin = User::factory()->create();
        $plotting = Plotting::query()->create(['plot' => 'WIL I']);
        $pka = $this->createPka($admin, $plotting);
        $this->createKka($pka, $admin, $plotting);

        $matching = $this->actingAs($admin)->getJson(
            route('audit.kka.index', ['bidang' => 'Operasional', 'status' => 'Draft']),
            ['X-Requested-With' => 'XMLHttpRequest']
        );
        $this->assertEquals(1, $matching->json('recordsFiltered'));

        $other = $this->actingAs($admin)->getJson(
            route('audit.kka.index', ['bidang' => 'Logistik']),
            ['X-Requested-With' => 'XMLHttpRequest']
        );
        $this->assertEquals(0, $other->json('recordsFiltered'));
    }

    public function test_can_create_kka_for_dinas_auditee_and_copy_creators_from_pka(): void
    {
        $admin = User::factory()->create();
        $reviewer = User::factory()->create();
        $unit = StrukturPerangkatDaerah::query()->create([
            'nama_unit' => 'Dinas Pendidikan',
            'level' => 1,
            'jenis_unit' => 'Dinas',
        ]);
        $pka = $this->createPka($admin);
        $pka->update(['direviu_oleh_id' => $reviewer->id]);

        $response = $this->actingAs($admin)
            ->postJson(route('audit.kka.store'), $this->kkaPayload($pka, $unit));

        $response->assertCreated()
            ->assertJsonFragment(['message' => 'Kertas Kerja Audit (KKA) berhasil ditambahkan.']);

        $this->assertDatabaseHas('kkas', [
            'pka_id' => $pka->id,
            'dibuat_oleh_id' => $admin->id,
            'direviu_oleh_id' => $reviewer->id,
            'tempat' => 'Kantor Balai Wilayah Sungai',
            'auditee_level' => 'Dinas',
            'struktur_perangkat_daerah_id' => $unit->id,
            'auditee_kode_wilayah' => null,
            'tujuan' => '<p>Tujuan KKA</p>',
            'langkah' => '<p>Langkah KKA</p>',
            'hasil' => '<p>Hasil KKA</p>',
            'status' => 'Draft',
            'created_by' => $admin->id,
        ]);
    }

    public function test_can_create_kka_for_kecamatan_auditee(): void
    {
        $admin = User::factory()->create();
        $unit = StrukturPerangkatDaerah::query()->create([
            'nama_unit' => 'Kecamatan Terbanggi Besar',
            'level' => 1,
            'jenis_unit' => 'Kecamatan',
            'kode_wilayah' => '180201',
        ]);
        $pka = $this->createPka($admin);

        $response = $this->actingAs($admin)->postJson(route('audit.kka.store'), $this->kkaPayload($pka, $unit, [
            'auditee_level' => 'Kecamatan',
            'struktur_perangkat_daerah_id' => $unit->id,
        ]));
        $response->assertCreated();
        $this->assertDatabaseHas('kkas', [
            'id' => $response->json('data.id'),
            'auditee_level' => 'Kecamatan',
            'struktur_perangkat_daerah_id' => $unit->id,
            'auditee_kode_wilayah' => '180201',
        ]);

        $json = $this->actingAs($admin)->getJson(route('audit.kka.index'), ['X-Requested-With' => 'XMLHttpRequest']);
        $names = collect($json->json('data'))->pluck('auditee_name')->all();
        $this->assertContains('Kecamatan Terbanggi Besar', $names);
    }

    public function test_can_create_kka_for_sub_unit_and_retrieve_options(): void
    {
        $admin = User::factory()->create();
        $dinas = StrukturPerangkatDaerah::query()->create([
            'nama_unit' => 'Badan Pengelolaan Keuangan dan Aset Daerah',
            'level' => 1,
            'jenis_unit' => 'Badan',
        ]);
        $subUnit = StrukturPerangkatDaerah::query()->create([
            'parent_id' => $dinas->id,
            'nama_unit' => 'Bidang Anggaran',
            'level' => 2,
            'jenis_unit' => 'Bidang',
        ]);
        $pka = $this->createPka($admin);

        $optionsSub = $this->actingAs($admin)->getJson(route('audit.kka.index', ['options' => 'sub_units', 'parent' => $dinas->id]));
        $optionsSub->assertOk()
            ->assertJsonFragment(['name' => 'Bidang Anggaran']);

        $optionsHierarchy = $this->actingAs($admin)->getJson(route('audit.kka.index', ['options' => 'unit_hierarchy', 'parent' => $subUnit->id]));
        $optionsHierarchy->assertOk()
            ->assertJsonFragment(['name' => 'Badan Pengelolaan Keuangan dan Aset Daerah'])
            ->assertJsonFragment(['name' => 'Bidang Anggaran']);

        $response = $this->actingAs($admin)->postJson(route('audit.kka.store'), $this->kkaPayload($pka, $subUnit, [
            'auditee_level' => 'Dinas',
            'struktur_perangkat_daerah_id' => $subUnit->id,
        ]));
        $response->assertCreated();
        $this->assertDatabaseHas('kkas', [
            'id' => $response->json('data.id'),
            'struktur_perangkat_daerah_id' => $subUnit->id,
        ]);

        $json = $this->actingAs($admin)->getJson(route('audit.kka.index'), ['X-Requested-With' => 'XMLHttpRequest']);
        $row = collect($json->json('data'))->firstWhere('id', $response->json('data.id'));
        $this->assertEquals('Bidang Anggaran', $row['auditee_name']);
        $this->assertEquals('Badan Pengelolaan Keuangan dan Aset Daerah', $row['auditee_detail']);
    }

    public function test_kka_validation_rules(): void
    {
        $admin = User::factory()->create();
        $unit = StrukturPerangkatDaerah::query()->create([
            'nama_unit' => 'Dinas Kesehatan',
            'level' => 1,
            'jenis_unit' => 'Dinas',
        ]);
        $pka = $this->createPka($admin);

        $this->actingAs($admin)->postJson(route('audit.kka.store'), [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['nomor_kka', 'pka_id', 'tanggal_kka', 'tempat', 'auditee_level', 'struktur_perangkat_daerah_id', 'status']);

        $this->actingAs($admin)->postJson(route('audit.kka.store'), $this->kkaPayload($pka, $unit, [
            'auditee_level' => 'Nasional',
        ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['auditee_level']);

        $this->actingAs($admin)->postJson(route('audit.kka.store'), $this->kkaPayload($pka, $unit, ['status' => 'Lainnya']))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['status']);

        // PKA berstatus Draft tidak boleh dipilih untuk membuat KKA
        $draftPka = $this->createPka($admin, null, 'PKA-DRAFT-2026', 'Draft');
        $finalPka = $this->createPka($admin, null, 'PKA-FINAL-2026', 'Final');
        $this->actingAs($admin)->postJson(route('audit.kka.store'), $this->kkaPayload($draftPka, $unit))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['pka_id']);

        // Halaman KKA hanya menyediakan opsi PKA yang berstatus Final
        $page = $this->actingAs($admin)->get(route('audit.kka.index'));
        $page->assertOk()
            ->assertSee('PKA-FINAL-2026')
            ->assertDontSee('PKA-DRAFT-2026');
    }

    public function test_can_show_update_duplicate_change_status_and_soft_delete_kka(): void
    {
        $admin = User::factory()->create();
        $unit = StrukturPerangkatDaerah::query()->create([
            'nama_unit' => 'Dinas Bina Marga',
            'level' => 1,
            'jenis_unit' => 'Dinas',
        ]);
        $pka = $this->createPka($admin);
        $kka = $this->createKka($pka, $admin, $unit);

        $this->actingAs($admin)->getJson(route('audit.kka.show', $kka))
            ->assertOk()
            ->assertJsonFragment(['tempat' => 'Kantor Uji'])
            ->assertJsonFragment(['auditee_level' => 'Dinas'])
            ->assertJsonFragment(['pka_id' => (string) $pka->id]);

        $this->actingAs($admin)->putJson(route('audit.kka.update', $kka), $this->kkaPayload($pka, $unit, [
            'tempat' => 'Kantor Baru',
            'status' => 'Final',
        ]))
            ->assertOk()
            ->assertJsonFragment(['message' => 'Kertas Kerja Audit (KKA) berhasil diperbarui.']);

        $this->assertDatabaseHas('kkas', ['id' => $kka->id, 'tempat' => 'Kantor Baru', 'status' => 'Final']);

        $kka->update(['approved_by_katim_date' => now()]);

        $duplicate = $this->actingAs($admin)->postJson(route('audit.kka.duplicate', $kka))
            ->assertCreated();
        $this->assertDatabaseHas('kkas', [
            'id' => $duplicate->json('data.id'),
            'tempat' => 'Kantor Baru',
            'status' => 'Draft',
            'approved_by_katim_date' => null,
        ]);

        $this->actingAs($admin)->postJson(route('audit.kka.update-status', $kka), ['status' => 'Draft'])
            ->assertOk()
            ->assertJsonFragment(['status' => 'Draft']);
        $this->assertDatabaseHas('kkas', ['id' => $kka->id, 'status' => 'Draft']);

        $this->actingAs($admin)->postJson(route('audit.kka.update-status', $kka), ['status' => 'Selesai'])
            ->assertUnprocessable();

        $this->actingAs($admin)->deleteJson(route('audit.kka.destroy', $kka))
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

        $this->actingAs($admin)->get(route('audit.kka.index'))
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

        $this->actingAs($stranger)->postJson(route('audit.kka.reviews.store', [$kka, 'katim']), ['status' => 'Disetujui'])
            ->assertForbidden();

        $this->actingAs($admin)->postJson(route('audit.kka.reviews.store', [$kka, 'katim']), ['status' => 'Tidak Valid'])
            ->assertUnprocessable();

        $this->actingAs($admin)->postJson(route('audit.kka.reviews.store', [$kka, 'unknown']), ['status' => 'Disetujui'])
            ->assertUnprocessable();

        $this->actingAs($admin)->postJson(route('audit.kka.reviews.store', [$kka, 'katim']), [
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

        $this->actingAs($admin)->getJson(route('audit.kka.reviews.index', [$kka, 'katim']))
            ->assertOk()
            ->assertJsonPath('kka.latest_review_status', 'Disetujui')
            ->assertJsonPath('reviews.0.catatan', 'KKA sudah sesuai.');

        $this->actingAs($admin)->postJson(route('audit.kka.reviews.store', [$kka, 'katim']), ['status' => 'Koreksi'])
            ->assertCreated();
        $this->assertNull($kka->fresh()->approved_by_katim_date);

        $row = $this->actingAs($admin)
            ->getJson(route('audit.kka.index'), ['X-Requested-With' => 'XMLHttpRequest'])
            ->json('data.0');
        $this->assertEquals('Koreksi', $row['latest_katim_review_status']);
    }

    public function test_kka_datatable_and_access_isolated_to_assigned_auditor_creator_and_super_admin(): void
    {
        $superAdmin = User::factory()->create(['role' => 'Super Admin']);
        $creatorAdmin = User::factory()->create(['role' => 'Admin Dinas']);
        $assignedAuditor = User::factory()->create(['role' => 'Auditor']);
        $unassignedAuditor = User::factory()->create(['role' => 'Auditor']);

        $pka = $this->createPka($superAdmin);
        $unit = StrukturPerangkatDaerah::query()->create([
            'nama_unit' => 'Dinas Perhubungan',
            'level' => 1,
            'jenis_unit' => 'Dinas',
        ]);

        $kka = Kka::query()->create([
            'nomor_kka' => 'KKA/ISOLATION/001',
            'pka_id' => $pka->id,
            'dibuat_oleh_id' => $assignedAuditor->id,
            'created_by' => $creatorAdmin->id,
            'tanggal_dibuat' => now()->toDateString(),
            'tanggal_direviu' => now()->toDateString(),
            'tanggal_kka' => now()->toDateString(),
            'tempat' => 'Gunung Sugih',
            'auditee_level' => 'Dinas',
            'struktur_perangkat_daerah_id' => $unit->id,
            'status' => 'Draft',
        ]);

        // 1. Super Admin sees the KKA
        $responseSuperAdmin = $this->actingAs($superAdmin)->getJson(route('audit.kka.index'), ['X-Requested-With' => 'XMLHttpRequest']);
        $responseSuperAdmin->assertOk();
        $this->assertCount(1, $responseSuperAdmin->json('data'));

        // 2. Creator Admin sees the KKA
        $responseCreator = $this->actingAs($creatorAdmin)->getJson(route('audit.kka.index'), ['X-Requested-With' => 'XMLHttpRequest']);
        $responseCreator->assertOk();
        $this->assertCount(1, $responseCreator->json('data'));

        // 3. Assigned Auditor sees the KKA and can access show
        $responseAssigned = $this->actingAs($assignedAuditor)->getJson(route('audit.kka.index'), ['X-Requested-With' => 'XMLHttpRequest']);
        $responseAssigned->assertOk();
        $this->assertCount(1, $responseAssigned->json('data'));
        $this->actingAs($assignedAuditor)->getJson(route('audit.kka.show', $kka))->assertOk();

        // 4. Unassigned Auditor CANNOT see the KKA in datatable and CANNOT access show
        $responseUnassigned = $this->actingAs($unassignedAuditor)->getJson(route('audit.kka.index'), ['X-Requested-With' => 'XMLHttpRequest']);
        $responseUnassigned->assertOk();
        $this->assertCount(0, $responseUnassigned->json('data'));
        $this->actingAs($unassignedAuditor)->getJson(route('audit.kka.show', $kka))->assertForbidden();
    }

    public function test_auditor_only_sees_assigned_pka_options_when_creating_kka_while_admin_sees_all(): void
    {
        $superAdmin = User::factory()->create(['role' => 'Super Admin']);
        $auditor1 = User::factory()->create(['role' => 'Auditor']);
        $auditor2 = User::factory()->create(['role' => 'Auditor']);

        $pka1 = $this->createPka($superAdmin, null, 'PKA/AUDITOR1/2026', 'Final');
        $pka1->update(['dibuat_oleh_id' => $auditor1->id]);

        $pka2 = $this->createPka($superAdmin, null, 'PKA/AUDITOR2/2026', 'Final');
        $pka2->update(['dibuat_oleh_id' => $auditor2->id]);

        // 1. Super Admin sees both PKAs in create modal options
        $responseAdmin = $this->actingAs($superAdmin)->get(route('audit.kka.index'));
        $responseAdmin->assertOk();
        $pkasAdmin = collect($responseAdmin->viewData('dataTableConfig')['pkas']);
        $this->assertCount(2, $pkasAdmin);

        // 2. Auditor 1 only sees PKA 1
        $responseAuditor1 = $this->actingAs($auditor1)->get(route('audit.kka.index'));
        $responseAuditor1->assertOk();
        $pkasAuditor1 = collect($responseAuditor1->viewData('dataTableConfig')['pkas']);
        $this->assertCount(1, $pkasAuditor1);
        $this->assertEquals($pka1->id, $pkasAuditor1->first()['id']);

        // 3. Auditor 2 only sees PKA 2
        $responseAuditor2 = $this->actingAs($auditor2)->get(route('audit.kka.index'));
        $responseAuditor2->assertOk();
        $pkasAuditor2 = collect($responseAuditor2->viewData('dataTableConfig')['pkas']);
        $this->assertCount(1, $pkasAuditor2);
        $this->assertEquals($pka2->id, $pkasAuditor2->first()['id']);

        // 4. Auditor 1 cannot create KKA with PKA 2 (forbidden)
        $unit = StrukturPerangkatDaerah::query()->create([
            'nama_unit' => 'Dinas Perhubungan',
            'level' => 1,
            'jenis_unit' => 'Dinas',
        ]);

        $payload = $this->kkaPayload($pka2, $unit);
        $this->actingAs($auditor1)->postJson(route('audit.kka.store'), $payload)
            ->assertForbidden();

        // 5. Auditor 1 can create KKA with PKA 1 (assigned)
        $payloadPka1 = $this->kkaPayload($pka1, $unit);
        $this->actingAs($auditor1)->postJson(route('audit.kka.store'), $payloadPka1)
            ->assertCreated();
    }
}
