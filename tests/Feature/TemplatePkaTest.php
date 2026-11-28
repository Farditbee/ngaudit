<?php

namespace Tests\Feature;

use App\Models\Plotting;
use App\Models\TemplatePka;
use App\Models\User;
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
            'nama_template' => 'PKA Operasional Dinas 2026',
            'lingkup' => 'Dinas',
            'struktur_perangkat_daerah_id' => $plotting->id,
            'pendahuluan' => '<p>Pendahuluan uji</p>',
            'created_by' => $admin->id,
        ]);

        $response = $this->actingAs($admin)
            ->getJson(route('master.template.pka.index'), ['X-Requested-With' => 'XMLHttpRequest']);

        $response->assertOk()
            ->assertJsonStructure(['draw', 'recordsTotal', 'recordsFiltered', 'data']);

        $this->assertEquals(1, $response->json('recordsTotal'));
        $this->assertEquals('PKA Operasional Dinas 2026', $response->json('data.0.nama_template'));
    }

    public function test_can_create_template_pka_with_tiptap_rich_text_sections(): void
    {
        $admin = User::factory()->create();
        $plotting = Plotting::query()->create(['plot' => 'WIL I']);

        $payload = [
            'nama_template' => 'Template Audit Keuangan Lengkap',
            'lingkup' => 'Dinas',
            'struktur_perangkat_daerah_id' => $plotting->id,
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
            'lingkup' => 'Dinas',
            'struktur_perangkat_daerah_id' => $plotting->id,
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

        // 2. Validasi saat lingkup tidak valid
        $resInvalid = $this->actingAs($admin)->postJson(route('master.template.pka.store'), [
            'nama_template' => 'Template Uji',
            'lingkup' => 'Nasional',
        ]);

        $resInvalid->assertUnprocessable()
            ->assertJsonValidationErrors(['lingkup']);

        // 3. Validasi saat struktur_perangkat_daerah_id tidak ada di DB
        $resSpd = $this->actingAs($admin)->postJson(route('master.template.pka.store'), [
            'nama_template' => 'Template Uji',
            'lingkup' => 'Dinas',
            'struktur_perangkat_daerah_id' => 999999,
        ]);

        $resSpd->assertUnprocessable()
            ->assertJsonValidationErrors(['struktur_perangkat_daerah_id']);
    }

    public function test_admin_nasional_and_wilayah_scoping(): void
    {
        $plot1 = Plotting::query()->create(['plot' => 'Dinas Pendidikan']);
        $plot2 = Plotting::query()->create(['plot' => 'Kecamatan Terusan Nunyai', 'jenis_unit' => 'Kecamatan']);

        $adminDinas = User::factory()->create([
            'role' => 'Admin Dinas',
            'level' => 'Dinas',
            'struktur_perangkat_daerah_id' => $plot1->id,
        ]);

        $adminKecamatan = User::factory()->create([
            'role' => 'Admin Kecamatan',
            'level' => 'Kecamatan',
            'struktur_perangkat_daerah_id' => $plot2->id,
        ]);

        // Admin Dinas membuat template Dinas
        $resDinas = $this->actingAs($adminDinas)->postJson(route('master.template.pka.store'), [
            'nama_template' => 'Template Sah Dinas',
            'lingkup' => 'Dinas',
            'struktur_perangkat_daerah_id' => $plot1->id,
        ]);
        $resDinas->assertCreated();

        // Admin Kecamatan membuat template Kecamatan
        $resKecamatan = $this->actingAs($adminKecamatan)->postJson(route('master.template.pka.store'), [
            'nama_template' => 'Template Sah Kecamatan',
            'lingkup' => 'Kecamatan',
            'struktur_perangkat_daerah_id' => $plot2->id,
        ]);
        $resKecamatan->assertCreated();
    }

    public function test_can_show_update_and_soft_delete_template_pka(): void
    {
        $admin = User::factory()->create();
        $plotting = Plotting::query()->create(['plot' => 'WIL I']);

        $template = TemplatePka::query()->create([
            'nama_template' => 'Template Asli',
            'lingkup' => 'Dinas',
            'struktur_perangkat_daerah_id' => $plotting->id,
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
            'lingkup' => 'Dinas',
            'struktur_perangkat_daerah_id' => $plotting->id,
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
            'lingkup' => 'Dinas',
            'struktur_perangkat_daerah_id' => $plotting->id,
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
            'lingkup' => 'Dinas',
            'struktur_perangkat_daerah_id' => $plotting->id,
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
