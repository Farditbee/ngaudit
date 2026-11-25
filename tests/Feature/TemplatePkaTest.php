<?php

namespace Tests\Feature;

use App\Models\Plotting;
use App\Models\TemplatePka;
use App\Models\User;
use App\Models\Wilayah;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TemplatePkaTest extends TestCase
{
    use RefreshDatabase;

    public function test_template_pka_page_is_displayed_for_authenticated_user(): void
    {
        $admin = User::factory()->create();

        $response = $this->actingAs($admin)->get(route('master.template.pka.index'));

        $response->assertOk();
        $response->assertSee('Template PKA');
        $response->assertSee('Tambah Template PKA');
    }

    public function test_template_pka_datatable_json_endpoint(): void
    {
        $admin = User::factory()->create();
        $plotting = Plotting::query()->create(['plot' => 'WIL I']);

        TemplatePka::query()->create([
            'nama_template' => 'PKA Operasional Nasional 2026',
            'lingkup' => 'Nasional',
            'plotting_id' => $plotting->id,
            'pendahuluan' => '<p>Pendahuluan uji</p>',
            'created_by' => $admin->id,
        ]);

        $response = $this->actingAs($admin)
            ->getJson(route('master.template.pka.index'), ['X-Requested-With' => 'XMLHttpRequest']);

        $response->assertOk()
            ->assertJsonStructure(['draw', 'recordsTotal', 'recordsFiltered', 'data']);

        $this->assertEquals(1, $response->json('recordsTotal'));
        $this->assertEquals('PKA Operasional Nasional 2026', $response->json('data.0.nama_template'));
    }

    public function test_can_create_template_pka_with_tiptap_rich_text_sections(): void
    {
        $admin = User::factory()->create();
        $plotting = Plotting::query()->create(['plot' => 'WIL I']);

        $payload = [
            'nama_template' => 'Template Audit Keuangan Lengkap',
            'lingkup' => 'Nasional',
            'plotting_id' => $plotting->id,
            'pendahuluan' => '<p><strong>Bab I:</strong> Pendahuluan audit keuangan.</p>',
            'tujuan' => '<ul><li>Memberikan keyakinan memadai</li></ul>',
            'ruang_lingkup' => '<p>Ruang lingkup mencakup seluruh transaksi TA 2026.</p>',
            'sasaran' => '<p>Sasaran efisiensi dan kepatuhan.</p>',
            'waktu_pelaksanaan' => '<p>Pelaksanaan selama 30 hari kalender.</p>',
            'periode' => '<p>01 Januari s/d 31 Desember 2026.</p>',
            'penutup' => '<p>Demikian PKA ini disusun untuk dipedomani.</p>',
        ];

        $response = $this->actingAs($admin)->postJson(route('master.template.pka.store'), $payload);

        $response->assertCreated()
            ->assertJsonFragment(['message' => 'Template PKA berhasil ditambahkan.']);

        $this->assertDatabaseHas('template_pkas', [
            'nama_template' => 'Template Audit Keuangan Lengkap',
            'lingkup' => 'Nasional',
            'plotting_id' => $plotting->id,
            'created_by' => $admin->id,
        ]);

        $created = TemplatePka::query()->where('nama_template', 'Template Audit Keuangan Lengkap')->first();
        $this->assertNotNull($created);
        $this->assertEquals($payload['pendahuluan'], $created->pendahuluan);
        $this->assertEquals($payload['penutup'], $created->penutup);
    }

    public function test_validation_rules_for_template_pka(): void
    {
        $admin = User::factory()->create();

        // 1. Validasi saat data kosong
        $response = $this->actingAs($admin)->postJson(route('master.template.pka.store'), []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['nama_template', 'lingkup']);

        // 2. Validasi saat lingkup Nasional namun plotting_id tidak diisi
        $resNasional = $this->actingAs($admin)->postJson(route('master.template.pka.store'), [
            'nama_template' => 'Template Uji',
            'lingkup' => 'Nasional',
        ]);

        $resNasional->assertUnprocessable()
            ->assertJsonValidationErrors(['plotting_id']);

        // 3. Validasi saat lingkup Wilayah namun kode_wilayah tidak diisi
        $resWilayah = $this->actingAs($admin)->postJson(route('master.template.pka.store'), [
            'nama_template' => 'Template Uji',
            'lingkup' => 'Wilayah',
        ]);

        $resWilayah->assertUnprocessable()
            ->assertJsonValidationErrors(['kode_wilayah']);
    }

    public function test_admin_nasional_and_wilayah_scoping(): void
    {
        $plot1 = Plotting::query()->create(['plot' => 'WIL I']);
        $plot2 = Plotting::query()->create(['plot' => 'WIL II']);

        Wilayah::query()->create([
            'kode_wilayah' => '32',
            'provinsi' => 'JAWA BARAT',
        ]);
        Wilayah::query()->create([
            'kode_wilayah' => '33',
            'provinsi' => 'JAWA TENGAH',
        ]);

        $adminNasional = User::factory()->create([
            'role' => 'Admin Nasional',
            'plotting_id' => $plot1->id,
        ]);

        $adminWilayah = User::factory()->create([
            'role' => 'Admin Wilayah',
            'kode_wilayah' => '32.00',
        ]);

        // Admin Nasional tidak boleh membuat template untuk plotting orang lain
        $resNasionalFail = $this->actingAs($adminNasional)->postJson(route('master.template.pka.store'), [
            'nama_template' => 'Template Ilegal Nasional',
            'lingkup' => 'Nasional',
            'plotting_id' => $plot2->id,
        ]);
        $resNasionalFail->assertUnprocessable()
            ->assertJsonValidationErrors(['plotting_id']);

        // Admin Nasional berhasil jika plotting_id miliknya
        $resNasionalSuccess = $this->actingAs($adminNasional)->postJson(route('master.template.pka.store'), [
            'nama_template' => 'Template Sah Nasional',
            'lingkup' => 'Nasional',
            'plotting_id' => $plot1->id,
        ]);
        $resNasionalSuccess->assertCreated();

        // Admin Wilayah tidak boleh membuat template untuk provinsi lain
        $resWilayahFail = $this->actingAs($adminWilayah)->postJson(route('master.template.pka.store'), [
            'nama_template' => 'Template Ilegal Wilayah',
            'lingkup' => 'Wilayah',
            'kode_wilayah' => '33',
        ]);
        $resWilayahFail->assertUnprocessable()
            ->assertJsonValidationErrors(['kode_wilayah']);

        // Admin Wilayah berhasil jika sesuai provinsinya
        $resWilayahSuccess = $this->actingAs($adminWilayah)->postJson(route('master.template.pka.store'), [
            'nama_template' => 'Template Sah Wilayah',
            'lingkup' => 'Wilayah',
            'kode_wilayah' => '32',
        ]);
        $resWilayahSuccess->assertCreated();
    }

    public function test_can_show_update_and_soft_delete_template_pka(): void
    {
        $admin = User::factory()->create();
        $plotting = Plotting::query()->create(['plot' => 'WIL I']);

        $template = TemplatePka::query()->create([
            'nama_template' => 'Template Asli',
            'lingkup' => 'Nasional',
            'plotting_id' => $plotting->id,
            'pendahuluan' => '<p>Pendahuluan lama</p>',
            'created_by' => $admin->id,
        ]);

        // Show endpoint
        $showResponse = $this->actingAs($admin)->getJson(route('master.template.pka.show', $template));
        $showResponse->assertOk()
            ->assertJsonFragment(['nama_template' => 'Template Asli'])
            ->assertJsonFragment(['pendahuluan' => '<p>Pendahuluan lama</p>']);

        // Update endpoint
        $updateResponse = $this->actingAs($admin)->putJson(route('master.template.pka.update', $template), [
            'nama_template' => 'Template Terupdate',
            'lingkup' => 'Nasional',
            'plotting_id' => $plotting->id,
            'pendahuluan' => '<p>Pendahuluan baru yang sudah direvisi</p>',
        ]);

        $updateResponse->assertOk()
            ->assertJsonFragment(['message' => 'Template PKA berhasil diperbarui.']);

        $this->assertDatabaseHas('template_pkas', [
            'id' => $template->id,
            'nama_template' => 'Template Terupdate',
            'pendahuluan' => '<p>Pendahuluan baru yang sudah direvisi</p>',
        ]);

        // Delete endpoint (soft delete)
        $deleteResponse = $this->actingAs($admin)->deleteJson(route('master.template.pka.destroy', $template));
        $deleteResponse->assertOk()
            ->assertJsonFragment(['message' => 'Template PKA berhasil dihapus.']);

        $this->assertSoftDeleted('template_pkas', [
            'id' => $template->id,
        ]);
    }

    public function test_can_duplicate_template_pka_with_replicated_sections(): void
    {
        $admin = User::factory()->create();
        $plotting = Plotting::query()->create(['plot' => 'WIL I']);

        $original = TemplatePka::query()->create([
            'nama_template' => 'PKA Audit Reguler 2026',
            'lingkup' => 'Nasional',
            'plotting_id' => $plotting->id,
            'pendahuluan' => '<p>Pendahuluan dokumen asli</p>',
            'tujuan' => '<p>Tujuan dokumen asli</p>',
            'penutup' => '<p>Penutup dokumen asli</p>',
            'created_by' => $admin->id,
        ]);

        $response = $this->actingAs($admin)
            ->postJson(route('master.template.pka.duplicate', $original));

        $response->assertCreated()
            ->assertJsonFragment(['message' => 'Template PKA berhasil diduplikasi.']);

        $this->assertDatabaseHas('template_pkas', [
            'nama_template' => 'PKA Audit Reguler 2026 (Salinan)',
            'lingkup' => 'Nasional',
            'plotting_id' => $plotting->id,
            'pendahuluan' => '<p>Pendahuluan dokumen asli</p>',
            'tujuan' => '<p>Tujuan dokumen asli</p>',
            'penutup' => '<p>Penutup dokumen asli</p>',
        ]);

        // Duplikasi kedua kalinya harus menghasilkan penomoran (Salinan 2)
        $secondResponse = $this->actingAs($admin)
            ->postJson(route('master.template.pka.duplicate', $original));

        $secondResponse->assertCreated();
        $this->assertDatabaseHas('template_pkas', [
            'nama_template' => 'PKA Audit Reguler 2026 (Salinan 2)',
        ]);
    }
}
