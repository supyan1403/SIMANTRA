<?php

namespace App\Http\Controllers;

use App\Models\AlokasiHonor;
use App\Models\Bidang;
use App\Models\Kegiatan;
use App\Models\Mitra;
use App\Models\Periode;
use App\Traits\HasBidangScope;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportController extends Controller
{
    use HasBidangScope;

    public function index()
    {
        return view('import.index');
    }

    public function downloadUniversalTemplate()
    {
        return \App\Support\UniversalTemplateService::generateTemplate();
    }

    public function downloadMantraTemplate()
    {
        return \App\Support\MantraMatrixService::downloadBlankTemplate();
    }

    public function downloadMitraTemplate()
    {
        return app(\App\Http\Controllers\MitraController::class)->importTemplate();
    }

    public function downloadKegiatanTemplate()
    {
        return app(\App\Http\Controllers\KegiatanController::class)->importTemplate();
    }

    public function preview(Request $request)
    {
        ini_set('memory_limit', '-1');
        set_time_limit(0);

        $request->validate(['file' => 'required|file|mimes:xlsx,xls']);

        $file = $request->file('file');
        $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $file->getClientOriginalName());
        $importsDir = storage_path('app/imports');
        if (!file_exists($importsDir)) {
            mkdir($importsDir, 0777, true);
        }
        $file->move($importsDir, $filename);
        $fullPath = $importsDir . DIRECTORY_SEPARATOR . $filename;

        $reader = IOFactory::createReaderForFile($fullPath);
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($fullPath);

        $sheetNames = $spreadsheet->getSheetNames();
        $firstSheet = $spreadsheet->getSheet(0);
        $firstSheetName = strtoupper(trim($firstSheet->getTitle()));

        // Cek header baris 1 dan baris 2 untuk auto-detection
        $row1 = $firstSheet->rangeToArray('A1:Z2');
        $headerStr = strtoupper(implode(' ', array_filter(array_merge($row1[0] ?? [], $row1[1] ?? []))));

        $isMitra = str_contains($firstSheetName, 'MITRA') || str_contains($firstSheetName, 'SOBAT') || str_contains($headerStr, 'SOBAT') || str_contains($headerStr, 'NIK') || str_contains($headerStr, 'NAMA LENGKAP');
        $isKegiatan = str_contains($firstSheetName, 'ANGGARAN') || str_contains($firstSheetName, 'KEGIATAN') || str_contains($headerStr, 'MAK') || str_contains($headerStr, 'AKUN') || str_contains($headerStr, 'KODE MATA ANGGARAN');
        $isDipaPok = str_contains($firstSheetName, 'RKK_MULTIYEAR') || str_contains($firstSheetName, 'RINCIAN KERTAS KERJA');

        // Deteksi MITRA KEPKA/SE (format kolom: Nama Lengkap, Posisi, Status Seleksi, SOBAT ID)
        $isMitraKepkaSE = str_contains($headerStr, 'STATUS SELEKSI') || str_contains($headerStr, 'POSISI DAFTAR') || str_contains($headerStr, 'ALAMAT PROVINSI');

        if ($isMitraKepkaSE) {
            $parsed = $this->parseMitraKepkaSE($fullPath);

            return view('import.mitra-kepka-preview', [
                'rows' => $parsed['rows'],
                'stats' => $parsed['stats'],
                'path' => $filename,
            ]);
        }

        if ($isDipaPok) {
            $jenisDokumen = $request->jenis_dokumen ?? 'DIPA';
            $revisiKe = $request->revisi_ke ?? 'DIPA Awal';
            $tahun = $request->tahun ?? date('Y');

            $parsed = \App\Support\DipaPokParser::parse($fullPath, $jenisDokumen, $revisiKe, $tahun);

            return view('import.dipa-pok-preview', [
                'rows' => $parsed['rows'],
                'stats' => $parsed['stats'],
                'path' => $filename,
                'jenisDokumen' => $jenisDokumen,
                'revisiKe' => $revisiKe,
                'tahun' => $tahun,
            ]);
        }

        if ($isMitra && !$isKegiatan) {
            return app(\App\Http\Controllers\MitraController::class)->importPreviewFromPath($filename);
        }

        if ($isKegiatan && !$isMitra) {
            return app(\App\Http\Controllers\KegiatanController::class)->importPreviewFromPath($filename);
        }

        $isUniversal = false;
        $monthlySheets = [];

        foreach ($sheetNames as $name) {
            $upper = strtoupper(trim($name));
            if (str_contains($upper, 'DATA MITRA') || str_contains($upper, 'MATA ANGGARAN') || str_contains($upper, 'ALOKASI')) {
                $isUniversal = true;
            }
            if (in_array($upper, ['JANUARI', 'FEBRUARI', 'MARET', 'APRIL', 'MEI', 'JUNI', 'JULI', 'AGUSTUS', 'SEPTEMBR', 'OKTOBER', 'NOPEMBER', 'DESEMBER'])) {
                $monthlySheets[] = $name;
            }
        }

        return view('import.preview', [
            'sheets' => $monthlySheets,
            'allSheetNames' => $sheetNames,
            'isUniversal' => $isUniversal,
            'path' => $filename
        ]);
    }

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

    public function process(Request $request)
    {
        ini_set('memory_limit', '-1');
        set_time_limit(0);

        $request->validate(['path' => 'required|string']);

        $filename = $request->path;
        $fullPath = storage_path('app/imports/' . $filename);

        if (!file_exists($fullPath)) {
            return redirect()->route('import.index')->with('error', 'File temporari impor tidak ditemukan. Silakan upload ulang.');
        }

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

        // Cache Bidangs
        $bidangDistribusi = Bidang::firstOrCreate(['nama' => 'Distribusi']);
        $bidangNeraca = Bidang::firstOrCreate(['nama' => 'Neraca']);
        $bidangProduksi = Bidang::firstOrCreate(['nama' => 'Produksi']);
        $bidangSosial = Bidang::firstOrCreate(['nama' => 'Sosial']);
        $bidangIpds = Bidang::firstOrCreate(['nama' => 'IPDS']);
        $bidangUmum = Bidang::firstOrCreate(['nama' => 'Bagian Umum']);

        $bidangNameMap = [
            'distribusi' => $bidangDistribusi,
            'neraca' => $bidangNeraca,
            'produksi' => $bidangProduksi,
            'sosial' => $bidangSosial,
            'ipds' => $bidangIpds,
            'bagian umum' => $bidangUmum,
            'umum' => $bidangUmum,
        ];

        $reader = IOFactory::createReaderForFile($fullPath);
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($fullPath);
        $sheetNames = $spreadsheet->getSheetNames();

        // Check if file is DIPA/POK (RKK_MULTIYEAR_SATKER sheet)
        $firstSheetTitle = strtoupper(trim($spreadsheet->getSheet(0)->getTitle()));
        $isDipaPok = str_contains($firstSheetTitle, 'RKK_MULTIYEAR') || str_contains($firstSheetTitle, 'RINCIAN KERTAS KERJA');

        // Check if file is Universal All-in-One Template
        $isUniversal = false;
        foreach ($sheetNames as $sName) {
            $upper = strtoupper(trim($sName));
            if (str_contains($upper, 'DATA MITRA') || str_contains($upper, 'MATA ANGGARAN') || str_contains($upper, 'ALOKASI PENUGASAN') || str_contains($upper, 'ALOKASI')) {
                $isUniversal = true;
                break;
            }
        }

        DB::beginTransaction();
        try {
            // Handle DIPA/POK Import
            if ($isDipaPok) {
                $jenisDokumen = $request->jenis_dokumen ?? 'DIPA';
                $revisiKe = $request->revisi_ke ?? 'DIPA Awal';
                $tahun = $request->tahun ?? date('Y');

                $parsed = \App\Support\DipaPokParser::parse($fullPath, $jenisDokumen, $revisiKe, $tahun);
                $rows = $parsed['rows'];

                // Ensure bidangs exist
                $bidangNames = array_unique(array_column($rows, 'bidang'));
                $bidangModels = [];
                foreach ($bidangNames as $bn) {
                    $bidangModels[strtolower($bn)] = Bidang::firstOrCreate(['nama' => $bn]);
                }

                $count = 0;
                foreach ($rows as $row) {
                    $bidangKey = strtolower($row['bidang']);
                    $bidangId = $bidangModels[$bidangKey]->id ?? null;

                    Kegiatan::updateOrCreate(
                        [
                            'kode_mata_anggaran' => $row['kode_mata_anggaran'],
                            'tahun' => $row['tahun'],
                        ],
                        [
                            'nama' => $row['nama'],
                            'bidang_id' => $bidangId,
                            'jumlah' => $row['jumlah'],
                            'satuan' => $row['satuan'],
                            'harga' => $row['harga'],
                            'total' => $row['total'],
                            'source_file' => $row['source_file'],
                            'revisi_ke' => $row['revisi_ke'],
                            'jenis_dokumen' => $row['jenis_dokumen'],
                        ]
                    );
                    $count++;
                }

                // Auto-create Periode for the year if not exists
                $bulanNames = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
                for ($m = 1; $m <= 12; $m++) {
                    Periode::firstOrCreate([
                        'tahun' => (int) $tahun,
                        'bulan' => $bulanNames[$m - 1],
                        'bulan_angka' => $m,
                    ]);
                }

                DB::commit();
                return redirect()->route('import.index')->with('success', "Import {$jenisDokumen} ({$revisiKe}) berhasil! {$count} data kegiatan tersimpan.");
            }

            if ($isUniversal) {
                $mitraCount = 0;
                $kegiatanCount = 0;
                $alokasiCount = 0;

                // 1. Process Sheet DATA MITRA
                foreach ($sheetNames as $sName) {
                    if (str_contains(strtoupper($sName), 'MITRA')) {
                        $mSheet = $spreadsheet->getSheetByName($sName);
                        if ($mSheet) {
                            $mMax = $mSheet->getHighestRow();
                            for ($r = 2; $r <= $mMax; $r++) {
                                // Col D is Nama (or Col C if older format)
                                $nama = trim((string)$this->getCellValue($mSheet->getCell('D' . $r)));
                                $idSobat = trim((string)$this->getCellValue($mSheet->getCell('B' . $r)));
                                $nik = trim((string)$this->getCellValue($mSheet->getCell('C' . $r)));
                                
                                if (empty($nama) || $nama === 'NAMA LENGKAP MITRA' || str_starts_with($nama, '=')) {
                                    // Try Col C for older template format
                                    $altNama = $nik;
                                    if (!empty($altNama) && !is_numeric($altNama) && $altNama !== 'NAMA LENGKAP MITRA') {
                                        $nama = $altNama;
                                        $nik = '';
                                    } else {
                                        continue;
                                    }
                                }

                                $posisi = trim((string)$this->getCellValue($mSheet->getCell('E' . $r)));
                                $jk = trim((string)$this->getCellValue($mSheet->getCell('F' . $r)));
                                $jk = in_array(strtoupper($jk), ['L', 'LAKI-LAKI', '1']) ? 'L' : (in_array(strtoupper($jk), ['P', 'PEREMPUAN', '2']) ? 'P' : null);
                                $noHp = trim((string)$this->getCellValue($mSheet->getCell('G' . $r)));
                                $email = trim((string)$this->getCellValue($mSheet->getCell('H' . $r)));
                                $npwp = trim((string)$this->getCellValue($mSheet->getCell('I' . $r)));
                                $pendidikan = trim((string)$this->getCellValue($mSheet->getCell('J' . $r)));
                                $pekerjaan = trim((string)$this->getCellValue($mSheet->getCell('K' . $r)));
                                $kabKota = trim((string)$this->getCellValue($mSheet->getCell('L' . $r)));
                                $kecamatan = trim((string)$this->getCellValue($mSheet->getCell('M' . $r)));
                                $desaKelurahan = trim((string)$this->getCellValue($mSheet->getCell('N' . $r)));
                                $alamat = trim((string)$this->getCellValue($mSheet->getCell('O' . $r)));

                                $existing = Mitra::where('nama', $nama)
                                    ->orWhere(function($q) use ($idSobat, $nik) {
                                        if (!empty($idSobat)) $q->where('id_sobat', $idSobat);
                                        if (!empty($nik)) $q->orWhere('nik', $nik);
                                    })->first();

                                $payload = array_filter([
                                    'nama' => $nama,
                                    'id_sobat' => $idSobat ?: null,
                                    'nik' => $nik ?: null,
                                    'posisi' => $posisi ?: null,
                                    'jk' => $jk,
                                    'no_hp' => $noHp ?: null,
                                    'email' => $email ?: null,
                                    'npwp' => $npwp ?: null,
                                    'pendidikan' => $pendidikan ?: null,
                                    'pekerjaan' => $pekerjaan ?: null,
                                    'kabupaten_kota' => $kabKota ?: null,
                                    'kecamatan' => $kecamatan ?: null,
                                    'desa' => $desaKelurahan ?: null,
                                    'alamat_detail' => $alamat ?: null,
                                    'alamat' => $alamat ?: null,
                                ]);

                                if ($existing) {
                                    $existing->update($payload);
                                } else {
                                    Mitra::create($payload);
                                }
                                $mitraCount++;
                            }
                        }
                        break;
                    }
                }

                // 2. Process Sheet MATA ANGGARAN
                foreach ($sheetNames as $sName) {
                    if (str_contains(strtoupper($sName), 'ANGGARAN') || str_contains(strtoupper($sName), 'KEGIATAN')) {
                        $kSheet = $spreadsheet->getSheetByName($sName);
                        if ($kSheet) {
                            $kMax = $kSheet->getHighestRow();
                            for ($r = 2; $r <= $kMax; $r++) {
                                $kegNama = trim((string)$this->getCellValue($kSheet->getCell('C' . $r)));
                                if (empty($kegNama) || $kegNama === 'NAMA KEGIATAN STATISTIK' || str_starts_with($kegNama, '=')) continue;

                                $bidangStr = strtolower(trim((string)$this->getCellValue($kSheet->getCell('B' . $r))));
                                $targetBidang = $bidangDistribusi;
                                foreach ($bidangNameMap as $bKey => $bModel) {
                                    if (str_contains($bidangStr, $bKey)) {
                                        $targetBidang = $bModel;
                                        break;
                                    }
                                }

                                $mak = trim((string)$this->getCellValue($kSheet->getCell('D' . $r)));
                                $tahun = (int)($this->getCellValue($kSheet->getCell('E' . $r)) ?: date('Y'));
                                $satuan = trim((string)$this->getCellValue($kSheet->getCell('F' . $r))) ?: 'Dokumen';
                                $tarif = (float)($this->getCellValue($kSheet->getCell('G' . $r)) ?: 0);
                                $targetVol = (float)($this->getCellValue($kSheet->getCell('H' . $r)) ?: 0);

                                $keg = Kegiatan::firstOrCreate(
                                    ['nama' => $kegNama],
                                    [
                                        'bidang_id' => $targetBidang->id,
                                        'kode_mata_anggaran' => $mak ?: null,
                                        'tahun' => $tahun,
                                        'satuan' => $satuan,
                                        'harga_satuan' => $tarif,
                                        'target_volume' => $targetVol > 0 ? $targetVol : null,
                                    ]
                                );

                                $keg->update(array_filter([
                                    'bidang_id' => $targetBidang->id,
                                    'kode_mata_anggaran' => $mak ?: $keg->kode_mata_anggaran,
                                    'tahun' => $tahun,
                                    'satuan' => $satuan,
                                    'harga_satuan' => $tarif > 0 ? $tarif : $keg->harga_satuan,
                                    'target_volume' => $targetVol > 0 ? $targetVol : $keg->target_volume,
                                ]));

                                $kegiatanCount++;
                            }
                        }
                        break;
                    }
                }

                // 3. Process Sheet ALOKASI PENUGASAN
                foreach ($sheetNames as $sName) {
                    if (str_contains(strtoupper($sName), 'ALOKASI') || str_contains(strtoupper($sName), 'PENUGASAN')) {
                        $aSheet = $spreadsheet->getSheetByName($sName);
                        if ($aSheet) {
                            $aMax = $aSheet->getHighestRow();
                            for ($r = 2; $r <= $aMax; $r++) {
                                $namaMitra = trim((string)$this->getCellValue($aSheet->getCell('D' . $r)));
                                $namaKeg = trim((string)$this->getCellValue($aSheet->getCell('E' . $r)));
                                if (empty($namaMitra) || empty($namaKeg) || str_starts_with($namaMitra, '=')) continue;

                                $tahun = (int)($this->getCellValue($aSheet->getCell('B' . $r)) ?: date('Y'));
                                $bulanRaw = trim((string)$this->getCellValue($aSheet->getCell('C' . $r)));

                                // Resolve Month
                                $bulanUpper = strtoupper($bulanRaw);
                                $bulanInfo = $bulanMap[$bulanUpper] ?? null;
                                if (!$bulanInfo && is_numeric($bulanRaw)) {
                                    $mNum = (int)$bulanRaw;
                                    foreach ($bulanMap as $k => $bData) {
                                        if ($bData['angka'] === $mNum) {
                                            $bulanInfo = $bData;
                                            break;
                                        }
                                    }
                                }
                                if (!$bulanInfo) {
                                    $bulanInfo = ['bulan' => 'Januari', 'angka' => 1];
                                }

                                $periode = Periode::firstOrCreate([
                                    'tahun' => $tahun,
                                    'bulan' => $bulanInfo['bulan'],
                                    'bulan_angka' => $bulanInfo['angka'],
                                ]);

                                $mitra = Mitra::where('nama', $namaMitra)
                                    ->orWhere('id_sobat', $namaMitra)
                                    ->first() ?? Mitra::create(['nama' => $namaMitra]);

                                $kegiatan = Kegiatan::where('nama', $namaKeg)->first() ?? Kegiatan::create([
                                    'nama' => $namaKeg,
                                    'bidang_id' => $bidangDistribusi->id,
                                    'tahun' => $tahun,
                                ]);

                                $vol = (float)($this->getCellValue($aSheet->getCell('F' . $r)) ?: 1);
                                $satuan = trim((string)$this->getCellValue($aSheet->getCell('G' . $r))) ?: ($kegiatan->satuan ?: 'Dokumen');
                                $tarif = (float)($this->getCellValue($aSheet->getCell('H' . $r)) ?: ($kegiatan->harga_satuan ?: 0));
                                $totalHonor = (float)($this->getCellValue($aSheet->getCell('I' . $r)) ?: ($vol * $tarif));
                                $tglSpk = trim((string)$this->getCellValue($aSheet->getCell('J' . $r)));

                                AlokasiHonor::updateOrCreate(
                                    [
                                        'mitra_id' => $mitra->id,
                                        'periode_id' => $periode->id,
                                        'kegiatan_id' => $kegiatan->id,
                                    ],
                                    [
                                        'nominal' => $totalHonor,
                                        'volume' => $vol,
                                        'satuan' => $satuan,
                                        'tanggal_spk' => (!empty($tglSpk) && !str_starts_with($tglSpk, '=')) ? $tglSpk : null,
                                    ]
                                );

                                $alokasiCount++;
                            }
                        }
                        break;
                    }
                }

                DB::commit();
                return redirect()->route('import.index')->with(
                    'success',
                    "Import Template Universal Berhasil! Sinkronisasi: {$mitraCount} Data Mitra, {$kegiatanCount} Mata Anggaran, dan {$alokasiCount} Alokasi Penugasan."
                );
            }

            // Otherwise, process as Multi-Sheet MANTRA Matrix
            $selectedSheets = $request->sheets ?? [];
            if (empty($selectedSheets)) {
                return back()->with('error', 'Pilih minimal 1 sheet untuk diimpor.');
            }

            $totalImported = 0;
            $year = (int)($request->tahun ?? date('Y'));

            // Auto-extract DB Mitra sheet if present
            foreach ($spreadsheet->getSheetNames() as $sName) {
                if (str_contains(strtoupper($sName), 'DB MITRA') || (str_contains(strtoupper($sName), 'MITRA') && !isset($bulanMap[strtoupper($sName)]))) {
                    $mitraSheet = $spreadsheet->getSheetByName($sName);
                    if ($mitraSheet) {
                        $mHighestRow = $mitraSheet->getHighestRow();
                        for ($mr = 3; $mr <= min($mHighestRow, 3000); $mr++) {
                            $mNama = trim((string)$this->getCellValue($mitraSheet->getCell('B' . $mr)));
                            if (empty($mNama) || $mNama === 'Nama') continue;

                            $mAlamat = trim((string)$this->getCellValue($mitraSheet->getCell('C' . $mr)));
                            $mPekerjaan = trim((string)$this->getCellValue($mitraSheet->getCell('E' . $mr)));
                            $mNik = trim((string)$this->getCellValue($mitraSheet->getCell('G' . $mr)));
                            $mPosisi = trim((string)$this->getCellValue($mitraSheet->getCell('H' . $mr)));
                            $mStatusSeleksi = trim((string)$this->getCellValue($mitraSheet->getCell('I' . $mr)));
                            $mEmail = trim((string)$this->getCellValue($mitraSheet->getCell('J' . $mr)));
                            $mTglLahir = \App\Support\ExcelStyler::parseDate($this->getCellValue($mitraSheet->getCell('Q' . $mr)));
                            $mNpwp = trim((string)$this->getCellValue($mitraSheet->getCell('R' . $mr)));
                            $mJkRaw = trim((string)$this->getCellValue($mitraSheet->getCell('S' . $mr)));
                            $mJk = in_array(strtoupper($mJkRaw), ['L', 'LAKI-LAKI', '1']) ? 'L' : (in_array(strtoupper($mJkRaw), ['P', 'PEREMPUAN', '2']) ? 'P' : null);
                            $mAgama = trim((string)$this->getCellValue($mitraSheet->getCell('T' . $mr)));
                            $mStatusKawin = trim((string)$this->getCellValue($mitraSheet->getCell('U' . $mr)));
                            $mPendidikan = trim((string)$this->getCellValue($mitraSheet->getCell('V' . $mr)));
                            $mNoHp = trim((string)$this->getCellValue($mitraSheet->getCell('Y' . $mr)));
                            
                            $expSp = (int)$this->getCellValue($mitraSheet->getCell('AA' . $mr)) === 1;
                            $expSt = (int)$this->getCellValue($mitraSheet->getCell('AB' . $mr)) === 1;
                            $expSe = (int)$this->getCellValue($mitraSheet->getCell('AC' . $mr)) === 1;
                            $expSusenas = (int)$this->getCellValue($mitraSheet->getCell('AD' . $mr)) === 1;
                            $expSakernas = (int)$this->getCellValue($mitraSheet->getCell('AE' . $mr)) === 1;
                            $expSbh = (int)$this->getCellValue($mitraSheet->getCell('AF' . $mr)) === 1;
                            $mCatatan = trim((string)$this->getCellValue($mitraSheet->getCell('AG' . $mr)));
                            $mPosisiDaftar = trim((string)$this->getCellValue($mitraSheet->getCell('AH' . $mr)));
                            $mSobatId = trim((string)$this->getCellValue($mitraSheet->getCell('AJ' . $mr)));
                            $nilaiRaw = $this->getCellValue($mitraSheet->getCell('AK' . $mr));
                            $mNilaiUjian = is_numeric($nilaiRaw) ? (float)$nilaiRaw : null;

                            $existingMitra = Mitra::where('nama', $mNama)
                                ->orWhere(function($q) use ($mSobatId, $mNik) {
                                    if (!empty($mSobatId)) $q->where('id_sobat', $mSobatId);
                                    if (!empty($mNik)) $q->orWhere('nik', $mNik);
                                })->first();

                            $sobatPayload = array_filter([
                                'nama' => $mNama,
                                'id_sobat' => $mSobatId ?: null,
                                'nik' => $mNik ?: null,
                                'posisi' => $mPosisi ?: null,
                                'posisi_daftar' => $mPosisiDaftar ?: null,
                                'status_seleksi' => $mStatusSeleksi ?: null,
                                'email' => $mEmail ?: null,
                                'tanggal_lahir' => $mTglLahir ?: null,
                                'npwp' => $mNpwp ?: null,
                                'jk' => $mJk ?: null,
                                'agama' => $mAgama ?: null,
                                'status_perkawinan' => $mStatusKawin ?: null,
                                'pendidikan' => $mPendidikan ?: null,
                                'no_hp' => $mNoHp ?: null,
                                'alamat' => ($mAlamat && !str_starts_with($mAlamat, '=') && !str_contains($mAlamat, '#')) ? $mAlamat : null,
                                'pekerjaan' => ($mPekerjaan && !str_starts_with($mPekerjaan, '=') && !str_contains($mPekerjaan, '#')) ? $mPekerjaan : null,
                                'catatan_mitra' => $mCatatan ?: null,
                                'nilai_ujian' => $mNilaiUjian,
                            ]);
                            $sobatPayload['exp_sp'] = $expSp;
                            $sobatPayload['exp_st'] = $expSt;
                            $sobatPayload['exp_se'] = $expSe;
                            $sobatPayload['exp_susenas'] = $expSusenas;
                            $sobatPayload['exp_sakernas'] = $expSakernas;
                            $sobatPayload['exp_sbh'] = $expSbh;

                            if ($existingMitra) {
                                $existingMitra->update($sobatPayload);
                            } else {
                                Mitra::create($sobatPayload);
                            }
                        }
                    }
                    break;
                }
            }

            // Process monthly sheets
            foreach ($selectedSheets as $sheetName) {
                $sheet = $spreadsheet->getSheetByName($sheetName);
                if (!$sheet) continue;

                $bulanUpper = strtoupper($sheetName);
                if (!isset($bulanMap[$bulanUpper])) continue;

                $bulanInfo = $bulanMap[$bulanUpper];

                $periode = Periode::firstOrCreate([
                    'tahun' => $year,
                    'bulan' => $bulanInfo['bulan'],
                    'bulan_angka' => $bulanInfo['angka'],
                ]);

                $highestColStr = $sheet->getHighestColumn();
                $highestCol = Coordinate::columnIndexFromString($highestColStr);
                $highestRow = $sheet->getHighestRow();

                // Scan Column Headers (K is column 11)
                $currentBidang = $bidangDistribusi;
                $kegiatanByCol = [];

                for ($c = 11; $c <= min($highestCol, 100); $c++) {
                    $cLetter = Coordinate::stringFromColumnIndex($c);
                    $r1 = trim((string)$this->getCellValue($sheet->getCell($cLetter . '1')));

                    if (!empty($r1) && !in_array(strtoupper($r1), ['PILIH TIM', 'STATUS SBML', 'JUMLAH TOTAL PENDAPATAN MITRA'])) {
                        $lowerB = strtolower($r1);
                        foreach ($bidangNameMap as $bKey => $bModel) {
                            if (str_contains($lowerB, $bKey)) {
                                $currentBidang = $bModel;
                                break;
                            }
                        }
                    }

                    $kegNama = trim((string)$this->getCellValue($sheet->getCell($cLetter . '3')));
                    if (empty($kegNama) || str_starts_with($kegNama, '=') || str_contains($kegNama, '#REF!')) {
                        continue;
                    }

                    $mak = trim((string)$this->getCellValue($sheet->getCell($cLetter . '5')));
                    $cleanMak = (!empty($mak) && !str_starts_with($mak, '=') && !str_contains($mak, '#')) ? $mak : null;

                    $tarifRaw = $this->getCellValue($sheet->getCell($cLetter . '4'));
                    $tarif = is_numeric($tarifRaw) ? (float)$tarifRaw : 0;

                    $kegiatan = Kegiatan::firstOrCreate(
                        ['nama' => $kegNama],
                        [
                            'bidang_id' => $currentBidang->id,
                            'kode_mata_anggaran' => $cleanMak,
                            'harga_satuan' => $tarif,
                            'tahun' => $year,
                        ]
                    );

                    if ($cleanMak && empty($kegiatan->kode_mata_anggaran)) {
                        $kegiatan->update(['kode_mata_anggaran' => $cleanMak]);
                    }
                    if ($tarif > 0 && empty($kegiatan->harga_satuan)) {
                        $kegiatan->update(['harga_satuan' => $tarif]);
                    }

                    $kegiatanByCol[$c] = [
                        'kegiatan' => $kegiatan,
                        'tarif' => $tarif,
                    ];
                }

                // Process rows starting from row 7
                for ($row = 7; $row <= min($highestRow, 3000); $row++) {
                    $namaMitra = trim((string)$this->getCellValue($sheet->getCell('B' . $row)));
                    if (empty($namaMitra) || $namaMitra === 'Nama' || str_starts_with($namaMitra, '=')) {
                        continue;
                    }

                    $alamat = trim((string)$this->getCellValue($sheet->getCell('C' . $row)));
                    $pekerjaan = trim((string)$this->getCellValue($sheet->getCell('D' . $row)));
                    $kodeAlamat = trim((string)$this->getCellValue($sheet->getCell('E' . $row)));
                    $jkRaw = trim((string)$this->getCellValue($sheet->getCell('F' . $row)));
                    $jk = ($jkRaw == '1' || strtolower($jkRaw) == 'l') ? 'L' : (($jkRaw == '2' || strtolower($jkRaw) == 'p') ? 'P' : null);

                    $cleanAlamat = (!empty($alamat) && !str_starts_with($alamat, '=') && !str_contains($alamat, '#')) ? $alamat : null;
                    $cleanPekerjaan = (!empty($pekerjaan) && !str_starts_with($pekerjaan, '=') && !str_contains($pekerjaan, '#')) ? $pekerjaan : null;

                    $mitra = Mitra::firstOrCreate(
                        ['nama' => $namaMitra],
                        [
                            'alamat' => $cleanAlamat,
                            'pekerjaan' => $cleanPekerjaan,
                            'kode_alamat' => $kodeAlamat,
                            'jk' => $jk,
                        ]
                    );

                    if ($cleanPekerjaan && (empty($mitra->pekerjaan) || $mitra->pekerjaan === 'Lainnya/ Belum Bekerja' || str_starts_with($mitra->pekerjaan, '='))) {
                        $mitra->update(['pekerjaan' => $cleanPekerjaan]);
                    }
                    if ($cleanAlamat && (empty($mitra->alamat) || $mitra->alamat === 'Kabupaten Tasikmalaya' || str_starts_with($mitra->alamat, '='))) {
                        $mitra->update(['alamat' => $cleanAlamat]);
                    }

                    // Loop through each activity column
                    foreach ($kegiatanByCol as $colIdx => $kegInfo) {
                        $colLetter = Coordinate::stringFromColumnIndex($colIdx);
                        $cellValRaw = $this->getCellValue($sheet->getCell($colLetter . $row));

                        if (is_numeric($cellValRaw) && (float)$cellValRaw > 0) {
                            $nominal = (float)$cellValRaw;
                            $tarif = $kegInfo['tarif'];
                            $volume = 1;

                            if ($tarif > 0) {
                                if ($nominal > $tarif && fmod($nominal, $tarif) == 0) {
                                    $volume = round($nominal / $tarif, 2);
                                } elseif ($nominal <= 100) {
                                    // User entered volume
                                    $volume = $nominal;
                                    $nominal = $volume * $tarif;
                                }
                            }

                            AlokasiHonor::updateOrCreate(
                                [
                                    'mitra_id' => $mitra->id,
                                    'periode_id' => $periode->id,
                                    'kegiatan_id' => $kegInfo['kegiatan']->id,
                                ],
                                [
                                    'nominal' => $nominal,
                                    'volume' => $volume,
                                    'satuan' => 'Dokumen',
                                ]
                            );

                            $totalImported++;
                        }
                    }
                }
            }

            DB::commit();
            return redirect()->route('import.index')->with('success', "Import berhasil! {$totalImported} alokasi honor kegiatan berhasil dipetakan secara presisi.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error saat mengimpor data: ' . $e->getMessage());
        }
    }

    /**
     * Parse file Mitra Baru menjadi array data mitra
     */
    private function parseMitraKepkaSE(string $filePath): array
    {
        ini_set('memory_limit', '512M');
        $reader = IOFactory::createReader('Xlsx');
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($filePath);
        $sheet = $spreadsheet->getActiveSheet();

        $data = $sheet->toArray(null, true, true, true);
        $rows = [];

        $existingSobat = \App\Models\Mitra::pluck('id_sobat')->filter()->map(fn($v) => strtoupper(trim($v)))->toArray();
        $existingNama = \App\Models\Mitra::pluck('nama')->filter()->map(fn($v) => strtoupper(trim($v)))->toArray();

        $seenSobat = [];
        $seenNama = [];

        for ($row = 2; $row <= count($data); $row++) {
            $nama = trim((string)($data[$row]['A'] ?? ''));
            if (empty($nama)) continue;

            $namaLower = strtolower($nama);
            if (str_starts_with($nama, '*')) continue;
            if (str_contains($namaLower, 'keterangan')) continue;
            if (str_contains($namaLower, 'umur dihitung')) continue;

            $upperNama = strtoupper($nama);
            $idSobat = trim((string)($data[$row]['U'] ?? ''));
            $upperSobat = strtoupper($idSobat);

            if (in_array($upperNama, $existingNama) || isset($seenNama[$upperNama])) continue;
            if (!empty($idSobat) && (in_array($upperSobat, $existingSobat) || isset($seenSobat[$upperSobat]))) continue;

            $seenNama[$upperNama] = true;
            if (!empty($idSobat)) $seenSobat[$upperSobat] = true;

            $ttl = trim((string)($data[$row]['J'] ?? ''));
            $tanggalLahir = null;
            $ttlParts = explode(',', $ttl);
            if (count($ttlParts) >= 2) {
                $dateStr = trim($ttlParts[count($ttlParts) - 1]);
                $bulanMap = [
                    'Januari' => '01', 'Februari' => '02', 'Maret' => '03', 'April' => '04',
                    'Mei' => '05', 'Juni' => '06', 'Juli' => '07', 'Agustus' => '08',
                    'September' => '09', 'Oktober' => '10', 'November' => '11', 'Desember' => '12',
                ];
                foreach ($bulanMap as $namaBulan => $angka) {
                    $dateStr = str_replace($namaBulan, $angka, $dateStr);
                }
                $dateParts = explode(' ', trim($dateStr));
                if (count($dateParts) == 3) {
                    $tanggalLahir = $dateParts[2] . '-' . $dateParts[1] . '-' . str_pad($dateParts[0], 2, '0', STR_PAD_LEFT);
                }
            }

            $jk = strtoupper(trim((string)($data[$row]['L'] ?? '')));
            $jk = ($jk === 'LK' || $jk === 'L') ? 'L' : (($jk === 'PR' || $jk === 'P') ? 'P' : null);

            $ynToBool = fn($val) => strtoupper(trim((string)$val)) === 'YA';

            $rows[] = [
                'nama' => $nama,
                'posisi' => trim((string)($data[$row]['B'] ?? '')),
                'status_seleksi' => trim((string)($data[$row]['C'] ?? '')),
                'posisi_daftar' => trim((string)($data[$row]['D'] ?? '')),
                'alamat_detail' => trim((string)($data[$row]['E'] ?? '')),
                'kode_alamat' => trim((string)($data[$row]['F'] ?? '')),
                'kabupaten_kota' => trim((string)($data[$row]['G'] ?? '')),
                'kecamatan' => trim((string)($data[$row]['H'] ?? '')),
                'desa' => trim((string)($data[$row]['I'] ?? '')),
                'tanggal_lahir' => $tanggalLahir,
                'jk' => $jk,
                'status_perkawinan' => trim((string)($data[$row]['M'] ?? '')),
                'pendidikan' => trim((string)($data[$row]['N'] ?? '')),
                'pekerjaan' => trim((string)($data[$row]['O'] ?? '')),
                'no_hp' => trim((string)($data[$row]['T'] ?? '')),
                'id_sobat' => $idSobat,
                'email' => trim((string)($data[$row]['V'] ?? '')),
                'exp_sp' => $ynToBool($data[$row]['AG'] ?? '') ? 'Ya' : '',
                'exp_st' => $ynToBool($data[$row]['AH'] ?? '') ? 'Ya' : '',
                'exp_se' => $ynToBool($data[$row]['AI'] ?? '') ? 'Ya' : '',
                'exp_susenas' => $ynToBool($data[$row]['AJ'] ?? '') ? 'Ya' : '',
                'exp_sakernas' => $ynToBool($data[$row]['AK'] ?? '') ? 'Ya' : '',
                'exp_sbh' => $ynToBool($data[$row]['AL'] ?? '') ? 'Ya' : '',
            ];
        }

        $stats = [
            'total_items' => count($rows),
            'total_duplicate_skipped' => count($data) - 1 - count($rows),
        ];

        return ['rows' => $rows, 'stats' => $stats];
    }

    /**
     * Process import Mitra Baru
     */
    public function processMitraKepkaSE(Request $request)
    {
        ini_set('memory_limit', '512M');
        set_time_limit(0);
        $request->validate(['path' => 'required|string']);

        $filename = $request->path;
        $fullPath = storage_path('app/imports/' . $filename);

        if (!file_exists($fullPath)) {
            return redirect()->route('import.index')->with('error', 'File tidak ditemukan. Upload ulang.');
        }

        $parsed = $this->parseMitraKepkaSE($fullPath);
        $rows = $parsed['rows'];

        DB::beginTransaction();
        try {
            $count = 0;
            foreach ($rows as $row) {
                if (Mitra::where('id_sobat', $row['id_sobat'])->exists() || Mitra::where('nama', $row['nama'])->exists()) {
                    continue;
                }
                Mitra::create($row);
                $count++;
            }
            DB::commit();
            return redirect()->route('import.index')->with('success', 'Import Mitra Baru berhasil! ' . $count . ' mitra baru tersimpan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error saat import: ' . $e->getMessage());
        }
    }
}