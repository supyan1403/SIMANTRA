<?php

namespace Database\Seeders;

use App\Models\AlokasiHonor;
use App\Models\Bidang;
use App\Models\Kegiatan;
use App\Models\Mitra;
use App\Models\Periode;
use Illuminate\Database\Seeder;

class Dummy2025Seeder extends Seeder
{
    public function run(): void
    {
        $bidangs = Bidang::all();
        if ($bidangs->isEmpty()) {
            return;
        }

        // Pastikan periode 2025 ada dari bulan 1 sampai 12
        $bulanNama = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        $periodes2025 = [];
        foreach ($bulanNama as $num => $nama) {
            $periodes2025[$num] = Periode::firstOrCreate(
                ['tahun' => '2025', 'bulan_angka' => $num],
                ['bulan' => $nama]
            );
        }

        // Pastikan kegiatan 2025 tersedia per bidang
        $kegiatanList = [
            ['nama' => 'Survei Angkatan Kerja Nasional (SAKERNAS) 2025', 'bidang' => 'Sosial', 'pagu' => 125000000],
            ['nama' => 'Survei Biaya Hidup (SBH) 2025', 'bidang' => 'Distribusi', 'pagu' => 180000000],
            ['nama' => 'Survei Industri Mikro dan Kecil (VIMK) 2025', 'bidang' => 'Produksi', 'pagu' => 95000000],
            ['nama' => 'Penyusunan Tabel Input-Output & Neraca Regional 2025', 'bidang' => 'Neraca', 'pagu' => 75000000],
            ['nama' => 'Pendataan Potensi Desa (PODES) 2025', 'bidang' => 'Sosial', 'pagu' => 210000000],
            ['nama' => 'Survei Komoditas Strategis & Harga Produsen 2025', 'bidang' => 'Distribusi', 'pagu' => 110000000],
        ];

        $createdKegiatans = [];
        foreach ($kegiatanList as $kData) {
            $b = $bidangs->firstWhere('nama', $kData['bidang']) ?? $bidangs->first();
            $createdKegiatans[] = Kegiatan::firstOrCreate(
                ['nama' => $kData['nama'], 'tahun' => '2025'],
                [
                    'bidang_id' => $b->id,
                    'kode_mata_anggaran' => '054.01.GG.' . rand(1000, 9999),
                    'total' => $kData['pagu'],
                    'jumlah' => rand(50, 150),
                    'harga' => 1500000,
                ]
            );
        }

        // Ambil sampel mitra untuk dialokasikan ke 2025
        $mitras = Mitra::limit(350)->get();
        if ($mitras->count() < 100) {
            return;
        }

        // Alokasikan sebagian mitra ke beberapa bulan 2025 dengan nomor SPK manual per kegiatan
        $spkCounters = [
            1 => 'B-001/BPS/3206/SPK/01/2025',
            2 => 'B-002/BPS/3206/SPK/02/2025',
            3 => 'B-003/BPS/3206/SPK/03/2025',
            4 => 'B-004/BPS/3206/SPK/04/2025',
            5 => 'B-005/BPS/3206/SPK/05/2025',
            6 => 'B-006/BPS/3206/SPK/06/2025',
        ];

        foreach ($createdKegiatans as $idx => $keg) {
            $nomorSpk = $spkCounters[$idx + 1] ?? ('B-0' . ($idx + 1) . '/BPS/3206/SPK/0' . ($idx + 1) . '/2025');
            $nomorBast = str_replace('SPK', 'BAST', $nomorSpk);
            
            // Pilih rentang bulan
            $bulanTarget = ($idx % 6) + 1; // Bulan 1..6
            $periode = $periodes2025[$bulanTarget];

            // Ambil 30-50 mitra untuk kegiatan ini
            $kegMitras = $mitras->slice($idx * 25, 35);
            foreach ($kegMitras as $m) {
                // Beri nominal rata-rata 1.2jt - 2.5jt, dan salah satu buat contoh kelebihan SBML > 4.65jt
                $nominal = rand(12, 28) * 100000;
                
                AlokasiHonor::firstOrCreate(
                    [
                        'mitra_id' => $m->id,
                        'periode_id' => $periode->id,
                        'kegiatan_id' => $keg->id,
                    ],
                    [
                        'nominal' => $nominal,
                        'nomor_spk' => $nomorSpk,
                        'nomor_bast' => $nomorBast,
                        'tanggal_spk' => '2025-0' . $bulanTarget . '-05',
                    ]
                );
            }
        }

        // Buat 1 contoh mitra yang melebihi batas SBML (misal Rp 5.200.000) pada bulan 3
        $sampleMitra = $mitras->first();
        if ($sampleMitra) {
            $pMaret = $periodes2025[3];
            $keg1 = $createdKegiatans[0];
            $keg2 = $createdKegiatans[1];

            AlokasiHonor::updateOrCreate(
                ['mitra_id' => $sampleMitra->id, 'periode_id' => $pMaret->id, 'kegiatan_id' => $keg1->id],
                ['nominal' => 2800000, 'nomor_spk' => $spkCounters[1]]
            );

            AlokasiHonor::updateOrCreate(
                ['mitra_id' => $sampleMitra->id, 'periode_id' => $pMaret->id, 'kegiatan_id' => $keg2->id],
                ['nominal' => 2400000, 'nomor_spk' => $spkCounters[2]]
            );
        }
    }
}
