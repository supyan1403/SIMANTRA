<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Models\Bidang;
use App\Models\Periode;
use App\Models\Mitra;
use App\Models\Kegiatan;
use App\Models\AlokasiHonor;
use App\Models\Sbml;

class ImportMantraCommand extends Command
{
    protected $signature = 'mantra:import {path?}';
    protected $description = 'Import data MANTRA Excel ke database';

    public function handle()
    {
        ini_set('memory_limit', '-1');
        set_time_limit(0);

        $filePath = $this->argument('path') ?? base_path('1. Input MANTRA (Monitoring Alokasi Pekerjaan Dan Honor Mitra) oleh PJ Kegiatan atau Ketua Tim - Update3.xlsx.xlsx');

        if (!file_exists($filePath)) {
            $this->error("File tidak ditemukan di path: {$filePath}");
            return 1;
        }

        $this->info("Membaca file Excel MANTRA: {$filePath}...");
        $reader = IOFactory::createReaderForFile($filePath);
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($filePath);

        $bulanMap = [
            'JANUARI' => ['bulan' => 'Januari', 'angka' => 1],
            'FEBRUARI' => ['bulan' => 'Februari', 'angka' => 2],
            'MARET' => ['bulan' => 'Maret', 'angka' => 3],
            'APRIL' => ['bulan' => 'April', 'angka' => 4],
            'MEI' => ['bulan' => 'Mei', 'angka' => 5],
            'JUNI' => ['bulan' => 'Juni', 'angka' => 6],
            'JULI' => ['bulan' => 'Juli', 'angka' => 7],
            'AGUSTUS' => ['bulan' => 'Agustus', 'angka' => 8],
            'SEPTEMBR' => ['bulan' => 'September', 'angka' => 9],
            'OKTOBER' => ['bulan' => 'Oktober', 'angka' => 10],
            'NOPEMBER' => ['bulan' => 'November', 'angka' => 11],
            'DESEMBER' => ['bulan' => 'Desember', 'angka' => 12],
        ];

        // Ensure default Bidang exist
        $bidangDistribusi = Bidang::firstOrCreate(['nama' => 'Distribusi']);
        $bidangNeraca = Bidang::firstOrCreate(['nama' => 'Neraca']);
        $bidangProduksi = Bidang::firstOrCreate(['nama' => 'Produksi']);
        $bidangSosial = Bidang::firstOrCreate(['nama' => 'Sosial']);
        $bidangCadangan = Bidang::firstOrCreate(['nama' => 'Cadangan']);

        $sheets = [];
        foreach ($spreadsheet->getSheetNames() as $name) {
            if (isset($bulanMap[$name])) {
                $sheets[] = $name;
            }
        }

        $this->info("Sheet bulanan terdeteksi (" . count($sheets) . "): " . implode(', ', $sheets));

        $totalMitraCount = 0;
        $totalAlokasiCount = 0;

        DB::beginTransaction();

        try {
            // Auto-import DB Mitra sheet for SOBAT ID & No. HP
            foreach ($spreadsheet->getSheetNames() as $sName) {
                if (str_contains(strtoupper($sName), 'DB MITRA') || (str_contains(strtoupper($sName), 'MITRA') && !str_contains(strtoupper($sName), 'JANUARI'))) {
                    $mitraSheet = $spreadsheet->getSheetByName($sName);
                    if ($mitraSheet) {
                        $this->info("Mengimpor data SOBAT ID & No HP dari sheet {$sName}...");
                        $mHighestRow = $mitraSheet->getHighestRow();
                        for ($mr = 3; $mr <= $mHighestRow; $mr++) {
                            $mNama = trim((string)$mitraSheet->getCell('B' . $mr)->getValue());
                            if (empty($mNama) || $mNama === 'Nama') continue;

                            $mAlamat = trim((string)$mitraSheet->getCell('C' . $mr)->getValue());
                            $mPekerjaan = trim((string)$mitraSheet->getCell('E' . $mr)->getValue());
                            $mNoHp = trim((string)$mitraSheet->getCell('Y' . $mr)->getValue());
                            $mSobatId = trim((string)$mitraSheet->getCell('AJ' . $mr)->getValue());

                            $existingMitra = Mitra::where('nama', $mNama)->first();
                            if ($existingMitra) {
                                $existingMitra->update(array_filter([
                                    'id_sobat' => $mSobatId ?: $existingMitra->id_sobat,
                                    'no_hp' => $mNoHp ?: $existingMitra->no_hp,
                                    'alamat' => $mAlamat ?: $existingMitra->alamat,
                                    'pekerjaan' => $mPekerjaan ?: $existingMitra->pekerjaan,
                                ]));
                            } else {
                                Mitra::create([
                                    'nama' => $mNama,
                                    'id_sobat' => $mSobatId ?: null,
                                    'no_hp' => $mNoHp ?: null,
                                    'alamat' => $mAlamat ?: null,
                                    'pekerjaan' => $mPekerjaan ?: null,
                                ]);
                            }
                        }
                    }
                    break;
                }
            }

            foreach ($sheets as $sheetName) {
                $sheet = $spreadsheet->getSheetByName($sheetName);
                if (!$sheet) continue;

                $this->info("Mengolah Sheet {$sheetName}...");

                $bulanInfo = $bulanMap[$sheetName];
                $year = 2024;

                $periode = Periode::firstOrCreate([
                    'tahun' => $year,
                    'bulan' => $bulanInfo['bulan'],
                    'bulan_angka' => $bulanInfo['angka'],
                ]);

                $highestRow = $sheet->getHighestRow();

                for ($row = 7; $row <= $highestRow; $row++) {
                    $no = trim((string)($sheet->getCell('A' . $row)->getValue() ?? ''));
                    if (empty($no) || !is_numeric($no)) continue;

                    $namaMitra = trim((string)($sheet->getCell('B' . $row)->getValue() ?? ''));
                    if (empty($namaMitra)) continue;

                    $alamat = trim((string)($sheet->getCell('C' . $row)->getValue() ?? ''));
                    $pekerjaan = trim((string)($sheet->getCell('D' . $row)->getValue() ?? ''));
                    $kodeAlamat = trim((string)($sheet->getCell('E' . $row)->getValue() ?? ''));
                    $jkRaw = trim((string)($sheet->getCell('F' . $row)->getValue() ?? ''));
                    $jk = ($jkRaw == '1' || strtolower($jkRaw) == 'l') ? 'L' : (($jkRaw == '2' || strtolower($jkRaw) == 'p') ? 'P' : null);

                    $mitra = Mitra::firstOrCreate(
                        ['nama' => $namaMitra],
                        ['alamat' => $alamat, 'pekerjaan' => $pekerjaan, 'kode_alamat' => $kodeAlamat, 'jk' => $jk]
                    );

                    $totalMitraCount++;

                    // Honor & Kegiatan
                    $totalHonorRaw = $sheet->getCell('G' . $row)->getValue() ?? 0;
                    $totalHonor = is_numeric($totalHonorRaw) ? floatval($totalHonorRaw) : 0;

                    $namaKegiatanRaw = trim((string)($sheet->getCell('I' . $row)->getValue() ?? ''));
                    $makRaw = trim((string)($sheet->getCell('J' . $row)->getValue() ?? ''));

                    if (!empty($namaKegiatanRaw) && $totalHonor > 0) {
                        $kegiatanItems = array_filter(array_map('trim', explode(';', $namaKegiatanRaw)));
                        $makItems = array_filter(array_map('trim', explode(';', $makRaw)));

                        $namaKegiatan = $kegiatanItems[0] ?? $namaKegiatanRaw;
                        $kodeMak = $makItems[0] ?? null;

                        // Determine Bidang
                        $targetBidang = $bidangDistribusi;
                        $honorL = floatval($sheet->getCell('L' . $row)->getValue() ?? 0);
                        $honorM = floatval($sheet->getCell('M' . $row)->getValue() ?? 0);
                        $honorN = floatval($sheet->getCell('N' . $row)->getValue() ?? 0);
                        $honorO = floatval($sheet->getCell('O' . $row)->getValue() ?? 0);

                        if ($honorL > 0) $targetBidang = $bidangNeraca;
                        elseif ($honorM > 0) $targetBidang = $bidangProduksi;
                        elseif ($honorN > 0) $targetBidang = $bidangSosial;
                        elseif ($honorO > 0) $targetBidang = $bidangCadangan;
                        else {
                            $lowerName = strtolower($namaKegiatan);
                            if (str_contains($lowerName, 'sosial') || str_contains($lowerName, 'sakernas') || str_contains($lowerName, 'susenas') || str_contains($lowerName, 'podes')) {
                                $targetBidang = $bidangSosial;
                            } elseif (str_contains($lowerName, 'produksi') || str_contains($lowerName, 'tani') || str_contains($lowerName, 'industri') || str_contains($lowerName, 'ubih') || str_contains($lowerName, 'kerubin')) {
                                $targetBidang = $bidangProduksi;
                            } elseif (str_contains($lowerName, 'neraca') || str_contains($lowerName, 'sktr') || str_contains($lowerName, 'disbun')) {
                                $targetBidang = $bidangNeraca;
                            }
                        }

                        $kegiatan = Kegiatan::firstOrCreate(
                            ['nama' => $namaKegiatan],
                            [
                                'bidang_id' => $targetBidang->id,
                                'kode_mata_anggaran' => $kodeMak,
                                'tahun' => '2024',
                            ]
                        );

                        // If existing kegiatan was default Distribusi, update to target bidang if more specific
                        if ($kegiatan->bidang_id == $bidangDistribusi->id && $targetBidang->id != $bidangDistribusi->id) {
                            $kegiatan->update(['bidang_id' => $targetBidang->id]);
                        }

                        AlokasiHonor::updateOrCreate(
                            ['mitra_id' => $mitra->id, 'periode_id' => $periode->id, 'kegiatan_id' => $kegiatan->id],
                            ['nominal' => $totalHonor]
                        );
                        $totalAlokasiCount++;
                    }

                    // SBML Pencacahan & Pengolahan
                    $sbmlPencacahan = $sheet->getCell('BO' . $row)->getValue() ?? 0;
                    $sbmlPengolahan = $sheet->getCell('BS' . $row)->getValue() ?? 0;

                    $sbmlPencacahanVal = is_numeric($sbmlPencacahan) ? floatval($sbmlPencacahan) : 0;
                    $sbmlPengolahanVal = is_numeric($sbmlPengolahan) ? floatval($sbmlPengolahan) : 0;

                    if ($sbmlPencacahanVal > 0) {
                        Sbml::updateOrCreate(
                            ['mitra_id' => $mitra->id, 'periode_id' => $periode->id, 'jenis' => 'Pencacahan'],
                            ['nominal' => $sbmlPencacahanVal]
                        );
                    }
                    if ($sbmlPengolahanVal > 0) {
                        Sbml::updateOrCreate(
                            ['mitra_id' => $mitra->id, 'periode_id' => $periode->id, 'jenis' => 'Pengolahan'],
                            ['nominal' => $sbmlPengolahanVal]
                        );
                    }
                }
            }

            DB::commit();
            $this->info("Import BERHASIL! {$totalAlokasiCount} data alokasi honor dan mitra berhasil diimport ke seluruh Bidang.");
            return 0;
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Gagal mengimpor data: " . $e->getMessage());
            return 1;
        }
    }
}
