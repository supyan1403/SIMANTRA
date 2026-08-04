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

    private function getCellValue($cell)
    {
        if (!$cell) return '';
        $val = $cell->getValue();
        if (is_string($val) && str_starts_with($val, '=')) {
            try {
                $oldVal = $cell->getOldCalculatedValue();
                if ($oldVal !== null && (!is_string($oldVal) || !str_starts_with((string)$oldVal, '='))) {
                    return $oldVal;
                }
            } catch (\Throwable $e) {}
            return '';
        }
        return $val;
    }

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
                            $mNama = trim((string)$this->getCellValue($mitraSheet->getCell('B' . $mr)));
                            if (empty($mNama) || $mNama === 'Nama') continue;

                            $mAlamat = trim((string)$this->getCellValue($mitraSheet->getCell('C' . $mr)));
                            $mPekerjaan = trim((string)$this->getCellValue($mitraSheet->getCell('E' . $mr)));
                            $mNoHp = trim((string)$this->getCellValue($mitraSheet->getCell('Y' . $mr)));
                            $mSobatId = trim((string)$this->getCellValue($mitraSheet->getCell('AJ' . $mr)));

                            $existingMitra = Mitra::where('nama', $mNama)->first();
                            if ($existingMitra) {
                                $existingMitra->update(array_filter([
                                    'id_sobat' => $mSobatId ?: $existingMitra->id_sobat,
                                    'no_hp' => $mNoHp ?: $existingMitra->no_hp,
                                    'alamat' => ($mAlamat && !str_starts_with($mAlamat, '=')) ? $mAlamat : $existingMitra->alamat,
                                    'pekerjaan' => ($mPekerjaan && !str_starts_with($mPekerjaan, '=')) ? $mPekerjaan : $existingMitra->pekerjaan,
                                ]));
                            } else {
                                Mitra::create([
                                    'nama' => $mNama,
                                    'id_sobat' => $mSobatId ?: null,
                                    'no_hp' => $mNoHp ?: null,
                                    'alamat' => ($mAlamat && !str_starts_with($mAlamat, '=')) ? $mAlamat : null,
                                    'pekerjaan' => ($mPekerjaan && !str_starts_with($mPekerjaan, '=')) ? $mPekerjaan : null,
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
                    $no = trim((string)($this->getCellValue($sheet->getCell('A' . $row)) ?? ''));
                    if (empty($no) || !is_numeric($no)) continue;

                    $namaMitra = trim((string)($this->getCellValue($sheet->getCell('B' . $row)) ?? ''));
                    if (empty($namaMitra)) continue;

                    $alamat = trim((string)($this->getCellValue($sheet->getCell('C' . $row)) ?? ''));
                    $pekerjaan = trim((string)($this->getCellValue($sheet->getCell('D' . $row)) ?? ''));
                    $kodeAlamat = trim((string)($this->getCellValue($sheet->getCell('E' . $row)) ?? ''));
                    $jkRaw = trim((string)($this->getCellValue($sheet->getCell('F' . $row)) ?? ''));
                    $jk = ($jkRaw == '1' || strtolower($jkRaw) == 'l') ? 'L' : (($jkRaw == '2' || strtolower($jkRaw) == 'p') ? 'P' : null);

                    $cleanAlamat = (!empty($alamat) && !str_starts_with($alamat, '=')) ? $alamat : null;
                    $cleanPekerjaan = (!empty($pekerjaan) && !str_starts_with($pekerjaan, '=')) ? $pekerjaan : null;

                    $mitra = Mitra::firstOrCreate(
                        ['nama' => $namaMitra],
                        ['alamat' => $cleanAlamat, 'pekerjaan' => $cleanPekerjaan, 'kode_alamat' => $kodeAlamat, 'jk' => $jk]
                    );

                    if ($mitra->pekerjaan && str_starts_with($mitra->pekerjaan, '=')) {
                        $mitra->update(['pekerjaan' => $cleanPekerjaan]);
                    }
                    if ($mitra->alamat && str_starts_with($mitra->alamat, '=')) {
                        $mitra->update(['alamat' => $cleanAlamat]);
                    }

                    $totalMitraCount++;

                    // Honor & Kegiatan
                    $totalHonorRaw = $this->getCellValue($sheet->getCell('G' . $row)) ?? 0;
                    $totalHonor = is_numeric($totalHonorRaw) ? floatval($totalHonorRaw) : 0;

                    $namaKegiatanRaw = trim((string)($this->getCellValue($sheet->getCell('I' . $row)) ?? ''));
                    $makRaw = trim((string)($this->getCellValue($sheet->getCell('J' . $row)) ?? ''));

                    if (!empty($namaKegiatanRaw) && $totalHonor > 0) {
                        $kegiatanItems = array_filter(array_map('trim', explode(';', $namaKegiatanRaw)));
                        $makItems = array_filter(array_map('trim', explode(';', $makRaw)));

                        $namaKegiatan = $kegiatanItems[0] ?? $namaKegiatanRaw;
                        $kodeMak = $makItems[0] ?? null;

                        // Determine Bidang
                        $targetBidang = $bidangDistribusi;
                        $honorL = floatval($this->getCellValue($sheet->getCell('L' . $row)) ?? 0);
                        $honorM = floatval($this->getCellValue($sheet->getCell('M' . $row)) ?? 0);
                        $honorN = floatval($this->getCellValue($sheet->getCell('N' . $row)) ?? 0);
                        $honorO = floatval($this->getCellValue($sheet->getCell('O' . $row)) ?? 0);

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
                    $sbmlPencacahan = $this->getCellValue($sheet->getCell('BO' . $row)) ?? 0;
                    $sbmlPengolahan = $this->getCellValue($sheet->getCell('BS' . $row)) ?? 0;

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
