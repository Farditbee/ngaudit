<?php

namespace Tests\Feature;

use App\Models\Kode;
use App\Models\SubKode;
use App\Models\User;
use Database\Seeders\KodeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KodeMasterTest extends TestCase
{
    use RefreshDatabase;

    public function test_kode_and_sub_kode_pages_are_displayed_for_every_jenis(): void
    {
        $user = User::factory()->create();

        foreach (Kode::JENIS as $jenis) {
            $this->actingAs($user)->get(route('master.kode.index', $jenis))
                ->assertOk()
                ->assertSee('Kode '.ucfirst($jenis));

            $this->actingAs($user)->get(route('master.sub-kode.index', $jenis))
                ->assertOk()
                ->assertSee('Sub Kode '.ucfirst($jenis));
        }
    }

    public function test_unknown_jenis_returns_not_found(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/master/kode/lainnya')->assertNotFound();
    }

    public function test_seeder_creates_ten_kode_temuan_and_contextual_sebab_akibat_rekomendasi_with_their_sub_kodes(): void
    {
        $this->seed(KodeSeeder::class);

        $temuans = Kode::query()->where('jenis', Kode::JENIS_TEMUAN)->orderBy('kode')->get();
        $this->assertCount(10, $temuans);

        $kerugian = $temuans->firstWhere('kode', '01');
        $this->assertNotNull($kerugian);
        $this->assertSame('Kerugian Negara', $kerugian->nama);
        $this->assertNull($kerugian->kode_temuan_id);
        $this->assertSame(10, $kerugian->subKodes()->count());

        $this->assertSame('Kewajiban Setor Kepada Negara', $temuans->firstWhere('kode', '02')?->nama);
        $this->assertSame('Pelanggaran Terhadap Peraturan Perundang-Undangan', $temuans->firstWhere('kode', '03')?->nama);
        $this->assertSame('Pelanggaran Disiplin Anggaran', $temuans->firstWhere('kode', '05')?->nama);

        // Kode Sebab, Akibat, Rekomendasi semuanya terikat ke kode_temuan_id
        foreach ([Kode::JENIS_SEBAB, Kode::JENIS_AKIBAT, Kode::JENIS_REKOMENDASI] as $jenis) {
            $this->assertSame(0, Kode::query()->where('jenis', $jenis)->whereNull('kode_temuan_id')->count());

            foreach ($temuans as $temuan) {
                $contextualKodes = Kode::query()
                    ->where('jenis', $jenis)
                    ->where('kode_temuan_id', $temuan->id)
                    ->get();

                $this->assertGreaterThan(0, $contextualKodes->count());

                foreach ($contextualKodes as $contextualKode) {
                    $this->assertGreaterThan(0, $contextualKode->subKodes()->count());
                    // Sub kode terikat ke kode utamanya masing-masing, bukan ke kode temuan
                    $this->assertTrue(
                        $contextualKode->subKodes->every(fn (SubKode $sub): bool => $sub->kode_id === $contextualKode->id && $sub->kode_id !== $temuan->id)
                    );
                }
            }
        }
    }

    public function test_seeder_is_idempotent(): void
    {
        $this->seed(KodeSeeder::class);
        $kodeCount = Kode::query()->count();
        $subKodeCount = SubKode::query()->count();

        $this->seed(KodeSeeder::class);

        $this->assertSame($kodeCount, Kode::query()->count());
        $this->assertSame($subKodeCount, SubKode::query()->count());
    }

    public function test_datatable_filters_kode_sebab_akibat_rekomendasi_by_kode_temuan_and_sub_kode_by_main_kode(): void
    {
        $user = User::factory()->create();
        $this->seed(KodeSeeder::class);

        $temuan01 = Kode::query()->where('jenis', Kode::JENIS_TEMUAN)->where('kode', '01')->firstOrFail();

        // Filter Kode Sebab berdasarkan Kode Temuan 01
        $expectedSebabCount = Kode::query()
            ->where('jenis', Kode::JENIS_SEBAB)
            ->where('kode_temuan_id', $temuan01->id)
            ->count();

        $sebabResponse = $this->actingAs($user)->getJson(route('master.kode.index', [
            'jenis' => Kode::JENIS_SEBAB,
            'kode_temuan_id' => $temuan01->id,
            'length' => 100,
        ]))->assertOk();

        $this->assertSame($expectedSebabCount, $sebabResponse->json('recordsFiltered'));

        // Filter Sub Kode Sebab berdasarkan Kode Sebab utamanya (bukan Kode Temuan)
        $sebabUtama = Kode::query()
            ->where('jenis', Kode::JENIS_SEBAB)
            ->where('kode_temuan_id', $temuan01->id)
            ->firstOrFail();

        $subSebabResponse = $this->actingAs($user)->getJson(route('master.sub-kode.index', [
            'jenis' => Kode::JENIS_SEBAB,
            'kode_id' => $sebabUtama->id,
            'length' => 100,
        ]))->assertOk();

        $this->assertSame($sebabUtama->subKodes()->count(), $subSebabResponse->json('recordsFiltered'));
    }

    public function test_user_can_create_update_and_delete_contextual_kode_with_sub_kode(): void
    {
        $user = User::factory()->create();

        $temuan = Kode::query()->create([
            'jenis' => Kode::JENIS_TEMUAN,
            'kode' => '01',
            'nama' => 'Kerugian Negara',
        ]);

        // Kode Sebab wajib menyertakan kode_temuan_id
        $this->actingAs($user)->postJson(route('master.kode.store', 'sebab'), [
            'kode' => '01.S1',
            'nama' => 'Sebab Tanpa Temuan',
        ])->assertUnprocessable()->assertJsonValidationErrors('kode_temuan_id');

        $store = $this->actingAs($user)->postJson(route('master.kode.store', 'sebab'), [
            'kode_temuan_id' => $temuan->id,
            'kode' => '01.S1',
            'nama' => 'Sebab Uji',
        ])->assertCreated();

        $kodeSebabId = $store->json('data.id');

        // Sub kode sebab mengikat ke kode sebab utama ($kodeSebabId)
        $sub = $this->actingAs($user)->postJson(route('master.sub-kode.store', 'sebab'), [
            'kode_id' => $kodeSebabId,
            'kode' => '01.S1.01',
            'nama' => 'Sub Sebab Uji',
        ])->assertCreated();

        // Sub kode sebab tidak boleh mengikat ke kode temuan
        $this->actingAs($user)->postJson(route('master.sub-kode.store', 'sebab'), [
            'kode_id' => $temuan->id,
            'kode' => '01.S1.02',
            'nama' => 'Salah Induk ke Temuan',
        ])->assertUnprocessable()->assertJsonValidationErrors('kode_id');

        $this->actingAs($user)->putJson(route('master.kode.update', ['sebab', $kodeSebabId]), [
            'kode_temuan_id' => $temuan->id,
            'kode' => '01.S1',
            'nama' => 'Sebab Diubah',
        ])->assertOk();

        $this->assertDatabaseHas('kodes', [
            'id' => $kodeSebabId,
            'kode_temuan_id' => $temuan->id,
            'nama' => 'Sebab Diubah',
        ]);

        $this->actingAs($user)->deleteJson(route('master.kode.destroy', ['sebab', $kodeSebabId]))->assertOk();
        $this->assertSoftDeleted('kodes', ['id' => $kodeSebabId]);
        $this->assertSoftDeleted('sub_kodes', ['id' => $sub->json('data.id')]);
    }
}
