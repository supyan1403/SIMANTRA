<?php

namespace Tests\Feature;

use App\Models\AlokasiHonor;
use App\Models\Bidang;
use App\Models\Kegiatan;
use App\Models\Mitra;
use App\Models\Periode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiMantraFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_landing_page_displays_simantra_title_and_no_login_button(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('SIMANTRA');
        $response->assertSee('Sistem Informasi Monitoring Alokasi Pekerjaan dan Honor Mitra');
        $response->assertDontSee('Login Admin / Operator');
    }

    public function test_manual_spk_update_and_synchronization_per_kegiatan(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $bidang = Bidang::create(['nama' => 'Distribusi']);
        $periode = Periode::create(['tahun' => '2025', 'bulan' => 'Maret', 'bulan_angka' => 3]);
        $kegiatan = Kegiatan::create([
            'nama' => 'Survei Harga Konsumen 2025',
            'bidang_id' => $bidang->id,
            'tahun' => '2025',
            'total' => 100000000
        ]);

        $mitra1 = Mitra::create(['nama' => 'Asep Saepuloh', 'pekerjaan' => 'Mitra', 'jk' => 'L', 'alamat' => 'Tasikmalaya']);
        $mitra2 = Mitra::create(['nama' => 'Budi Gunawan', 'pekerjaan' => 'Mitra', 'jk' => 'L', 'alamat' => 'Tasikmalaya']);

        $alokasi1 = AlokasiHonor::create([
            'mitra_id' => $mitra1->id,
            'periode_id' => $periode->id,
            'kegiatan_id' => $kegiatan->id,
            'nominal' => 1500000,
        ]);

        $alokasi2 = AlokasiHonor::create([
            'mitra_id' => $mitra2->id,
            'periode_id' => $periode->id,
            'kegiatan_id' => $kegiatan->id,
            'nominal' => 1500000,
        ]);

        $response = $this->actingAs($admin)->post(route('monitoring.update-spk'), [
            'kegiatan_id' => $kegiatan->id,
            'periode_id'  => $periode->id,
            'nomor_spk'   => 'B-099/BPS/3206/SPK/03/2025',
            'nomor_bast'  => 'B-099/BPS/3206/BAST/03/2025',
            'tanggal_spk' => '2025-03-01',
        ]);

        $response->assertSessionHas('success');

        $this->assertEquals('B-099/BPS/3206/SPK/03/2025', $alokasi1->fresh()->nomor_spk);
        $this->assertEquals('B-099/BPS/3206/SPK/03/2025', $alokasi2->fresh()->nomor_spk);
    }

    public function test_operator_is_scoped_to_assigned_bidang(): void
    {
        $bidangDist = Bidang::create(['nama' => 'Distribusi']);
        $bidangSos = Bidang::create(['nama' => 'Sosial']);

        $operatorDist = User::factory()->create([
            'role' => 'operator',
            'bidang_id' => $bidangDist->id
        ]);

        $periode = Periode::create(['tahun' => '2025', 'bulan' => 'April', 'bulan_angka' => 4]);

        $kegiatanSos = Kegiatan::create([
            'nama' => 'Survei Sosial Ekonomi 2025',
            'bidang_id' => $bidangSos->id,
            'tahun' => '2025'
        ]);

        // Operator distribusi mencoba update SPK kegiatan bidang sosial -> harus ditolak
        $response = $this->actingAs($operatorDist)->post(route('monitoring.update-spk'), [
            'kegiatan_id' => $kegiatanSos->id,
            'periode_id'  => $periode->id,
            'nomor_spk'   => 'B-ILLEGAL/BPS/3206/SPK/04/2025',
        ]);

        $response->assertSessionHas('error');
    }
}
