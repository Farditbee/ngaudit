<?php

namespace Tests\Feature;

use App\Models\Kka;
use App\Models\KkaTabulasi;
use App\Models\Kode;
use App\Models\Pka;
use App\Models\Plotting;
use App\Models\Sprin;
use App\Models\StrukturPerangkatDaerah;
use App\Models\SubKode;
use App\Models\Tim;
use App\Models\User;
use Database\Seeders\KodeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TindakLanjutAuditeeTest extends TestCase
{
    use RefreshDatabase;

    private function createKkaWithTabulasi(User $admin): array
    {
        $plotting = Plotting::query()->create(['plot' => 'WIL I']);
        $tim = Tim::query()->create([
            'nama_tim' => 'Tim Audit I',
            'lingkup' => 'Nasional',
            'plotting_id' => $plotting->id,
            'daltu_id' => $admin->id,
            'dalnis_id' => $admin->id,
            'ketua_tim_id' => $admin->id,
            'created_by' => $admin->id,
        ]);

        $sprin = Sprin::query()->create([
            'no_sprin' => 'CONTOH/SPRIN-01-OKTOBER',
            'no_surat_pemberitahuan' => 'SPT/100/X/2026',
            'tgl_mulai' => '2026-10-02',
            'tgl_akhir' => '2026-10-30',
            'tahap' => '1',
            'sprin_tipe' => 'Nasional',
            'plotting_id' => $plotting->id,
            'tim_id' => $tim->id,
            'katim' => $admin->id,
            'dalnis' => $admin->id,
            'daltu' => $admin->id,
            'approved_by_daltu_date' => now(),
            'created_by' => $admin->id,
            'user_id' => $admin->id,
        ]);

        $pka = Pka::query()->create([
            'sprin_id' => $sprin->id,
            'nomor_pka' => 'CONTOH/PKA-01 OKTOBER',
            'tanggal' => '2026-10-03',
            'bidang' => 'Operasional',
            'jenis_audit' => 'Audit Kinerja',
            'sasaran_auditi' => 'Satker Wilayah I',
            'anggaran' => 50000000,
            'status' => 'Final',
            'created_by' => $admin->id,
        ]);

        $kka = Kka::query()->create([
            'nomor_kka' => 'CONTOH/KKA-01 OKTOBER',
            'pka_id' => $pka->id,
            'dibuat_oleh_id' => $admin->id,
            'direviu_oleh_id' => $admin->id,
            'tanggal_dibuat' => '2026-10-05',
            'tanggal_direviu' => '2026-10-05',
            'tanggal_kka' => '2026-10-05',
            'tempat' => 'Jakarta',
            'auditee_level' => 'Nasional',
            'auditee_plotting_id' => $plotting->id,
            'status' => 'Final',
            'created_by' => $admin->id,
        ]);

        $temuan05 = Kode::query()->where('jenis', Kode::JENIS_TEMUAN)->where('kode', '05')->firstOrFail();

        $tabulasi = KkaTabulasi::query()->create([
            'kka_id' => $kka->id,
            'jenis_tabulasi' => 'Temuan',
            'bidang' => 'Operasional',
            'nomor' => '1',
            'kode_temuan_id' => $temuan05->id,
            'nilai_temuan' => 7924000,
            'uraian_temuan' => '<p>05 (pelanggaran disiplin anggaran)</p>',
            'uraian_kriteria' => '<p>1. Pemeliharaan peralatan harus sesuai dengan ketentuan</p>',
            'uraian_sebab' => '<p>1700 (Kelemahan pengawasan dan pengendalian)</p>',
            'uraian_akibat' => '<p>1902 (Berpotensi merugikan keuangan negara)</p>',
            'uraian_rekomendasi' => '<p>2024 (Kasatker meningkatkan pengawasan)</p>',
            'status' => 'Final',
            'created_by' => $admin->id,
        ]);

        return [$kka, $tabulasi];
    }

    public function test_tindak_lanjut_index_and_show_pages_are_displayed(): void
    {
        $admin = User::factory()->create(['role' => 'Super Admin']);
        $this->seed(KodeSeeder::class);
        [$kka] = $this->createKkaWithTabulasi($admin);

        $this->actingAs($admin)->get(route('audit.tindak-lanjut.index'))
            ->assertOk()
            ->assertSee('Tinjut Auditee');

        $this->actingAs($admin)->getJson(route('audit.tindak-lanjut.index'), ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk()
            ->assertJsonPath('data.0.temuan_count', 1)
            ->assertJsonPath('data.0.nomor_kka', 'CONTOH/KKA-01 OKTOBER');

        $this->actingAs($admin)->get(route('audit.tindak-lanjut.show', [$kka, 'tabulasi']))
            ->assertOk()
            ->assertSee('Tanggapan Auditi pada KKA Nomor:')
            ->assertSee('CONTOH/KKA-01 OKTOBER')
            ->assertSee('CONTOH/SPRIN-01-OKTOBER')
            ->assertSee('CONTOH/PKA-01 OKTOBER')
            ->assertSee('Cetak PTL')
            ->assertSee('Tabulasi Temuan (1)')
            ->assertSee('Tabulasi Konsultasi (0)')
            ->assertSee('Temuan Positif (0)')
            ->assertSee('Tampilkan Temuan Selesai');
    }

    public function test_user_can_submit_tanggapan_uji_nilai_messages_and_upload_lampiran(): void
    {
        Storage::fake('public');
        $auditor = User::factory()->create(['role' => 'Super Admin']);
        $auditee = User::factory()->create(['role' => 'Auditee']);
        $this->seed(KodeSeeder::class);
        [$kka, $tabulasi] = $this->createKkaWithTabulasi($auditor);

        $kodeTinjut = Kode::query()->where('jenis', Kode::JENIS_TINJUT)->where('kode', '01')->firstOrFail();
        $subTinjut = SubKode::query()->where('kode_id', $kodeTinjut->id)->firstOrFail();

        // 1. Auditor cannot submit Uji Nilai before Auditee submits tindak_lanjut
        $this->actingAs($auditor)->postJson(route('audit.tindak-lanjut.uji-nilai', [$kka, $tabulasi]), [
            'status_tinjut' => 'Selesai',
            'kesimpulan_tinjut' => 'Sesuai Rekomendasi',
            'alasan_tinjut' => 'Coba isi sebelum ditinjut',
        ])->assertStatus(422);

        // 2. Auditee submits Tindak Lanjut (Informasi tab) with formatted Rupiah nilai_pengembalian
        $this->actingAs($auditee)->postJson(route('audit.tindak-lanjut.tanggapan', [$kka, $tabulasi]), [
            'kode_tinjut_id' => $kodeTinjut->id,
            'sub_kode_tinjut_id' => $subTinjut->id,
            'tindak_lanjut' => "<p>({$kodeTinjut->kode}) - {$kodeTinjut->nama}</p>",
            'nilai_pengembalian' => '7.924.000',
        ])->assertOk()
            ->assertJsonPath('data.status_tinjut', 'Dalam Proses')
            ->assertJsonPath('data.has_tinjut', true)
            ->assertJsonPath('data.nilai_pengembalian', '7924000');

        $this->assertDatabaseHas('kka_tabulasis', [
            'id' => $tabulasi->id,
            'status_tinjut' => 'Dalam Proses',
            'kode_tinjut_id' => $kodeTinjut->id,
            'sub_kode_tinjut_id' => $subTinjut->id,
            'nilai_pengembalian' => 7924000,
        ]);

        // 3. Auditee cannot submit Uji Nilai even after tindak_lanjut is filled
        $this->actingAs($auditee)->postJson(route('audit.tindak-lanjut.uji-nilai', [$kka, $tabulasi]), [
            'status_tinjut' => 'Selesai',
            'kesimpulan_tinjut' => 'Sesuai Rekomendasi',
            'alasan_tinjut' => 'Auditee mencoba uji nilai',
        ])->assertForbidden();

        // 4. Auditor can now submit Uji Nilai after Auditee has submitted tindak_lanjut
        $this->actingAs($auditor)->postJson(route('audit.tindak-lanjut.uji-nilai', [$kka, $tabulasi]), [
            'status_tinjut' => 'Selesai',
            'kesimpulan_tinjut' => 'Sesuai Rekomendasi',
            'alasan_tinjut' => 'Bukti setor NTPN sudah lengkap.',
        ])->assertOk()
            ->assertJsonPath('data.status_tinjut', 'Selesai')
            ->assertJsonPath('data.kesimpulan_tinjut', 'Sesuai Rekomendasi');

        // 5. Conversation message with optional file
        $chatFile = UploadedFile::fake()->create('chat-doc.pdf', 80, 'application/pdf');
        $this->actingAs($auditee)->post(route('audit.tindak-lanjut.messages.store', [$kka, $tabulasi]), [
            'pesan' => 'Mohon dicek bukti setor terlampir.',
            'file' => $chatFile,
        ], ['Accept' => 'application/json'])
            ->assertCreated()
            ->assertJsonPath('data.messages.0.pesan', 'Mohon dicek bukti setor terlampir.')
            ->assertJsonPath('data.messages.0.file_name', 'chat-doc.pdf');

        // 6. Upload Lampiran (File & Link)
        $file = UploadedFile::fake()->create('bukti-tinjut.pdf', 120, 'application/pdf');
        $this->actingAs($auditee)->post(route('audit.tindak-lanjut.lampiran', [$kka, $tabulasi]), [
            'jenis_unggahan' => 'file',
            'lampiran' => $file,
            'keterangan' => 'Bukti setor kas negara NTPN',
        ], ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJsonPath('data.lampirans.0.file_name', 'bukti-tinjut.pdf')
            ->assertJsonPath('data.lampirans.0.keterangan', 'Bukti setor kas negara NTPN');

        $this->actingAs($auditee)->postJson(route('audit.tindak-lanjut.lampiran', [$kka, $tabulasi]), [
            'jenis_unggahan' => 'link',
            'link_url' => 'https://drive.google.com/drive/folders/example123',
            'keterangan' => 'Folder dokumentasi foto pemeliharaan',
        ])->assertOk()
            ->assertJsonPath('data.lampirans.0.jenis_unggahan', 'link')
            ->assertJsonPath('data.lampirans.0.url', 'https://drive.google.com/drive/folders/example123');
    }

    public function test_draft_kka_does_not_appear_in_tindak_lanjut_and_cannot_be_accessed(): void
    {
        $admin = User::factory()->create(['role' => 'Super Admin']);
        $this->seed(KodeSeeder::class);
        [$kka] = $this->createKkaWithTabulasi($admin);

        $kka->update(['status' => 'Draft']);

        $response = $this->actingAs($admin)->getJson(route('audit.tindak-lanjut.index'), ['X-Requested-With' => 'XMLHttpRequest']);
        $response->assertOk();
        $this->assertCount(0, $response->json('data'));

        $this->actingAs($admin)->get(route('audit.tindak-lanjut.show', [$kka, 'tabulasi']))
            ->assertForbidden();
    }

    public function test_only_assigned_auditor_can_access_kka_in_tindak_lanjut(): void
    {
        $admin = User::factory()->create(['role' => 'Super Admin']);
        $assignedAuditor = User::factory()->create(['role' => 'Auditor']);
        $unassignedAuditor = User::factory()->create(['role' => 'Auditor']);
        $this->seed(KodeSeeder::class);

        [$kka] = $this->createKkaWithTabulasi($admin);
        $kka->update([
            'dibuat_oleh_id' => $assignedAuditor->id,
            'status' => 'Final',
        ]);

        // Assigned auditor can view in datatable and access show
        $response = $this->actingAs($assignedAuditor)->getJson(route('audit.tindak-lanjut.index'), ['X-Requested-With' => 'XMLHttpRequest']);
        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
        $this->actingAs($assignedAuditor)->get(route('audit.tindak-lanjut.show', [$kka, 'tabulasi']))->assertOk();

        // Unassigned auditor cannot see in datatable and gets 403 on show
        $responseUnassigned = $this->actingAs($unassignedAuditor)->getJson(route('audit.tindak-lanjut.index'), ['X-Requested-With' => 'XMLHttpRequest']);
        $responseUnassigned->assertOk();
        $this->assertCount(0, $responseUnassigned->json('data'));
        $this->actingAs($unassignedAuditor)->get(route('audit.tindak-lanjut.show', [$kka, 'tabulasi']))->assertForbidden();
    }

    public function test_auditee_with_matching_level_and_hierarchy_can_access_kka_in_tindak_lanjut(): void
    {
        $admin = User::factory()->create(['role' => 'Super Admin']);
        $this->seed(KodeSeeder::class);

        $dinasDishub = StrukturPerangkatDaerah::query()->create([
            'nama_unit' => 'Dinas Perhubungan',
            'level' => 1,
            'jenis_unit' => 'Dinas',
        ]);

        $uptDishub = StrukturPerangkatDaerah::query()->create([
            'parent_id' => $dinasDishub->id,
            'nama_unit' => 'UPT Dinas Perhubungan',
            'level' => 2,
            'jenis_unit' => 'UPTD',
        ]);

        $dinasDinkes = StrukturPerangkatDaerah::query()->create([
            'nama_unit' => 'Dinas Kesehatan',
            'level' => 1,
            'jenis_unit' => 'Dinas',
        ]);

        [$kka] = $this->createKkaWithTabulasi($admin);
        $kka->update([
            'auditee_level' => 'Dinas',
            'struktur_perangkat_daerah_id' => $uptDishub->id,
            'status' => 'Final',
        ]);

        // 1. Auditee at parent Dinas (Dinas Perhubungan) CANNOT see KKA for UPT (must be same exact unit)
        $auditeeDishub = User::factory()->create([
            'role' => 'Auditee',
            'level' => 'Dinas',
            'struktur_perangkat_daerah_id' => $dinasDishub->id,
        ]);
        $responseDishub = $this->actingAs($auditeeDishub)->getJson(route('audit.tindak-lanjut.index'), ['X-Requested-With' => 'XMLHttpRequest']);
        $responseDishub->assertOk();
        $this->assertCount(0, $responseDishub->json('data'));
        $this->actingAs($auditeeDishub)->get(route('audit.tindak-lanjut.show', [$kka, 'tabulasi']))->assertForbidden();

        // 2. Auditee at exact child unit (UPT Dinas Perhubungan) CAN see KKA
        $auditeeUpt = User::factory()->create([
            'role' => 'Auditee',
            'level' => 'Dinas',
            'struktur_perangkat_daerah_id' => $uptDishub->id,
        ]);
        $responseUpt = $this->actingAs($auditeeUpt)->getJson(route('audit.tindak-lanjut.index'), ['X-Requested-With' => 'XMLHttpRequest']);
        $responseUpt->assertOk();
        $this->assertCount(1, $responseUpt->json('data'));
        $this->actingAs($auditeeUpt)->get(route('audit.tindak-lanjut.show', [$kka, 'tabulasi']))->assertOk();

        // 3. Auditee at different Dinas (Dinas Kesehatan) cannot see KKA
        $auditeeDinkes = User::factory()->create([
            'role' => 'Auditee',
            'level' => 'Dinas',
            'struktur_perangkat_daerah_id' => $dinasDinkes->id,
        ]);
        $responseDinkes = $this->actingAs($auditeeDinkes)->getJson(route('audit.tindak-lanjut.index'), ['X-Requested-With' => 'XMLHttpRequest']);
        $responseDinkes->assertOk();
        $this->assertCount(0, $responseDinkes->json('data'));
        $this->actingAs($auditeeDinkes)->get(route('audit.tindak-lanjut.show', [$kka, 'tabulasi']))->assertForbidden();

        // 4. Auditee with different level (Kecamatan) cannot see KKA
        $auditeeKec = User::factory()->create([
            'role' => 'Auditee',
            'level' => 'Kecamatan',
        ]);
        $responseKec = $this->actingAs($auditeeKec)->getJson(route('audit.tindak-lanjut.index'), ['X-Requested-With' => 'XMLHttpRequest']);
        $responseKec->assertOk();
        $this->assertCount(0, $responseKec->json('data'));
        $this->actingAs($auditeeKec)->get(route('audit.tindak-lanjut.show', [$kka, 'tabulasi']))->assertForbidden();

        // 5. Auditee with same unit but DIFFERENT bidang cannot see KKA
        $auditeeDiffBidang = User::factory()->create([
            'role' => 'Auditee',
            'level' => 'Dinas',
            'struktur_perangkat_daerah_id' => $uptDishub->id,
            'bidang' => 'Sumber Daya Manusia',
        ]);
        $responseDiffBidang = $this->actingAs($auditeeDiffBidang)->getJson(route('audit.tindak-lanjut.index'), ['X-Requested-With' => 'XMLHttpRequest']);
        $responseDiffBidang->assertOk();
        $this->assertCount(0, $responseDiffBidang->json('data'));
        $this->actingAs($auditeeDiffBidang)->get(route('audit.tindak-lanjut.show', [$kka, 'tabulasi']))->assertForbidden();

        // 6. Auditee with same unit and SAME bidang CAN see KKA
        $auditeeSameBidang = User::factory()->create([
            'role' => 'Auditee',
            'level' => 'Dinas',
            'struktur_perangkat_daerah_id' => $uptDishub->id,
            'bidang' => 'Operasional',
        ]);
        $responseSameBidang = $this->actingAs($auditeeSameBidang)->getJson(route('audit.tindak-lanjut.index'), ['X-Requested-With' => 'XMLHttpRequest']);
        $responseSameBidang->assertOk();
        $this->assertCount(1, $responseSameBidang->json('data'));
        $this->actingAs($auditeeSameBidang)->get(route('audit.tindak-lanjut.show', [$kka, 'tabulasi']))->assertOk();
    }
}
