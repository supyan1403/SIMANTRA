<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Mitra;
use App\Models\Periode;
use App\Models\Bidang;
use App\Models\Kegiatan;
use App\Models\AlokasiHonor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MonitoringTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_edit_and_delete_monitoring_alokasi_honor(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $mitra = Mitra::create(['nama' => 'Test Mitra', 'alamat' => 'Tasikmalaya', 'pekerjaan' => 'Mitra', 'kode_alamat' => '3206010', 'jk' => 'L']);
        $periode = Periode::create(['tahun' => 2024, 'bulan' => 'Januari', 'bulan_angka' => 1]);
        $bidang = Bidang::create(['nama' => 'Sosial']);
        $kegiatan = Kegiatan::create(['nama' => 'Sakernas', 'bidang_id' => $bidang->id]);

        $this->actingAs($user);

        // 1. Create
        $response = $this->post(route('monitoring.store'), [
            'mitra_id' => $mitra->id,
            'periode_id' => $periode->id,
            'kegiatan_id' => $kegiatan->id,
            'nominal' => 1000000,
        ]);
        $response->assertRedirect(route('monitoring.index'));
        $this->assertDatabaseHas('alokasi_honors', ['nominal' => 1000000]);

        $alokasi = AlokasiHonor::first();

        // 2. Edit Page
        $editResponse = $this->get(route('monitoring.edit', $alokasi));
        $editResponse->assertStatus(200);

        // 3. Update
        $updateResponse = $this->put(route('monitoring.update', $alokasi), [
            'mitra_id' => $mitra->id,
            'periode_id' => $periode->id,
            'kegiatan_id' => $kegiatan->id,
            'nominal' => 2000000,
        ]);
        $updateResponse->assertRedirect(route('monitoring.index'));
        $this->assertDatabaseHas('alokasi_honors', ['nominal' => 2000000]);

        // 4. Delete
        $deleteResponse = $this->delete(route('monitoring.destroy', $alokasi));
        $deleteResponse->assertRedirect(route('monitoring.index'));
        $this->assertDatabaseMissing('alokasi_honors', ['id' => $alokasi->id]);
    }
}
