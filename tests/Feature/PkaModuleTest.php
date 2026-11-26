<?php

namespace Tests\Feature;

use App\Models\Pka;
use App\Models\Plotting;
use App\Models\Sprin;
use App\Models\TemplatePka;
use App\Models\Tim;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PkaModuleTest extends TestCase
{
    use RefreshDatabase;

    protected function createTim(User $admin, Plotting $plotting): Tim
    {
        return Tim::query()->create([
            'nama_tim' => 'Tim Audit '.uniqid(),
            'lingkup' => 'Nasional',
            'plotting_id' => $plotting->id,
            'daltu_id' => $admin->id,
            'dalnis_id' => $admin->id,
            'ketua_tim_id' => $admin->id,
            'created_by' => $admin->id,
        ]);
    }

    protected function createSprin(User $admin, Tim $tim, Plotting $plotting, bool $approved = true): Sprin
    {
        return Sprin::query()->create([
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
            'approved_by_daltu_date' => $approved ? now() : null,
            'created_by' => $admin->id,
            'user_id' => $admin->id,
        ]);
    }

    public function test_pka_page_is_displayed_for_authenticated_user(): void
    {
        $admin = User::factory()->create();

        $response = $this->actingAs($admin)->get(route('audit.pka.index'));

        $response->assertOk();
        $response->assertSee('Program Kerja Audit (PKA)');
        $response->assertSee('Tambah PKA');
    }

    public function test_pka_datatable_json_endpoint(): void
    {
        $admin = User::factory()->create();
        $plotting = Plotting::query()->create(['plot' => 'WIL I']);
        $tim = $this->createTim($admin, $plotting);
        $sprin = $this->createSprin($admin, $tim, $plotting);

        Pka::query()->create([
            'sprin_id' => $sprin->id,
            'nomor_pka' => 'PKA/001/WIL-I/2026',
            'tanggal' => now()->toDateString(),
            'anggaran' => 150000000,
            'bidang' => 'Operasional',
            'jenis_audit' => 'Audit Kinerja',
            'sasaran_auditi' => 'Balai Wilayah Sungai Wilayah I',
            'created_by' => $admin->id,
        ]);

        $response = $this->actingAs($admin)
            ->getJson(route('audit.pka.index'), ['X-Requested-With' => 'XMLHttpRequest']);

        $response->assertOk()
            ->assertJsonStructure(['draw', 'recordsTotal', 'recordsFiltered', 'data']);

        $this->assertEquals(1, $response->json('recordsTotal'));
        $this->assertEquals('PKA/001/WIL-I/2026', $response->json('data.0.nomor_pka'));
        $this->assertEquals($sprin->no_sprin, $response->json('data.0.no_sprin'));
    }

    public function test_can_create_pka_with_file_upload_and_tiptap_rich_text_sections(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create();
        $plotting = Plotting::query()->create(['plot' => 'WIL I']);
        $tim = $this->createTim($admin, $plotting);
        $sprin = $this->createSprin($admin, $tim, $plotting);

        $template = TemplatePka::query()->create([
            'nama_template' => 'Template Standar Operasional',
            'lingkup' => 'Nasional',
            'plotting_id' => $plotting->id,
            'pendahuluan' => '<p>Pendahuluan dari template.</p>',
            'created_by' => $admin->id,
        ]);

        $file = UploadedFile::fake()->create('dokumen_lampiran_pka.pdf', 500, 'application/pdf');

        $payload = [
            'sprin_id' => $sprin->id,
            'nomor_pka' => 'PKA/100/OPR/2026',
            'tanggal' => now()->toDateString(),
            'anggaran' => 75000000,
            'bidang' => 'Operasional',
            'jenis_audit' => 'Audit Kinerja',
            'sasaran_auditi' => 'Dinas Pekerjaan Umum',
            'status' => 'Draft',
            'template_pka_id' => $template->id,
            'lampiran' => $file,
            'pendahuluan' => '<p><strong>10. Pendahuluan</strong> audit operasional dinas.</p>',
            'tujuan' => '<p>11. Tujuan audit kinerja.</p>',
            'ruang_lingkup' => '<p>12. Ruang lingkup seluruh kegiatan 2026.</p>',
            'sasaran' => '<p>13. Sasaran kepatuhan dan kehematan.</p>',
            'waktu_pelaksanaan' => '<p>14. Waktu 20 hari kerja.</p>',
            'periode' => '<p>15. Periode TA 2026.</p>',
            'penutup' => '<p>16. Penutup PKA.</p>',
        ];

        $response = $this->actingAs($admin)->postJson(route('audit.pka.store'), $payload);

        $response->assertCreated()
            ->assertJsonFragment(['message' => 'Program Kerja Audit (PKA) berhasil ditambahkan.']);

        $this->assertDatabaseHas('pkas', [
            'sprin_id' => $sprin->id,
            'nomor_pka' => 'PKA/100/OPR/2026',
            'bidang' => 'Operasional',
            'jenis_audit' => 'Audit Kinerja',
            'status' => 'Draft',
            'template_pka_id' => $template->id,
            'lampiran_name' => 'dokumen_lampiran_pka.pdf',
            'created_by' => $admin->id,
        ]);

        $created = Pka::query()->where('nomor_pka', 'PKA/100/OPR/2026')->first();
        $this->assertNotNull($created);
        $this->assertNotNull($created->lampiran_path);
        Storage::disk('public')->assertExists($created->lampiran_path);
        $this->assertEquals('<p><strong>10. Pendahuluan</strong> audit operasional dinas.</p>', $created->pendahuluan);
    }

    public function test_validation_rules_for_pka(): void
    {
        $admin = User::factory()->create();

        $response = $this->actingAs($admin)->postJson(route('audit.pka.store'), []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors([
                'sprin_id',
                'nomor_pka',
                'tanggal',
                'anggaran',
                'bidang',
                'jenis_audit',
                'sasaran_auditi',
                'status',
            ]);
    }

    public function test_can_show_update_and_delete_pka(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create();
        $plotting = Plotting::query()->create(['plot' => 'WIL I']);
        $tim = $this->createTim($admin, $plotting);
        $sprin = $this->createSprin($admin, $tim, $plotting);

        $pka = Pka::query()->create([
            'sprin_id' => $sprin->id,
            'nomor_pka' => 'PKA/200/A/2026',
            'tanggal' => now()->toDateString(),
            'anggaran' => 50000000,
            'bidang' => 'Logistik',
            'jenis_audit' => 'Audit PNBP',
            'sasaran_auditi' => 'Balai Logistik',
            'status' => 'Draft',
            'pendahuluan' => '<p>Pendahuluan lama</p>',
            'created_by' => $admin->id,
        ]);

        // Show endpoint
        $showResponse = $this->actingAs($admin)->getJson(route('audit.pka.show', $pka));
        $showResponse->assertOk()
            ->assertJsonFragment(['nomor_pka' => 'PKA/200/A/2026'])
            ->assertJsonFragment(['bidang' => 'Logistik'])
            ->assertJsonFragment(['status' => 'Draft'])
            ->assertJsonFragment(['pendahuluan' => '<p>Pendahuluan lama</p>']);

        // Update endpoint
        $newFile = UploadedFile::fake()->create('revisi_lampiran.pdf', 300, 'application/pdf');

        $updateResponse = $this->actingAs($admin)->postJson(route('audit.pka.update', $pka), [
            'sprin_id' => $sprin->id,
            'nomor_pka' => 'PKA/200/REVISI/2026',
            'tanggal' => now()->toDateString(),
            'anggaran' => 60000000,
            'bidang' => 'Logistik',
            'jenis_audit' => 'Audit PNBP',
            'sasaran_auditi' => 'Balai Logistik Terpadu',
            'status' => 'Final',
            'lampiran' => $newFile,
            'pendahuluan' => '<p>Pendahuluan yang diperbarui.</p>',
        ]);

        $updateResponse->assertOk()
            ->assertJsonFragment(['message' => 'Program Kerja Audit (PKA) berhasil diperbarui.']);

        $this->assertDatabaseHas('pkas', [
            'id' => $pka->id,
            'nomor_pka' => 'PKA/200/REVISI/2026',
            'anggaran' => 60000000,
            'sasaran_auditi' => 'Balai Logistik Terpadu',
            'status' => 'Final',
            'lampiran_name' => 'revisi_lampiran.pdf',
        ]);

        // Delete endpoint (soft delete)
        $deleteResponse = $this->actingAs($admin)->deleteJson(route('audit.pka.destroy', $pka));
        $deleteResponse->assertOk()
            ->assertJsonFragment(['message' => 'Program Kerja Audit (PKA) berhasil dihapus.']);

        $this->assertSoftDeleted('pkas', [
            'id' => $pka->id,
        ]);
    }

    public function test_only_approved_sprins_are_available_for_pka_creation(): void
    {
        $admin = User::factory()->create();
        $plotting = Plotting::query()->create(['plot' => 'WIL I']);
        $tim = $this->createTim($admin, $plotting);

        // 1. Sprin belum diapprove (approved_by_daltu_date = null)
        $sprinBelumApprove = $this->createSprin($admin, $tim, $plotting, approved: false);

        // 2. Sprin direviu dengan status Koreksi (approved_by_daltu_date = null)
        $sprinKoreksi = $this->createSprin($admin, $tim, $plotting, approved: false);
        $sprinKoreksi->reviews()->create([
            'user_id' => $admin->id,
            'status' => 'Koreksi',
            'catatan' => 'Perbaiki lampiran',
        ]);

        // 3. Sprin sudah disetujui (approved_by_daltu_date != null)
        $sprinDisetujui = $this->createSprin($admin, $tim, $plotting, approved: true);
        $sprinDisetujui->reviews()->create([
            'user_id' => $admin->id,
            'status' => 'Disetujui',
            'catatan' => 'Disetujui',
        ]);

        // Pada halaman pembuatan PKA, hanya sprin disetujui yang dimuat di dropdown
        $response = $this->actingAs($admin)->get(route('audit.pka.index'));
        $response->assertOk();

        $sprins = $response->viewData('sprins');
        $this->assertCount(1, $sprins);
        $this->assertSame($sprinDisetujui->id, $sprins[0]['id']);

        // Mencoba menyimpan PKA dengan Sprin yang belum diapprove -> Gagal validasi
        $this->actingAs($admin)
            ->postJson(route('audit.pka.store'), [
                'sprin_id' => $sprinBelumApprove->id,
                'nomor_pka' => 'PKA/TEST/001',
                'tanggal' => now()->toDateString(),
                'anggaran' => 10000000,
                'bidang' => 'Operasional',
                'jenis_audit' => 'Audit Kinerja',
                'sasaran_auditi' => 'Satker Uji',
                'status' => 'Draft',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['sprin_id']);

        // Mencoba menyimpan PKA dengan Sprin status Koreksi -> Gagal validasi
        $this->actingAs($admin)
            ->postJson(route('audit.pka.store'), [
                'sprin_id' => $sprinKoreksi->id,
                'nomor_pka' => 'PKA/TEST/002',
                'tanggal' => now()->toDateString(),
                'anggaran' => 10000000,
                'bidang' => 'Operasional',
                'jenis_audit' => 'Audit Kinerja',
                'sasaran_auditi' => 'Satker Uji',
                'status' => 'Draft',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['sprin_id']);

        // Menyimpan PKA dengan Sprin yang Disetujui -> Berhasil
        $this->actingAs($admin)
            ->postJson(route('audit.pka.store'), [
                'sprin_id' => $sprinDisetujui->id,
                'nomor_pka' => 'PKA/TEST/003',
                'tanggal' => now()->toDateString(),
                'anggaran' => 10000000,
                'bidang' => 'Operasional',
                'jenis_audit' => 'Audit Kinerja',
                'sasaran_auditi' => 'Satker Uji',
                'status' => 'Draft',
            ])
            ->assertCreated();
    }

    public function test_pka_can_be_duplicated(): void
    {
        $admin = User::factory()->create();
        $plotting = Plotting::query()->create(['plot' => 'WIL I']);
        $tim = $this->createTim($admin, $plotting);
        $sprin = $this->createSprin($admin, $tim, $plotting);

        $originalPka = Pka::query()->create([
            'sprin_id' => $sprin->id,
            'nomor_pka' => 'PKA/ORIGINAL/2026',
            'tanggal' => now()->toDateString(),
            'anggaran' => 50000000,
            'bidang' => 'Investigasi',
            'jenis_audit' => 'Audit Kinerja',
            'sasaran_auditi' => 'Satker Original',
            'status' => 'Final',
            'pendahuluan' => '<p>Pendahuluan dokumen asli</p>',
            'created_by' => $admin->id,
        ]);

        $response = $this->actingAs($admin)->postJson(route('audit.pka.duplicate', $originalPka), [
            'nomor_pka' => 'PKA/DUPLICATE/2026',
        ]);

        $response->assertCreated();
        $response->assertJson([
            'message' => 'Program Kerja Audit (PKA) berhasil diduplikasi.',
        ]);

        $this->assertDatabaseHas('pkas', [
            'nomor_pka' => 'PKA/DUPLICATE/2026',
            'sprin_id' => $sprin->id,
            'bidang' => 'Investigasi',
            'jenis_audit' => 'Audit Kinerja',
            'sasaran_auditi' => 'Satker Original',
            'status' => 'Draft',
            'pendahuluan' => '<p>Pendahuluan dokumen asli</p>',
            'created_by' => $admin->id,
        ]);
    }

    public function test_pka_status_can_be_toggled_directly(): void
    {
        $admin = User::factory()->create();
        $plotting = Plotting::query()->create(['plot' => 'WIL I']);
        $tim = $this->createTim($admin, $plotting);
        $sprin = $this->createSprin($admin, $tim, $plotting);

        $pka = Pka::query()->create([
            'sprin_id' => $sprin->id,
            'nomor_pka' => 'PKA/STATUS/2026',
            'tanggal' => now()->toDateString(),
            'anggaran' => 20000000,
            'bidang' => 'Operasional',
            'jenis_audit' => 'Audit Kinerja',
            'sasaran_auditi' => 'Satker Status',
            'status' => 'Draft',
            'created_by' => $admin->id,
        ]);

        // Toggle from Draft to Final
        $response = $this->actingAs($admin)->postJson(route('audit.pka.update-status', $pka), [
            'status' => 'Final',
        ]);

        $response->assertOk();
        $response->assertJson([
            'message' => "Status Dokumen PKA {$pka->nomor_pka} berhasil diubah menjadi Final.",
            'status' => 'Final',
        ]);

        $this->assertEquals('Final', $pka->fresh()->status);

        // Toggle from Final to Draft
        $response2 = $this->actingAs($admin)->postJson(route('audit.pka.update-status', $pka), [
            'status' => 'Draft',
        ]);

        $response2->assertOk();
        $response2->assertJson([
            'message' => "Status Dokumen PKA {$pka->nomor_pka} berhasil diubah menjadi Draft.",
            'status' => 'Draft',
        ]);

        $this->assertEquals('Draft', $pka->fresh()->status);
    }

    public function test_can_update_pka_with_zero_anggaran_and_empty_template(): void
    {
        $admin = User::factory()->create();
        $plotting = Plotting::query()->create(['plot' => 'WIL I']);
        $tim = $this->createTim($admin, $plotting);
        $sprin = $this->createSprin($admin, $tim, $plotting);

        $pka = Pka::query()->create([
            'sprin_id' => $sprin->id,
            'nomor_pka' => 'PKA/ZERO/2026',
            'tanggal' => now()->toDateString(),
            'anggaran' => 0,
            'bidang' => 'Operasional',
            'jenis_audit' => 'Audit Kinerja',
            'sasaran_auditi' => 'Auditi Nol',
            'status' => 'Draft',
            'created_by' => $admin->id,
        ]);

        $response = $this->actingAs($admin)->postJson(route('audit.pka.update', $pka), [
            'sprin_id' => $sprin->id,
            'nomor_pka' => 'PKA/ZERO/REVISED/2026',
            'tanggal' => now()->toDateString(),
            'anggaran' => '0',
            'bidang' => 'Operasional',
            'jenis_audit' => 'Audit Kinerja',
            'sasaran_auditi' => 'Auditi Nol Update',
            'template_pka_id' => '',
            'status' => 'Draft',
        ]);

        $response->assertOk()
            ->assertJsonFragment(['message' => 'Program Kerja Audit (PKA) berhasil diperbarui.']);

        $this->assertDatabaseHas('pkas', [
            'id' => $pka->id,
            'nomor_pka' => 'PKA/ZERO/REVISED/2026',
            'anggaran' => 0,
            'template_pka_id' => null,
        ]);
    }
}
