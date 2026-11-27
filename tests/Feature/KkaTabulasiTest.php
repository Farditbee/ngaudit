<?php

namespace Tests\Feature;

use App\Models\Kka;
use App\Models\Kode;
use App\Models\Kriteria;
use App\Models\Pka;
use App\Models\Plotting;
use App\Models\Sprin;
use App\Models\SubKode;
use App\Models\Tim;
use App\Models\User;
use Database\Seeders\KodeSeeder;
use Database\Seeders\KriteriaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KkaTabulasiTest extends TestCase
{
    use RefreshDatabase;

    private function createKka(User $admin): Kka
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
            'no_sprin' => 'Sprin/100/XI/2026',
            'no_surat_pemberitahuan' => 'SPT/100/XI/2026',
            'tgl_mulai' => '2026-11-02',
            'tgl_akhir' => '2026-11-30',
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
            'nomor_pka' => 'PKA-01/XI/2026',
            'tanggal' => '2026-11-03',
            'bidang' => 'Operasional',
            'jenis_audit' => 'Audit Kinerja',
            'sasaran_auditi' => 'Satker Wilayah I',
            'anggaran' => 50000000,
            'status' => 'Final',
            'created_by' => $admin->id,
        ]);

        return Kka::query()->create([
            'nomor_kka' => 'KKA-01/XI/2026',
            'pka_id' => $pka->id,
            'dibuat_oleh_id' => $admin->id,
            'direviu_oleh_id' => $admin->id,
            'tanggal_dibuat' => '2026-11-05',
            'tanggal_direviu' => '2026-11-05',
            'tanggal_kka' => '2026-11-05',
            'tempat' => 'Jakarta',
            'auditee_level' => 'Nasional',
            'auditee_plotting_id' => $plotting->id,
            'status' => 'Draft',
            'created_by' => $admin->id,
        ]);
    }

    public function test_kka_tabulasi_konsultasi_and_positif_pages_are_displayed(): void
    {
        $admin = User::factory()->create(['role' => 'Super Admin']);
        $this->seed([KodeSeeder::class, KriteriaSeeder::class]);
        $kka = $this->createKka($admin);

        $this->actingAs($admin)->get(route('audit.tabulasi.index'))
            ->assertOk()
            ->assertSee('Temuan')
            ->assertSee('Konsultasi')
            ->assertSee('Positif');

        $this->actingAs($admin)->get(route('audit.kka.tabulasi.index', [$kka, 'tabulasi']))
            ->assertOk()
            ->assertSee('Tabulasi Temuan')
            ->assertSee('Tambah Tabulasi Temuan')
            ->assertSee('KKA-01/XI/2026');

        $this->actingAs($admin)->get(route('audit.kka.tabulasi.index', [$kka, 'konsultasi']))
            ->assertOk()
            ->assertSee('Tabulasi Konsultasi')
            ->assertSee('Uraian Permasalahan');

        $this->actingAs($admin)->get(route('audit.kka.tabulasi.index', [$kka, 'positif']))
            ->assertOk()
            ->assertSee('Tabulasi Positif')
            ->assertSee('Deskripsi');
    }

    public function test_user_can_create_update_toggle_status_and_delete_kka_tabulasi(): void
    {
        $admin = User::factory()->create(['role' => 'Super Admin']);
        $this->seed([KodeSeeder::class, KriteriaSeeder::class]);
        $kka = $this->createKka($admin);

        $temuan01 = Kode::query()->where('jenis', Kode::JENIS_TEMUAN)->where('kode', '01')->firstOrFail();
        $subTemuan = SubKode::query()->where('kode_id', $temuan01->id)->firstOrFail();
        $sebab = Kode::query()->where('jenis', Kode::JENIS_SEBAB)->where('kode_temuan_id', $temuan01->id)->firstOrFail();
        $subSebab = SubKode::query()->where('kode_id', $sebab->id)->firstOrFail();
        $akibat = Kode::query()->where('jenis', Kode::JENIS_AKIBAT)->where('kode_temuan_id', $temuan01->id)->firstOrFail();
        $subAkibat = SubKode::query()->where('kode_id', $akibat->id)->firstOrFail();
        $rekom = Kode::query()->where('jenis', Kode::JENIS_REKOMENDASI)->where('kode_temuan_id', $temuan01->id)->firstOrFail();
        $subRekom = SubKode::query()->where('kode_id', $rekom->id)->firstOrFail();
        $kriteria = Kriteria::query()->firstOrFail();

        $store = $this->actingAs($admin)->postJson(route('audit.kka.tabulasi.store', $kka), [
            'jenis_tabulasi' => 'Temuan',
            'nomor' => '01',
            'tipe_temuan' => 'Baru',
            'kode_temuan_id' => $temuan01->id,
            'sub_kode_temuan_id' => $subTemuan->id,
            'nilai_temuan' => '125.000.000',
            'uraian_temuan' => "<p>({$temuan01->kode}) - {$temuan01->nama}</p><p>({$subTemuan->kode}) - {$subTemuan->nama}</p>",
            'kriteria_id' => $kriteria->id,
            'uraian_kriteria' => "<p>{$kriteria->nama}</p>",
            'kode_sebab_id' => $sebab->id,
            'sub_kode_sebab_id' => $subSebab->id,
            'uraian_sebab' => "<p>({$sebab->kode}) - {$sebab->nama}</p>",
            'kode_akibat_id' => $akibat->id,
            'sub_kode_akibat_id' => $subAkibat->id,
            'uraian_akibat' => "<p>({$akibat->kode}) - {$akibat->nama}</p>",
            'kode_rekomendasi_id' => $rekom->id,
            'sub_kode_rekomendasi_id' => $subRekom->id,
            'uraian_rekomendasi' => "<p>({$rekom->kode}) - {$rekom->nama}</p>",
            'auditor_id' => $admin->id,
            'status' => 'Draft',
        ])->assertCreated();

        $tabulasiId = $store->json('data.id');

        $this->assertDatabaseHas('kka_tabulasis', [
            'id' => $tabulasiId,
            'kka_id' => $kka->id,
            'jenis_tabulasi' => 'Temuan',
            'bidang' => 'Operasional',
            'nomor' => '01',
            'tipe_temuan' => 'Baru',
            'kode_temuan_id' => $temuan01->id,
            'nilai_temuan' => 125000000,
            'status' => 'Draft',
        ]);

        // Cek tabulasi_count di KKA DataTable naik menjadi 1
        $this->actingAs($admin)->getJson(route('audit.kka.index'), ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk()
            ->assertJsonPath('data.0.tabulasi_count', 1);

        // Jika ganti kode temuan ke 04 (bukan 01, 02, 03, 05), nilai_temuan otomatis jadi null
        $temuan04 = Kode::query()->where('jenis', Kode::JENIS_TEMUAN)->where('kode', '04')->firstOrFail();

        $this->actingAs($admin)->putJson(route('audit.kka.tabulasi.update', [$kka, $tabulasiId]), [
            'jenis_tabulasi' => 'Konsultasi',
            'nomor' => '02',
            'tipe_temuan' => 'Berulang',
            'kode_temuan_id' => $temuan04->id,
            'nilai_temuan' => 999999,
            'status' => 'Draft',
        ])->assertOk();

        $this->assertDatabaseHas('kka_tabulasis', [
            'id' => $tabulasiId,
            'jenis_tabulasi' => 'Konsultasi',
            'nomor' => '02',
            'kode_temuan_id' => $temuan04->id,
            'nilai_temuan' => null,
        ]);

        // Jika diubah menjadi Konsultasi, tabulasi_count di KKA DataTable hanya menghitung Temuan (jadi 0)
        $this->actingAs($admin)->getJson(route('audit.kka.index'), ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk()
            ->assertJsonPath('data.0.tabulasi_count', 0);

        // Toggle status ke Final
        $this->actingAs($admin)->postJson(route('audit.kka.tabulasi.update-status', [$kka, $tabulasiId]), [
            'status' => 'Final',
        ])->assertOk()->assertJsonPath('status', 'Final');

        // Delete
        $this->actingAs($admin)->deleteJson(route('audit.kka.tabulasi.destroy', [$kka, $tabulasiId]))
            ->assertOk();

        $this->assertSoftDeleted('kka_tabulasis', ['id' => $tabulasiId]);
    }
}
