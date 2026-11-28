<?php

namespace Tests\Feature;

use App\Models\Plotting;
use App\Models\Sprin;
use App\Models\Tim;
use App\Models\User;
use App\Models\Wilayah;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SprinTest extends TestCase
{
    use RefreshDatabase;

    public function test_sprin_page_is_displayed_for_authenticated_user(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('audit.sprin.index'))
            ->assertOk()
            ->assertSee('Data Sprin')
            ->assertSee('Tambah Sprin');
    }

    public function test_can_create_update_and_delete_sprin_with_tim_snapshot(): void
    {
        Storage::fake('public');

        $plotting = Plotting::query()->create(['plot' => 'WIL I']);
        $admin = User::factory()->create(['role' => 'Admin Nasional', 'plotting_id' => $plotting->id]);
        $tim = Tim::factory()->create(['lingkup' => 'Nasional', 'plotting_id' => $plotting->id]);
        $otherTim = Tim::factory()->create();

        $payload = [
            'no_sprin' => 'SPRIN-001/2026',
            'file_surat_perintah' => UploadedFile::fake()->create('sprin.pdf', 100, 'application/pdf'),
            'no_surat_pemberitahuan' => 'SP-001/2026',
            'file_surat_pemberitahuan' => UploadedFile::fake()->create('pemberitahuan.pdf', 100, 'application/pdf'),
            'tim_id' => $tim->id,
            'tahap' => '1',
            'tgl_mulai' => '2026-12-01',
            'tgl_akhir' => '2026-12-15',
            'keterangan' => 'Uji',
        ];

        $this->actingAs($admin)
            ->postJson(route('audit.sprin.store'), [...$payload, 'tim_id' => $otherTim->id])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['tim_id']);

        $this->actingAs($admin)
            ->postJson(route('audit.sprin.store'), $payload)
            ->assertCreated();

        $sprin = Sprin::query()->firstOrFail();
        $this->assertSame('Nasional', $sprin->sprin_tipe);
        $this->assertSame($plotting->id, $sprin->plotting_id);
        $this->assertNull($sprin->kode_wilayah);
        $this->assertSame($tim->ketua_tim_id, $sprin->katim);
        $this->assertSame($tim->dalnis_id, $sprin->dalnis);
        $this->assertSame($tim->daltu_id, $sprin->daltu);
        $this->assertSame($admin->id, $sprin->created_by);
        $this->assertSame($admin->id, $sprin->user_id);
        $this->assertSame('sprin.pdf', $sprin->file_surat_perintah_name);
        Storage::disk('public')->assertExists($sprin->file_surat_perintah_id);

        $this->actingAs($admin)
            ->postJson(route('audit.sprin.update', $sprin), [
                'no_sprin' => 'SPRIN-001/2026',
                'no_surat_pemberitahuan' => 'SP-002/2026',
                'tim_id' => $tim->id,
                'tahap' => 'Khusus',
                'tgl_mulai' => '2026-12-01',
                'tgl_akhir' => '2026-12-20',
                '_method' => 'PUT',
            ])
            ->assertOk();

        $this->assertSame('Khusus', $sprin->fresh()->tahap);

        $indexResponse = $this->actingAs($admin)
            ->getJson(route('audit.sprin.index'))
            ->assertOk()
            ->assertJsonPath('recordsTotal', 1);

        $this->assertSame('WIL I', $indexResponse->json('data.0.plot_name'));
        $this->assertNull($indexResponse->json('data.0.province_name'));

        $this->actingAs($admin)->deleteJson(route('audit.sprin.destroy', $sprin))->assertOk();
        $this->assertSoftDeleted($sprin);
    }

    public function test_tgl_akhir_cannot_be_before_tgl_mulai(): void
    {
        $this->actingAs(User::factory()->create())
            ->postJson(route('audit.sprin.store'), [
                'tgl_mulai' => '2026-12-10',
                'tgl_akhir' => '2026-12-01',
            ])
            ->assertJsonValidationErrors(['tgl_akhir']);
    }

    public function test_super_admin_and_daltu_can_review_sprin_and_record_logs(): void
    {
        $daltuUser = User::factory()->create(['role' => 'Koordinator dan Pengendali']);
        $tim = Tim::factory()->create(['daltu_id' => $daltuUser->id]);
        $sprin = Sprin::factory()->create([
            'tim_id' => $tim->id,
            'daltu' => $daltuUser->id,
            'approved_by_daltu_date' => null,
        ]);

        $auditor = User::factory()->create(['role' => 'Auditor']);
        $superAdmin = User::factory()->create(['role' => 'Super Admin']);

        // Auditor dilarang mereviu
        $this->actingAs($auditor)
            ->postJson(route('audit.sprin.reviews.store', $sprin), [
                'status' => 'Disetujui',
                'catatan' => 'Uji auditor',
            ])
            ->assertForbidden();

        // Daltu mereviu dengan status Koreksi
        $this->actingAs($daltuUser)
            ->postJson(route('audit.sprin.reviews.store', $sprin), [
                'status' => 'Koreksi',
                'catatan' => 'Perbaiki tanggal dan anggota tim',
            ])
            ->assertCreated()
            ->assertJsonPath('latest_status', 'Koreksi');

        $this->assertNull($sprin->fresh()->approved_by_daltu_date);
        $this->assertSame(1, $sprin->reviews()->count());

        // Super Admin mereviu dengan status Disetujui
        $this->actingAs($superAdmin)
            ->postJson(route('audit.sprin.reviews.store', $sprin), [
                'status' => 'Disetujui',
                'catatan' => 'Sudah sesuai dan disetujui',
            ])
            ->assertCreated()
            ->assertJsonPath('latest_status', 'Disetujui');

        $sprin->refresh();
        $this->assertNotNull($sprin->approved_by_daltu_date);
        $this->assertSame(2, $sprin->reviews()->count());

        // Cek endpoint getReviews
        $response = $this->actingAs($daltuUser)
            ->getJson(route('audit.sprin.reviews.index', $sprin))
            ->assertOk();

        $response->assertJsonCount(2, 'reviews');
        $this->assertSame('Disetujui', $response->json('reviews.0.status'));
        $this->assertTrue($response->json('sprin.can_review'));

        // Akun selain Daltu & Super Admin (misal Auditor pada tim) tetap bisa melihat hasil reviu, tapi can_review = false
        $tim->auditors()->attach($auditor->id);
        $auditorResponse = $this->actingAs($auditor)
            ->getJson(route('audit.sprin.reviews.index', $sprin))
            ->assertOk();

        $auditorResponse->assertJsonCount(2, 'reviews');
        $this->assertFalse($auditorResponse->json('sprin.can_review'));
    }

    public function test_daltu_can_only_see_and_review_sprin_in_their_own_wil(): void
    {
        $wil1 = Plotting::query()->create(['plot' => 'WIL I']);
        $wil2 = Plotting::query()->create(['plot' => 'WIL II']);

        $daltuWil1 = User::factory()->create([
            'role' => 'Koordinator dan Pengendali',
            'plotting_id' => $wil1->id,
        ]);

        $timWil1 = Tim::factory()->create(['plotting_id' => $wil1->id]);
        $sprinWil1 = Sprin::factory()->create([
            'sprin_tipe' => 'Nasional',
            'plotting_id' => $wil1->id,
            'tim_id' => $timWil1->id,
        ]);

        $timWil2 = Tim::factory()->create(['plotting_id' => $wil2->id]);
        $sprinWil2 = Sprin::factory()->create([
            'sprin_tipe' => 'Nasional',
            'plotting_id' => $wil2->id,
            'tim_id' => $timWil2->id,
        ]);

        // Daltu WIL 1 hanya melihat Sprin WIL 1
        $response = $this->actingAs($daltuWil1)
            ->getJson(route('audit.sprin.index'))
            ->assertOk();

        $response->assertJsonPath('recordsTotal', 1);
        $this->assertSame($sprinWil1->no_sprin, $response->json('data.0.no_sprin'));

        // Daltu WIL 1 dilarang mereviu Sprin WIL 2
        $this->actingAs($daltuWil1)
            ->postJson(route('audit.sprin.reviews.store', $sprinWil2), [
                'status' => 'Disetujui',
                'catatan' => 'Uji beda WIL',
            ])
            ->assertForbidden();
    }

    public function test_sprin_datatable_shows_wil_for_nasional_and_province_for_wilayah(): void
    {
        $wil = Plotting::query()->create(['plot' => 'WIL II']);
        $province = Wilayah::query()->create([
            'kode_wilayah' => '32',
            'provinsi' => 'JAWA BARAT',
        ]);

        $superAdmin = User::factory()->create(['role' => 'Super Admin']);
        $tim = Tim::factory()->create([
            'lingkup' => 'Wilayah',
            'province_code' => '32',
        ]);
        $sprin = Sprin::factory()->create([
            'sprin_tipe' => 'Wilayah',
            'kode_wilayah' => '32',
            'plotting_id' => null,
            'tim_id' => $tim->id,
        ]);

        $response = $this->actingAs($superAdmin)
            ->getJson(route('audit.sprin.index'))
            ->assertOk();

        $response->assertJsonPath('recordsTotal', 1);
        $this->assertNull($response->json('data.0.plot_name'));
        $this->assertSame('JAWA BARAT', $response->json('data.0.province_name'));

        // Cek juga di getReviews
        $reviewResponse = $this->actingAs($superAdmin)
            ->getJson(route('audit.sprin.reviews.index', $sprin))
            ->assertOk();

        $this->assertNull($reviewResponse->json('sprin.plot_name'));
        $this->assertSame('JAWA BARAT', $reviewResponse->json('sprin.province_name'));
    }

    public function test_sprin_datatable_sorts_newest_first_by_default(): void
    {
        $superAdmin = User::factory()->create(['role' => 'Super Admin']);
        $tim = Tim::factory()->create(['lingkup' => 'Nasional']);

        $sprinOld = Sprin::factory()->create([
            'no_sprin' => 'SPRIN-999/2026',
            'tim_id' => $tim->id,
            'created_at' => now()->subDay(),
        ]);

        $sprinNew = Sprin::factory()->create([
            'no_sprin' => 'SPRIN-001/2026',
            'tim_id' => $tim->id,
            'created_at' => now(),
        ]);

        // Default query without specific order params (sorts by created_at desc)
        $response = $this->actingAs($superAdmin)
            ->getJson(route('audit.sprin.index'))
            ->assertOk();

        $response->assertJsonPath('recordsTotal', 2);
        $this->assertSame($sprinNew->no_sprin, $response->json('data.0.no_sprin'));
        $this->assertSame($sprinOld->no_sprin, $response->json('data.1.no_sprin'));

        // When explicitly ordering by column 1 (no_sprin desc)
        $responseWithOrder = $this->actingAs($superAdmin)
            ->getJson(route('audit.sprin.index', [
                'order' => [
                    ['column' => 1, 'dir' => 'desc'],
                ],
            ]))
            ->assertOk();

        $this->assertSame($sprinOld->no_sprin, $responseWithOrder->json('data.0.no_sprin'));
        $this->assertSame($sprinNew->no_sprin, $responseWithOrder->json('data.1.no_sprin'));
    }
}
