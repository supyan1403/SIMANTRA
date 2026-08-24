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

class SpkNumberingTest extends TestCase
{
    use RefreshDatabase;

    public function test_spk_page_renders_with_kategori_and_format_spk(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $this->actingAs($user);

        $response = $this->get(route('spk.index', [
            'kategori_kegiatan' => 'sensus',
            'format_spk' => 'B-{nomor}/BPS/3206/SENSUS/{bulan}/{tahun}',
            'nomor_awal' => 10,
        ]));

        $response->assertStatus(200);
        $response->assertSee('POLA / FORMAT NO. SPK');
        $response->assertSee('KATEGORI KEGIATAN');
    }

    public function test_cetak_utama_with_custom_format_spk(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $mitra = Mitra::create(['nama' => 'Budi Santoso', 'alamat' => 'Tasikmalaya', 'pekerjaan' => 'Mitra', 'jk' => 'L']);
        $periode = Periode::create(['tahun' => 2026, 'bulan' => 'Januari', 'bulan_angka' => 1]);
        $bidang = Bidang::create(['nama' => 'Statistik Produksi']);
        $kegiatan = Kegiatan::create(['nama' => 'Sensus Pertanian 2026', 'bidang_id' => $bidang->id]);

        AlokasiHonor::create([
            'mitra_id' => $mitra->id,
            'periode_id' => $periode->id,
            'kegiatan_id' => $kegiatan->id,
            'nominal' => 2500000,
        ]);

        $this->actingAs($user);

        $response = $this->get(route('spk.cetak-utama', [
            'mitra' => $mitra->id,
            'tahun' => 2026,
            'bulan_awal' => 1,
            'bulan_akhir' => 1,
            'nomor_awal' => 5,
            'format_spk' => 'B-{nomor}/BPS/3206/SENSUS/{bulan}/{tahun}',
        ]));

        $response->assertStatus(200);
        $response->assertSee('B-0005/BPS/3206/SENSUS/01/2026');
    }

    public function test_cetak_massal_with_custom_format_spk(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $mitra1 = Mitra::create(['nama' => 'Mitra A', 'alamat' => 'Tasikmalaya', 'pekerjaan' => 'Mitra', 'jk' => 'L']);
        $mitra2 = Mitra::create(['nama' => 'Mitra B', 'alamat' => 'Tasikmalaya', 'pekerjaan' => 'Mitra', 'jk' => 'P']);
        $periode = Periode::create(['tahun' => 2026, 'bulan' => 'Februari', 'bulan_angka' => 2]);
        $bidang = Bidang::create(['nama' => 'Statistik Sosial']);
        $kegiatan = Kegiatan::create(['nama' => 'Survei Susenas', 'bidang_id' => $bidang->id]);

        AlokasiHonor::create([
            'mitra_id' => $mitra1->id,
            'periode_id' => $periode->id,
            'kegiatan_id' => $kegiatan->id,
            'nominal' => 1500000,
        ]);

        AlokasiHonor::create([
            'mitra_id' => $mitra2->id,
            'periode_id' => $periode->id,
            'kegiatan_id' => $kegiatan->id,
            'nominal' => 1500000,
        ]);

        $this->actingAs($user);

        $response = $this->post(route('spk.cetak-massal'), [
            'mitra_ids' => [$mitra1->id, $mitra2->id],
            'tahun' => 2026,
            'bulan_awal' => 2,
            'bulan_akhir' => 2,
            'nomor_awal' => 101,
            'kategori_kegiatan' => 'survei',
            'format_spk' => 'B-{nomor}/BPS/3206/SURVEI/{bulan}/{tahun}',
        ]);

        $response->assertStatus(200);
        $response->assertSee('B-0101/BPS/3206/SURVEI/02/2026');
        $response->assertSee('B-0102/BPS/3206/SURVEI/02/2026');
    }

    public function test_cetak_with_distinct_bulan_spk_and_tahun_spk(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $mitra = Mitra::create(['nama' => 'Mitra Triwulan', 'alamat' => 'Tasikmalaya', 'pekerjaan' => 'Mitra', 'jk' => 'L']);
        $periode = Periode::create(['tahun' => 2026, 'bulan' => 'Januari', 'bulan_angka' => 1]);
        $bidang = Bidang::create(['nama' => 'Statistik Distribusi']);
        $kegiatan = Kegiatan::create(['nama' => 'Survei Triwulanan', 'bidang_id' => $bidang->id]);

        AlokasiHonor::create([
            'mitra_id' => $mitra->id,
            'periode_id' => $periode->id,
            'kegiatan_id' => $kegiatan->id,
            'nominal' => 3000000,
        ]);

        $this->actingAs($user);

        // Filter periode kerja: Januari s.d Maret 2026 (bulan_awal: 1, bulan_akhir: 3, tahun: 2026)
        // Nomor SPK khusus: bulan_spk: 4 (April), tahun_spk: 2027
        $response = $this->get(route('spk.cetak-utama', [
            'mitra' => $mitra->id,
            'tahun' => 2026,
            'bulan_awal' => 1,
            'bulan_akhir' => 3,
            'bulan_spk' => 4,
            'tahun_spk' => 2027,
            'nomor_awal' => 88,
            'format_spk' => 'B-{nomor}/BPS/3206/SPK/{bulan}/{tahun}',
        ]));

        $response->assertStatus(200);
        // Nomor SPK must use bulan_spk: 04 and tahun_spk: 2027
        $response->assertSee('B-0088/BPS/3206/SPK/04/2027');
    }
}

