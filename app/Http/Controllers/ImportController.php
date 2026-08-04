<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportController extends Controller
{
    public function index()
    {
        return view('import.index');
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
        
        $sheets = [];
        foreach ($spreadsheet->getSheetNames() as $name) {
            if (in_array(strtoupper($name), ['JANUARI','FEBRUARI','MARET','APRIL','MEI','JUNI','JULI','AGUSTUS','SEPTEMBR','OKTOBER','NOPEMBER','DESEMBER'])) {
                $sheets[] = $name;
            }
        }
        
        return view('import.preview', ['sheets' => $sheets, 'path' => $filename]);
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

        $selectedSheets = $request->sheets ?? [];
        
        if (empty($selectedSheets)) {
            return back()->with('error', 'Pilih minimal 1 sheet');
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
        
        $bidangMap = [];
        $allBidang = \App\Models\Bidang::pluck('id', 'nama');
        foreach (\App\Models\Bidang::getNamaBidang() as $b) {
            $bidangMap[strtolower($b)] = $allBidang[$b] ?? \App\Models\Bidang::create(['nama' => $b])->id;
        }
        
        $reader = IOFactory::createReaderForFile($fullPath);
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($fullPath);

        $totalImported = 0;
        
        DB::beginTransaction();
        try {
            // Auto-extract DB Mitra sheet if present (read SOBAT ID & No Telp)
            foreach ($spreadsheet->getSheetNames() as $sName) {
                if (str_contains(strtoupper($sName), 'DB MITRA') || (str_contains(strtoupper($sName), 'MITRA') && !str_contains(strtoupper($sName), 'JANUARI'))) {
                    $mitraSheet = $spreadsheet->getSheetByName($sName);
                    if ($mitraSheet) {
                        $mHighestRow = $mitraSheet->getHighestRow();
                        for ($mr = 3; $mr <= $mHighestRow; $mr++) {
                            $mNama = trim((string)$this->getCellValue($mitraSheet->getCell('B' . $mr)));
                            if (empty($mNama) || $mNama === 'Nama') continue;

                            $mAlamat = trim((string)$this->getCellValue($mitraSheet->getCell('C' . $mr)));
                            $mPekerjaan = trim((string)$this->getCellValue($mitraSheet->getCell('E' . $mr)));
                            $mNoHp = trim((string)$this->getCellValue($mitraSheet->getCell('Y' . $mr)));
                            $mSobatId = trim((string)$this->getCellValue($mitraSheet->getCell('AJ' . $mr)));

                            $existingMitra = \App\Models\Mitra::where('nama', $mNama)->first();
                            if ($existingMitra) {
                                $existingMitra->update(array_filter([
                                    'id_sobat' => $mSobatId ?: $existingMitra->id_sobat,
                                    'no_hp' => $mNoHp ?: $existingMitra->no_hp,
                                    'alamat' => ($mAlamat && !str_starts_with($mAlamat, '=')) ? $mAlamat : $existingMitra->alamat,
                                    'pekerjaan' => ($mPekerjaan && !str_starts_with($mPekerjaan, '=')) ? $mPekerjaan : $existingMitra->pekerjaan,
                                ]));
                            } else {
                                \App\Models\Mitra::create([
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

            foreach ($selectedSheets as $sheetName) {
                $sheet = $spreadsheet->getSheetByName($sheetName);
                if (!$sheet) continue;
                
                $bulanInfo = $bulanMap[$sheetName];
                $year = $request->tahun ?? 2025;
                
                $periode = \App\Models\Periode::firstOrCreate([
                    'tahun' => $year,
                    'bulan' => $bulanInfo['bulan'],
                    'bulan_angka' => $bulanInfo['angka'],
                ]);
                
                $highestRow = $sheet->getHighestRow();
                
                for ($row = 7; $row <= $highestRow; $row++) {
                    $no = trim((string)($this->getCellValue($sheet->getCell('A'.$row)) ?? ''));
                    if (empty($no) || !is_numeric($no)) continue;
                    
                    $namaMitra = trim((string)($this->getCellValue($sheet->getCell('B'.$row)) ?? ''));
                    if (empty($namaMitra)) continue;
                    
                    $alamat = trim((string)($this->getCellValue($sheet->getCell('C'.$row)) ?? ''));
                    $pekerjaan = trim((string)($this->getCellValue($sheet->getCell('D'.$row)) ?? ''));
                    $kodeAlamat = trim((string)($this->getCellValue($sheet->getCell('E'.$row)) ?? ''));
                    $jk = trim((string)($this->getCellValue($sheet->getCell('F'.$row)) ?? ''));
                    $jk = ($jk == '1') ? 'L' : (($jk == '2') ? 'P' : $jk);
                    
                    $cleanAlamat = (!empty($alamat) && !str_starts_with($alamat, '=')) ? $alamat : null;
                    $cleanPekerjaan = (!empty($pekerjaan) && !str_starts_with($pekerjaan, '=')) ? $pekerjaan : null;

                    $mitra = \App\Models\Mitra::firstOrCreate(
                        ['nama' => $namaMitra],
                        ['alamat' => $cleanAlamat, 'pekerjaan' => $cleanPekerjaan, 'kode_alamat' => $kodeAlamat, 'jk' => $jk]
                    );
                    
                    if ($mitra->pekerjaan && str_starts_with($mitra->pekerjaan, '=')) {
                        $mitra->update(['pekerjaan' => $cleanPekerjaan]);
                    }
                    if ($mitra->alamat && str_starts_with($mitra->alamat, '=')) {
                        $mitra->update(['alamat' => $cleanAlamat]);
                    }

                    $namaKegiatan = trim((string)($this->getCellValue($sheet->getCell('I'.$row)) ?? ''));
                    if (!empty($namaKegiatan) && !str_contains($namaKegiatan, '#REF!')) {
                        $kegiatanNames = explode(';', $namaKegiatan);
                        $kegiatanName = trim($kegiatanNames[0]);
                        
                        if (!empty($kegiatanName) && !str_contains($kegiatanName, '#REF!')) {
                            $bidangFirst = \App\Models\Bidang::first();
                            $kegiatan = \App\Models\Kegiatan::firstOrCreate(
                                ['nama' => $kegiatanName],
                                [
                                    'bidang_id' => $bidangFirst->id ?? 1,
                                    'tahun' => $year,
                                ]
                            );
                            
                            $totalHonorRaw = $sheet->getCell('G'.$row)->getValue() ?? 0;
                            $totalHonor = is_numeric($totalHonorRaw) ? floatval($totalHonorRaw) : 0;
                            
                            \App\Models\AlokasiHonor::updateOrCreate(
                                ['mitra_id' => $mitra->id, 'periode_id' => $periode->id, 'kegiatan_id' => $kegiatan->id],
                                ['nominal' => $totalHonor]
                            );
                            $totalImported++;
                        }
                    }
                    
                    // SBML
                    $sbmlPencacahan = $sheet->getCell('BO'.$row)->getValue() ?? 0;
                    $sbmlPengolahan = $sheet->getCell('BS'.$row)->getValue() ?? 0;
                    
                    $sbmlPencacahanVal = is_numeric($sbmlPencacahan) ? floatval($sbmlPencacahan) : 0;
                    $sbmlPengolahanVal = is_numeric($sbmlPengolahan) ? floatval($sbmlPengolahan) : 0;
                    
                    if ($sbmlPencacahanVal > 0) {
                        \App\Models\Sbml::updateOrCreate(
                            ['mitra_id' => $mitra->id, 'periode_id' => $periode->id, 'jenis' => 'Pencacahan'],
                            ['nominal' => $sbmlPencacahanVal]
                        );
                    }
                    if ($sbmlPengolahanVal > 0) {
                        \App\Models\Sbml::updateOrCreate(
                            ['mitra_id' => $mitra->id, 'periode_id' => $periode->id, 'jenis' => 'Pengolahan'],
                            ['nominal' => $sbmlPengolahanVal]
                        );
                    }
                }
            }
            
            DB::commit();
            return redirect()->route('import.index')->with('success', "Import berhasil! $totalImported data diimport.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error: '.$e->getMessage());
        }
    }
}
