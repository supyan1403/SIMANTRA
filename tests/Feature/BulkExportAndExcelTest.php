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

class BulkExportAndExcelTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Bidang $bidang;
    private Periode $periode;
    private Kegiatan $kegiatan;
    private Mitra $mitra;

    protected function setUp(): void
    {
        parent::setUp();

        $this->bidang = Bidang::create(['nama' => 'Statistik Sosial', 'kode' => 'SOS']);
        $this->admin = User::create([
            'name' => 'Admin Test',
            'username' => 'admintest',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $this->periode = Periode::create([
            'tahun' => 2026,
            'bulan' => 'Januari',
            'bulan_angka' => 1,
        ]);

        $this->kegiatan = Kegiatan::create([
            'bidang_id' => $this->bidang->id,
            'nama' => 'Survei Sosial Ekonomi Nasional 2026',
            'kode_mata_anggaran' => '2894.BMA.001.051.A',
            'tahun' => 2026,
            'target_volume' => 10,
            'satuan' => 'Dokumen',
            'harga_satuan' => 500000,
            'total_anggaran' => 5000000,
        ]);

        $this->mitra = Mitra::create([
            'nama' => 'BUDI SANTOSO',
            'id_sobat' => '320600123',
            'no_hp' => '081234567890',
            'kabupaten_kota' => 'Kabupaten Tasikmalaya',
            'kecamatan' => 'CIPATUJAH',
            'desa' => 'CIHERAS',
            'alamat_detail' => 'Jl. Raya Cipatujah No. 10',
            'pekerjaan' => 'Pencacah',
            'jk' => 'L',
        ]);

        AlokasiHonor::create([
            'mitra_id' => $this->mitra->id,
            'periode_id' => $this->periode->id,
            'kegiatan_id' => $this->kegiatan->id,
            'nominal' => 1500000,
            'volume' => 3,
            'satuan' => 'Dokumen',
            'nomor_spk' => 'SPK/001/3206/2026',
            'nomor_bast' => 'BAST/001/3206/2026',
        ]);
    }

    public function test_export_mitra_excel(): void
    {
        $response = $this->actingAs($this->admin)->get(route('mitra.export'));
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    public function test_export_kegiatan_excel(): void
    {
        $response = $this->actingAs($this->admin)->get(route('kegiatan.export'));
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    public function test_export_monitoring_excel(): void
    {
        $response = $this->actingAs($this->admin)->get(route('monitoring.export'));
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    public function test_export_rekap_excel(): void
    {
        $response = $this->actingAs($this->admin)->get(route('rekap.export', ['tahun' => 2026]));
        $response->assertStatus(200);
    }

    public function test_download_universal_template(): void
    {
        $response = $this->actingAs($this->admin)->get(route('import.template-universal'));
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    public function test_download_mantra_template(): void
    {
        $response = $this->actingAs($this->admin)->get(route('import.template-mantra'));
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    public function test_export_mantra_matrix(): void
    {
        $response = $this->actingAs($this->admin)->get(route('rekap.export-mantra-matrix', ['tahun' => 2026]));
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    public function test_unduh_massal_zip(): void
    {
        $response = $this->actingAs($this->admin)->post(route('spk.unduh-massal-zip'), [
            'mitra_ids' => ["{$this->mitra->id}_{$this->kegiatan->id}"],
            'tahun' => 2026,
            'bulan_awal' => 1,
            'bulan_akhir' => 12,
            'format' => 'docx',
        ]);

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/zip');
    }
}
