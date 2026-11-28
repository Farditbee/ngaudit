<?php

namespace Tests\Feature;

use App\Models\Plotting;
use App\Models\TemplateLangkahKerja;
use App\Models\TemplateLangkahKerjaDetail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TemplateLangkahKerjaTest extends TestCase
{
    use RefreshDatabase;

    public function test_template_langkah_kerja_page_is_displayed_for_authenticated_user(): void
    {
        $admin = User::factory()->create();

        $response = $this->actingAs($admin)->get(route('master.template.langkah-kerja.index'));

        $response->assertOk();
        $response->assertSee('Template Langkah Kerja');
        $response->assertSee('Tambah Langkah Kerja');
    }

    public function test_template_langkah_kerja_datatable_json_endpoint(): void
    {
        $admin = User::factory()->create(['name' => 'Admin Tester']);
        $plotting = Plotting::query()->create(['plot' => 'WIL I']);

        TemplateLangkahKerja::query()->create([
            'nama_template' => 'Langkah Kerja Operasional Dinas 2026',
            'lingkup' => 'Dinas',
            'struktur_perangkat_daerah_id' => $plotting->id,
            'jumlah_langkah_kerja' => 0,
            'created_by' => $admin->id,
        ]);

        $response = $this->actingAs($admin)
            ->getJson(route('master.template.langkah-kerja.index'), ['X-Requested-With' => 'XMLHttpRequest']);

        $response->assertOk()
            ->assertJsonStructure(['draw', 'recordsTotal', 'recordsFiltered', 'data']);

        $this->assertEquals(1, $response->json('recordsTotal'));
        $this->assertEquals('Langkah Kerja Operasional Dinas 2026', $response->json('data.0.nama_template'));
        $this->assertEquals(0, $response->json('data.0.jumlah_langkah_kerja'));
        $this->assertEquals('Admin Tester', $response->json('data.0.creator_name'));
    }

    public function test_can_create_template_langkah_kerja(): void
    {
        $admin = User::factory()->create();
        $plotting = Plotting::query()->create(['plot' => 'WIL I']);

        $payload = [
            'nama_template' => 'Template Langkah Kerja Keuangan',
            'lingkup' => 'Dinas',
            'struktur_perangkat_daerah_id' => $plotting->id,
        ];

        $response = $this->actingAs($admin)->postJson(route('master.template.langkah-kerja.store'), $payload);

        $response->assertCreated()
            ->assertJsonFragment(['message' => 'Template Langkah Kerja berhasil ditambahkan.']);

        $this->assertDatabaseHas('template_langkah_kerjas', [
            'nama_template' => 'Template Langkah Kerja Keuangan',
            'lingkup' => 'Dinas',
            'struktur_perangkat_daerah_id' => $plotting->id,
            'jumlah_langkah_kerja' => 0,
            'created_by' => $admin->id,
        ]);
    }

    public function test_validation_rules_for_template_langkah_kerja(): void
    {
        $admin = User::factory()->create();

        // 1. Validasi saat data kosong
        $response = $this->actingAs($admin)->postJson(route('master.template.langkah-kerja.store'), []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['nama_template', 'lingkup']);

        // 2. Validasi saat lingkup tidak valid
        $resInvalid = $this->actingAs($admin)->postJson(route('master.template.langkah-kerja.store'), [
            'nama_template' => 'Template Uji',
            'lingkup' => 'Nasional',
        ]);

        $resInvalid->assertUnprocessable()
            ->assertJsonValidationErrors(['lingkup']);

        // 3. Validasi saat struktur_perangkat_daerah_id tidak valid
        $resSpd = $this->actingAs($admin)->postJson(route('master.template.langkah-kerja.store'), [
            'nama_template' => 'Template Uji',
            'lingkup' => 'Dinas',
            'struktur_perangkat_daerah_id' => 999999,
        ]);

        $resSpd->assertUnprocessable()
            ->assertJsonValidationErrors(['struktur_perangkat_daerah_id']);
    }

    public function test_logged_in_user_scope_locking_for_nasional_and_wilayah(): void
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
        $resDinas = $this->actingAs($adminDinas)->postJson(route('master.template.langkah-kerja.store'), [
            'nama_template' => 'Template Sah Dinas',
            'lingkup' => 'Dinas',
            'struktur_perangkat_daerah_id' => $plot1->id,
        ]);
        $resDinas->assertCreated();

        // Admin Kecamatan berhasil
        $resKecamatan = $this->actingAs($adminKecamatan)->postJson(route('master.template.langkah-kerja.store'), [
            'nama_template' => 'Template Sah Kecamatan',
            'lingkup' => 'Kecamatan',
            'struktur_perangkat_daerah_id' => $plot2->id,
        ]);
        $resKecamatan->assertCreated();
    }

    public function test_can_show_update_duplicate_and_soft_delete_template_langkah_kerja(): void
    {
        $admin = User::factory()->create();
        $plotting = Plotting::query()->create(['plot' => 'WIL I']);

        $template = TemplateLangkahKerja::query()->create([
            'nama_template' => 'Template Langkah Kerja Asli',
            'lingkup' => 'Dinas',
            'struktur_perangkat_daerah_id' => $plotting->id,
            'jumlah_langkah_kerja' => 5,
            'created_by' => $admin->id,
        ]);

        // Show endpoint
        $showResponse = $this->actingAs($admin)->getJson(route('master.template.langkah-kerja.show', $template));
        $showResponse->assertOk()
            ->assertJsonFragment(['nama_template' => 'Template Langkah Kerja Asli'])
            ->assertJsonFragment(['jumlah_langkah_kerja' => 5]);

        // Update endpoint
        $updateResponse = $this->actingAs($admin)->putJson(route('master.template.langkah-kerja.update', $template), [
            'nama_template' => 'Template Langkah Kerja Terupdate',
            'lingkup' => 'Dinas',
            'struktur_perangkat_daerah_id' => $plotting->id,
        ]);

        $updateResponse->assertOk()
            ->assertJsonFragment(['message' => 'Template Langkah Kerja berhasil diperbarui.']);

        $this->assertDatabaseHas('template_langkah_kerjas', [
            'id' => $template->id,
            'nama_template' => 'Template Langkah Kerja Terupdate',
        ]);

        // Duplicate endpoint
        $dupResponse = $this->actingAs($admin)->postJson(route('master.template.langkah-kerja.duplicate', $template));
        $dupResponse->assertCreated()
            ->assertJsonFragment(['message' => 'Template Langkah Kerja berhasil diduplikasi.']);

        $this->assertDatabaseHas('template_langkah_kerjas', [
            'nama_template' => 'Template Langkah Kerja Terupdate (Salinan)',
            'lingkup' => 'Dinas',
            'struktur_perangkat_daerah_id' => $plotting->id,
        ]);

        // Delete endpoint (soft delete)
        $deleteResponse = $this->actingAs($admin)->deleteJson(route('master.template.langkah-kerja.destroy', $template));
        $deleteResponse->assertOk()
            ->assertJsonFragment(['message' => 'Template Langkah Kerja berhasil dihapus.']);

        $this->assertSoftDeleted('template_langkah_kerjas', [
            'id' => $template->id,
        ]);
    }

    public function test_detail_langkah_kerja_crud_bulk_delete_and_counter_sync(): void
    {
        $admin = User::factory()->create();
        $plotting = Plotting::query()->create(['plot' => 'WIL I']);

        $template = TemplateLangkahKerja::query()->create([
            'nama_template' => 'Template Langkah Kerja Uji Detail',
            'lingkup' => 'Dinas',
            'struktur_perangkat_daerah_id' => $plotting->id,
            'jumlah_langkah_kerja' => 0,
            'created_by' => $admin->id,
        ]);

        // 1. Halaman detail langkah kerja
        $pageResponse = $this->actingAs($admin)->get(route('master.template.langkah-kerja.details.index', $template));
        $pageResponse->assertOk()
            ->assertSee('Detail Langkah Kerja')
            ->assertSee('Template Langkah Kerja Uji Detail');

        // 2. Tambah 2 detail langkah kerja dengan TipTap HTML
        $storeRes1 = $this->actingAs($admin)->postJson(
            route('master.template.langkah-kerja.details.store', $template),
            ['uraian' => '<p><strong>Langkah 1:</strong> Lakukan pemeriksaan dokumen kontrak.</p>']
        );
        $storeRes1->assertCreated()
            ->assertJsonFragment(['jumlah_langkah_kerja' => 1]);

        $storeRes2 = $this->actingAs($admin)->postJson(
            route('master.template.langkah-kerja.details.store', $template),
            ['uraian' => '<p><strong>Langkah 2:</strong> Lakukan uji petik fisik lapangan.</p>']
        );
        $storeRes2->assertCreated()
            ->assertJsonFragment(['jumlah_langkah_kerja' => 2]);

        $this->assertEquals(2, $template->fresh()->jumlah_langkah_kerja);

        // 3. DataTable JSON endpoint
        $dtResponse = $this->actingAs($admin)->getJson(
            route('master.template.langkah-kerja.details.index', $template),
            ['X-Requested-With' => 'XMLHttpRequest']
        );
        $dtResponse->assertOk();
        $this->assertEquals(2, $dtResponse->json('recordsTotal'));

        $detail1Id = $storeRes1->json('data.id');
        $detail2Id = $storeRes2->json('data.id');

        // 4. Edit detail langkah kerja
        $updateRes = $this->actingAs($admin)->putJson(
            route('master.template.langkah-kerja.details.update', [$template, $detail1Id]),
            ['uraian' => '<p><strong>Langkah 1 (Revisi):</strong> Pemeriksaan kontrak dan addendum.</p>']
        );
        $updateRes->assertOk()
            ->assertJsonFragment(['message' => 'Uraian langkah kerja berhasil diperbarui.']);

        $this->assertDatabaseHas('template_langkah_kerja_details', [
            'id' => $detail1Id,
            'uraian' => '<p><strong>Langkah 1 (Revisi):</strong> Pemeriksaan kontrak dan addendum.</p>',
        ]);

        // 5. Duplikasi template ikut menggandakan detail langkah kerja
        $dupRes = $this->actingAs($admin)->postJson(route('master.template.langkah-kerja.duplicate', $template));
        $dupRes->assertCreated();
        $copiedId = $dupRes->json('data.id');
        $copiedTemplate = TemplateLangkahKerja::query()->find($copiedId);
        $this->assertNotNull($copiedTemplate);
        $this->assertEquals(2, $copiedTemplate->jumlah_langkah_kerja);
        $this->assertEquals(2, $copiedTemplate->details()->count());

        // 6. Hapus 1 detail langkah kerja -> jumlah_langkah_kerja berkurang menjadi 1
        $delRes = $this->actingAs($admin)->deleteJson(
            route('master.template.langkah-kerja.details.destroy', [$template, $detail1Id])
        );
        $delRes->assertOk()
            ->assertJsonFragment(['jumlah_langkah_kerja' => 1]);
        $this->assertSoftDeleted('template_langkah_kerja_details', ['id' => $detail1Id]);
        $this->assertEquals(1, $template->fresh()->jumlah_langkah_kerja);

        // 7. Tambah 1 lagi lalu Bulk Delete
        $storeRes3 = $this->actingAs($admin)->postJson(
            route('master.template.langkah-kerja.details.store', $template),
            ['uraian' => '<p>Langkah 3: Konfirmasi pihak ketiga.</p>']
        );
        $detail3Id = $storeRes3->json('data.id');
        $this->assertEquals(2, $template->fresh()->jumlah_langkah_kerja);

        $bulkRes = $this->actingAs($admin)->postJson(
            route('master.template.langkah-kerja.details.bulk-destroy', $template),
            ['ids' => [$detail2Id, $detail3Id]]
        );
        $bulkRes->assertOk()
            ->assertJsonFragment(['deleted_count' => 2, 'jumlah_langkah_kerja' => 0]);

        $this->assertEquals(0, $template->fresh()->jumlah_langkah_kerja);
        $this->assertEquals(0, TemplateLangkahKerjaDetail::query()->where('template_langkah_kerja_id', $template->id)->count());
    }
}
