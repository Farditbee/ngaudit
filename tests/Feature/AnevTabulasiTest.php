<?php

namespace Tests\Feature;

use App\Models\Kka;
use App\Models\KkaTabulasi;
use App\Models\Pka;
use App\Models\Sprin;
use App\Models\StrukturPerangkatDaerah;
use App\Models\Tim;
use App\Models\User;
use Database\Seeders\KodeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnevTabulasiTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('anev.dinas'))->assertRedirect(route('login'));
        $this->get(route('anev.kecamatan'))->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_anev_dinas_and_kecamatan(): void
    {
        $user = User::factory()->create(['role' => 'Super Admin']);

        $this->actingAs($user)->get(route('anev.dinas'))
            ->assertOk()
            ->assertSee('Anev')
            ->assertSee('Dinas')
            ->assertSee('REKAPITULASI JUMLAH TEMUAN')
            ->assertSee('PER BIDANG TAHAP 1')
            ->assertSee('PER BIDANG TAHAP 2')
            ->assertSee('OPERASIONAL')
            ->assertSee('SDM')
            ->assertSee('LOGISTIK')
            ->assertSee('GARKEU')
            ->assertSee('TOTAL');

        $this->actingAs($user)->get(route('anev.kecamatan'))
            ->assertOk()
            ->assertSee('Anev')
            ->assertSee('Kecamatan')
            ->assertSee('REKAPITULASI JUMLAH TEMUAN')
            ->assertSee('OPERASIONAL')
            ->assertSee('TOTAL');
    }

    public function test_anev_tabulasi_calculates_counts_accurately_per_lingkup_and_tahap(): void
    {
        $this->seed(KodeSeeder::class);
        $user = User::factory()->create(['role' => 'Super Admin']);

        $dinasSpd = StrukturPerangkatDaerah::query()->create([
            'nama_unit' => 'Dinas Perhubungan',
            'jenis_unit' => 'Dinas',
            'level' => 1,
            'provinsi' => 'Jawa Barat',
            'kabupaten_kota' => 'Kabupaten Majalengka',
        ]);

        $timDinas = Tim::query()->create([
            'nama_tim' => 'Tim Dinas 1',
            'lingkup' => 'Dinas',
            'daltu_id' => $user->id,
            'dalnis_id' => $user->id,
            'ketua_tim_id' => $user->id,
            'created_by' => $user->id,
        ]);

        $sprinDinas = Sprin::query()->create([
            'no_sprin' => 'SPRIN/DINAS/2026/01',
            'no_surat_pemberitahuan' => 'SPT/DINAS/2026/01',
            'tgl_mulai' => '2026-03-03',
            'tgl_akhir' => '2026-03-04',
            'tahap' => '1',
            'sprin_tipe' => 'Dinas',
            'tim_id' => $timDinas->id,
            'struktur_perangkat_daerah_id' => $dinasSpd->id,
            'katim' => $user->id,
            'dalnis' => $user->id,
            'daltu' => $user->id,
            'approved_by_daltu_date' => now(),
            'created_by' => $user->id,
            'user_id' => $user->id,
        ]);

        $pkaOperasional = Pka::query()->create([
            'sprin_id' => $sprinDinas->id,
            'nomor_pka' => 'PKA/DINAS/2026/01',
            'tanggal' => '2026-03-03',
            'bidang' => 'Operasional',
            'jenis_audit' => 'Audit Kinerja',
            'sasaran_auditi' => 'Dinas Perhubungan',
            'anggaran' => 10000000,
            'status' => 'Final',
            'created_by' => $user->id,
        ]);

        $kkaOperasional = Kka::query()->create([
            'nomor_kka' => 'KKA/DINAS/2026/01',
            'pka_id' => $pkaOperasional->id,
            'struktur_perangkat_daerah_id' => $dinasSpd->id,
            'auditee_level' => 'Wilayah',
            'dibuat_oleh_id' => $user->id,
            'direviu_oleh_id' => $user->id,
            'tanggal_dibuat' => '2026-03-03',
            'tanggal_direviu' => '2026-03-03',
            'tanggal_kka' => '2026-03-03',
            'tempat' => 'Majalengka',
            'status' => 'Final',
        ]);

        // 1 temuan belum ditinjut
        KkaTabulasi::query()->create([
            'kka_id' => $kkaOperasional->id,
            'jenis_tabulasi' => 'Temuan',
            'nomor' => '1',
            'kondisi' => 'Kondisi 1',
            'kriteria' => 'Kriteria 1',
            'sebab' => 'Sebab 1',
            'akibat' => 'Akibat 1',
            'rekomendasi' => 'Rekomendasi 1',
            'status_tinjut' => 'Belum Ditinjut',
            'tindak_lanjut' => null,
            'status' => 'Final',
        ]);

        // 1 temuan sudah ditinjut tapi belum selesai (Koreksi)
        KkaTabulasi::query()->create([
            'kka_id' => $kkaOperasional->id,
            'jenis_tabulasi' => 'Temuan',
            'nomor' => '2',
            'kondisi' => 'Kondisi 2',
            'kriteria' => 'Kriteria 2',
            'sebab' => 'Sebab 2',
            'akibat' => 'Akibat 2',
            'rekomendasi' => 'Rekomendasi 2',
            'status_tinjut' => 'Koreksi',
            'tindak_lanjut' => '<p>Perbaikan</p>',
            'status' => 'Final',
        ]);

        // 1 temuan sudah selesai
        KkaTabulasi::query()->create([
            'kka_id' => $kkaOperasional->id,
            'jenis_tabulasi' => 'Temuan',
            'nomor' => '3',
            'kondisi' => 'Kondisi 3',
            'kriteria' => 'Kriteria 3',
            'sebab' => 'Sebab 3',
            'akibat' => 'Akibat 3',
            'rekomendasi' => 'Rekomendasi 3',
            'status_tinjut' => 'Selesai',
            'tindak_lanjut' => '<p>Selesai</p>',
            'status' => 'Final',
        ]);

        $response = $this->actingAs($user)->get(route('anev.dinas'));
        $response->assertOk()
            ->assertSee('2026-03-03 s.d 2026-03-04')
            ->assertSee('KOREKSI');

        $tahap1 = $response->viewData('tahap1');
        $this->assertEquals('1', $tahap1['tahap']);
        $this->assertEquals('2026-03-03 s.d 2026-03-04', $tahap1['date_range']);

        $operasionalRow = collect($tahap1['rows'])->firstWhere('bidang', 'OPERASIONAL');
        $this->assertNotNull($operasionalRow);
        $this->assertEquals(1, $operasionalRow['belum']);
        $this->assertEquals(2, $operasionalRow['sudah']);
        $this->assertEquals(1, $operasionalRow['koreksi']);
        $this->assertEquals(1, $operasionalRow['selesai']);
        $this->assertEquals(3, $operasionalRow['total']);

        $this->assertEquals(1, $tahap1['total']['belum']);
        $this->assertEquals(2, $tahap1['total']['sudah']);
        $this->assertEquals(1, $tahap1['total']['koreksi']);
        $this->assertEquals(1, $tahap1['total']['selesai']);
        $this->assertEquals(3, $tahap1['total']['total']);

        // Assert Table 2 (Satker / Dinas rows)
        $response->assertSee('TEMUAN BIDANG JEMEN')
            ->assertSee('OPSNAL')
            ->assertSee('JUMLAH');

        $this->assertNotEmpty($tahap1['satker_rows']);
        $satkerDinas = collect($tahap1['satker_rows'])->firstWhere('satker', 'Dinas Perhubungan');
        $this->assertNotNull($satkerDinas);
        $this->assertEquals(3, $satkerDinas['opsnal']);
        $this->assertEquals(0, $satkerDinas['sdm']);
        $this->assertEquals(0, $satkerDinas['logistik']);
        $this->assertEquals(0, $satkerDinas['garkeu']);
        $this->assertEquals(3, $satkerDinas['jumlah']);

        $this->assertEquals(3, $tahap1['satker_total']['opsnal']);
        $this->assertEquals(0, $tahap1['satker_total']['sdm']);
        $this->assertEquals(0, $tahap1['satker_total']['logistik']);
        $this->assertEquals(0, $tahap1['satker_total']['garkeu']);
        $this->assertEquals(3, $tahap1['satker_total']['jumlah']);

        // Assert Sub Units in satker row
        $this->assertNotEmpty($satkerDinas['sub_units']);
        $subTotalOpsnal = collect($satkerDinas['sub_units'])->sum('opsnal');
        $this->assertEquals(3, $subTotalOpsnal);

        $response->assertSee('SUB UNIT')
            ->assertSee('openSubUnitModal', false)
            ->assertSee('Filter Data')
            ->assertSee('Dari')
            ->assertSee('Sampai')
            ->assertSee('Audit');

        // Test Filter: Date out of range excludes findings
        $responseFilteredOut = $this->actingAs($user)->get(route('anev.dinas', [
            'dari' => '2026-01-01',
            'sampai' => '2026-02-28',
        ]));
        $responseFilteredOut->assertOk();
        $tahap1FilteredOut = $responseFilteredOut->viewData('tahap1');
        $this->assertEquals(0, $tahap1FilteredOut['total']['total']);

        // Test Filter: Date in range includes findings
        $responseFilteredIn = $this->actingAs($user)->get(route('anev.dinas', [
            'dari' => '2026-03-01',
            'sampai' => '2026-03-31',
        ]));
        $responseFilteredIn->assertOk();
        $tahap1FilteredIn = $responseFilteredIn->viewData('tahap1');
        $this->assertEquals(3, $tahap1FilteredIn['total']['total']);

        // Test Filter: Jenis audit mismatch excludes findings
        $responsePnbp = $this->actingAs($user)->get(route('anev.dinas', [
            'jenis_audit' => 'Audit PNBP',
        ]));
        $responsePnbp->assertOk();
        $tahap1Pnbp = $responsePnbp->viewData('tahap1');
        $this->assertEquals(0, $tahap1Pnbp['total']['total']);

        // Test Filter: Jenis audit match includes findings
        $responseKinerja = $this->actingAs($user)->get(route('anev.dinas', [
            'jenis_audit' => 'Audit Kinerja',
        ]));
        $responseKinerja->assertOk();
        $tahap1Kinerja = $responseKinerja->viewData('tahap1');
        $this->assertEquals(3, $tahap1Kinerja['total']['total']);

        // Assert 3D Donut Chart presence
        $response->assertSee('DIAGRAM TEMUAN PER BIDANG')
            ->assertSee('drawChart', false)
            ->assertSee('chartCanvas', false)
            ->assertSee('chartData', false);
    }
}
