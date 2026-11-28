<?php

namespace Tests\Feature;

use App\Models\Plotting;
use App\Models\TemplateKka;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TemplateKkaTest extends TestCase
{
    use RefreshDatabase;

    public function test_template_kka_page_is_displayed_for_authenticated_user(): void
    {
        $admin = User::factory()->create();

        $response = $this->actingAs($admin)->get(route('master.template.kka.index'));

        $response->assertOk();
        $response->assertSee('Template KKA');
        $response->assertSee('Tambah Template KKA');
    }

    public function test_template_kka_datatable_json_endpoint(): void
    {
        $admin = User::factory()->create();
        $plotting = Plotting::query()->create(['plot' => 'WIL I']);

        TemplateKka::query()->create([
            'nama_template' => 'KKA Operasional Dinas 2026',
            'lingkup' => 'Dinas',
            'struktur_perangkat_daerah_id' => $plotting->id,
            'tujuan' => '<p>Tujuan pengujian</p>',
            'langkah' => '<p>Langkah kerja pengujian</p>',
            'hasil' => '<p>Hasil pengujian</p>',
            'created_by' => $admin->id,
        ]);

        $response = $this->actingAs($admin)
            ->getJson(route('master.template.kka.index'), ['X-Requested-With' => 'XMLHttpRequest']);

        $response->assertOk()
            ->assertJsonStructure(['draw', 'recordsTotal', 'recordsFiltered', 'data']);

        $this->assertEquals(1, $response->json('recordsTotal'));
        $this->assertEquals('KKA Operasional Dinas 2026', $response->json('data.0.nama_template'));
    }

    public function test_can_create_template_kka_with_tujuan_langkah_and_hasil_sections(): void
    {
        $admin = User::factory()->create();
        $plotting = Plotting::query()->create(['plot' => 'WIL I']);

        $payload = [
            'nama_template' => 'Template KKA Audit Keuangan',
            'lingkup' => 'Dinas',
            'struktur_perangkat_daerah_id' => $plotting->id,
            'tujuan' => '<p><strong>Tujuan:</strong> Memastikan keandalan laporan keuangan.</p>',
            'langkah' => '<ol><li>Cek bukti kas</li><li>Vouching kuitansi</li></ol>',
            'hasil' => '<p><strong>Hasil:</strong> Bukti kas telah sesuai.</p>',
        ];

        $response = $this->actingAs($admin)->postJson(route('master.template.kka.store'), $payload);

        $response->assertCreated()
            ->assertJsonFragment(['message' => 'Template KKA berhasil ditambahkan.']);

        $this->assertDatabaseHas('template_kkas', [
            'nama_template' => 'Template KKA Audit Keuangan',
            'lingkup' => 'Dinas',
            'struktur_perangkat_daerah_id' => $plotting->id,
            'created_by' => $admin->id,
        ]);

        $created = TemplateKka::query()->where('nama_template', 'Template KKA Audit Keuangan')->first();
        $this->assertNotNull($created);
        $this->assertEquals($payload['tujuan'], $created->tujuan);
        $this->assertEquals($payload['langkah'], $created->langkah);
        $this->assertEquals($payload['hasil'], $created->hasil);
    }

    public function test_validation_rules_for_template_kka(): void
    {
        $admin = User::factory()->create();

        // 1. Validasi saat data kosong
        $response = $this->actingAs($admin)->postJson(route('master.template.kka.store'), []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['nama_template', 'lingkup']);

        // 2. Validasi saat lingkup tidak valid
        $resInvalid = $this->actingAs($admin)->postJson(route('master.template.kka.store'), [
            'nama_template' => 'Template Uji',
            'lingkup' => 'Nasional',
        ]);

        $resInvalid->assertUnprocessable()
            ->assertJsonValidationErrors(['lingkup']);

        // 3. Validasi saat struktur_perangkat_daerah_id tidak valid
        $resSpd = $this->actingAs($admin)->postJson(route('master.template.kka.store'), [
            'nama_template' => 'Template Uji',
            'lingkup' => 'Dinas',
            'struktur_perangkat_daerah_id' => 999999,
        ]);

        $resSpd->assertUnprocessable()
            ->assertJsonValidationErrors(['struktur_perangkat_daerah_id']);
    }

    public function test_logged_in_account_automatic_scope_locking_for_nasional_and_wilayah(): void
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

        // Admin Dinas berhasil
        $resDinas = $this->actingAs($adminDinas)->postJson(route('master.template.kka.store'), [
            'nama_template' => 'Template Sah Dinas',
            'lingkup' => 'Dinas',
            'struktur_perangkat_daerah_id' => $plot1->id,
        ]);
        $resDinas->assertCreated();

        // Admin Kecamatan berhasil
        $resKecamatan = $this->actingAs($adminKecamatan)->postJson(route('master.template.kka.store'), [
            'nama_template' => 'Template Sah Kecamatan',
            'lingkup' => 'Kecamatan',
            'struktur_perangkat_daerah_id' => $plot2->id,
        ]);
        $resKecamatan->assertCreated();
    }

    public function test_can_show_update_duplicate_and_soft_delete_template_kka(): void
    {
        $admin = User::factory()->create();
        $plotting = Plotting::query()->create(['plot' => 'WIL I']);

        $template = TemplateKka::query()->create([
            'nama_template' => 'Template KKA Asli',
            'lingkup' => 'Dinas',
            'struktur_perangkat_daerah_id' => $plotting->id,
            'tujuan' => '<p>Tujuan lama</p>',
            'langkah' => '<p>Langkah lama</p>',
            'hasil' => '<p>Hasil lama</p>',
            'created_by' => $admin->id,
        ]);

        // Show endpoint
        $showResponse = $this->actingAs($admin)->getJson(route('master.template.kka.show', $template));
        $showResponse->assertOk()
            ->assertJsonFragment(['nama_template' => 'Template KKA Asli'])
            ->assertJsonFragment(['tujuan' => '<p>Tujuan lama</p>'])
            ->assertJsonFragment(['langkah' => '<p>Langkah lama</p>'])
            ->assertJsonFragment(['hasil' => '<p>Hasil lama</p>']);

        // Update endpoint
        $updateResponse = $this->actingAs($admin)->putJson(route('master.template.kka.update', $template), [
            'nama_template' => 'Template KKA Terupdate',
            'lingkup' => 'Dinas',
            'struktur_perangkat_daerah_id' => $plotting->id,
            'tujuan' => '<p>Tujuan baru</p>',
            'langkah' => '<p>Langkah baru</p>',
            'hasil' => '<p>Hasil baru</p>',
        ]);

        $updateResponse->assertOk()
            ->assertJsonFragment(['message' => 'Template KKA berhasil diperbarui.']);

        $this->assertDatabaseHas('template_kkas', [
            'id' => $template->id,
            'nama_template' => 'Template KKA Terupdate',
            'tujuan' => '<p>Tujuan baru</p>',
            'langkah' => '<p>Langkah baru</p>',
            'hasil' => '<p>Hasil baru</p>',
        ]);

        // Duplicate endpoint
        $dupResponse = $this->actingAs($admin)->postJson(route('master.template.kka.duplicate', $template));
        $dupResponse->assertCreated()
            ->assertJsonFragment(['message' => 'Template KKA berhasil diduplikasi.']);

        $this->assertDatabaseHas('template_kkas', [
            'nama_template' => 'Template KKA Terupdate (Salinan)',
            'lingkup' => 'Dinas',
            'struktur_perangkat_daerah_id' => $plotting->id,
            'tujuan' => '<p>Tujuan baru</p>',
            'langkah' => '<p>Langkah baru</p>',
            'hasil' => '<p>Hasil baru</p>',
        ]);

        // Delete endpoint (soft delete)
        $deleteResponse = $this->actingAs($admin)->deleteJson(route('master.template.kka.destroy', $template));
        $deleteResponse->assertOk()
            ->assertJsonFragment(['message' => 'Template KKA berhasil dihapus.']);

        $this->assertSoftDeleted('template_kkas', [
            'id' => $template->id,
        ]);
    }
}
