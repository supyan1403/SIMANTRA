<?php

namespace App\Http\Controllers;

use App\Models\Kegiatan;
use App\Models\Bidang;
use App\Models\KegiatanJadwal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;

class KegiatanController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $search = $request->query('search');
        $bidangId = $request->query('bidang_id');
        $tahun = $request->query('tahun');

        $tahunList = Kegiatan::select('tahun')->distinct()->whereNotNull('tahun')->orderBy('tahun', 'desc')->pluck('tahun');
        if ($tahunList->isEmpty()) {
            $tahunList = \App\Models\Periode::select('tahun')->distinct()->orderBy('tahun', 'desc')->pluck('tahun');
        }

        $query = Kegiatan::with(['bidang', 'jadwal', 'alokasiHonors.mitra', 'alokasiHonors.periode'])
            ->withCount('alokasiHonors as total_alokasi');

        // Filter Tahun
        if ($tahun && $tahun !== 'all') {
            $query->where('tahun', $tahun);
        }

        // Scope Operator Khusus Bidangnya (Bab 3.2)
        if ($user && $user->role === 'operator' && $user->bidang_id) {
            $bidangId = $user->bidang_id;
            $query->where('bidang_id', $user->bidang_id);
            $bidangs = Bidang::where('id', $user->bidang_id)->get();
        } else {
            if ($bidangId && $bidangId !== 'all') {
                $query->where('bidang_id', $bidangId);
            }
            $bidangs = Bidang::all();
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('kode_mata_anggaran', 'like', "%{$search}%");
            });
        }

        $kegiatans = $query->latest()->paginate(15)->withQueryString();

        return view('kegiatan.index', compact('kegiatans', 'bidangs', 'search', 'bidangId', 'tahunList', 'tahun'));
    }

    public function create()
    {
        if (auth()->user()?->role !== 'admin') {
            return redirect()->route('kegiatan.index')->with('error', 'Hanya Admin yang memiliki hak akses untuk mengelola data Mata Anggaran.');
        }

        $user = auth()->user();
        if ($user && $user->role === 'operator' && $user->bidang_id) {
            $bidangs = Bidang::where('id', $user->bidang_id)->get();
        } else {
            $bidangs = Bidang::all();
        }
        return view('kegiatan.form', compact('bidangs'));
    }

    public function store(Request $request)
    {
        if (auth()->user()?->role !== 'admin') {
            return redirect()->route('kegiatan.index')->with('error', 'Hanya Admin yang memiliki hak akses untuk mengelola data Mata Anggaran.');
        }

        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'short_name' => 'nullable|string|max:50',
            'format_spk' => 'nullable|string|max:255',
            'bidang_id' => 'required|exists:bidangs,id',
            'kode_mata_anggaran' => 'nullable|string|max:100',
            'tahun' => 'nullable|string|max:4',
            'jumlah' => 'nullable|numeric|min:0',
            'satuan' => 'nullable|string|max:50',
            'harga' => 'nullable|numeric|min:0',
            'total' => 'nullable|numeric|min:0',
            'tgl_mulai' => 'nullable|date',
            'tgl_selesai' => 'nullable|date',
        ]);

        $validated['jumlah'] = $validated['jumlah'] ?? 0;
        $validated['harga'] = $validated['harga'] ?? 0;
        if (!isset($validated['total']) || $validated['total'] === null || $validated['total'] === '') {
            $validated['total'] = $validated['jumlah'] * $validated['harga'];
        }

        Kegiatan::create($validated);

        return redirect()->route('kegiatan.index')->with('success', 'Mata Anggaran/Kegiatan berhasil ditambahkan.');
    }

    public function edit(Kegiatan $kegiatan)
    {
        if (auth()->user()?->role !== 'admin') {
            return redirect()->route('kegiatan.index')->with('error', 'Hanya Admin yang memiliki hak akses untuk mengelola data Mata Anggaran.');
        }

        $bidangs = Bidang::all();
        return view('kegiatan.form', compact('kegiatan', 'bidangs'));
    }

    public function update(Request $request, Kegiatan $kegiatan)
    {
        if (auth()->user()?->role !== 'admin') {
            return redirect()->route('kegiatan.index')->with('error', 'Hanya Admin yang memiliki hak akses untuk mengelola data Mata Anggaran.');
        }

        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'short_name' => 'nullable|string|max:50',
            'format_spk' => 'nullable|string|max:255',
            'bidang_id' => 'required|exists:bidangs,id',
            'kode_mata_anggaran' => 'nullable|string|max:100',
            'tahun' => 'nullable|string|max:4',
            'jumlah' => 'nullable|numeric|min:0',
            'satuan' => 'nullable|string|max:50',
            'harga' => 'nullable|numeric|min:0',
            'total' => 'nullable|numeric|min:0',
            'tgl_mulai' => 'nullable|date',
            'tgl_selesai' => 'nullable|date',
        ]);

        $validated['jumlah'] = $validated['jumlah'] ?? 0;
        $validated['harga'] = $validated['harga'] ?? 0;
        if (!isset($validated['total']) || $validated['total'] === null || $validated['total'] === '') {
            $validated['total'] = $validated['jumlah'] * $validated['harga'];
        }

        $kegiatan->update($validated);

        return redirect()->route('kegiatan.index')->with('success', 'Mata Anggaran/Kegiatan berhasil diperbarui.');
    }

    public function destroy(Kegiatan $kegiatan)
    {
        if (auth()->user()?->role !== 'admin') {
            return redirect()->route('kegiatan.index')->with('error', 'Hanya Admin yang memiliki hak akses untuk mengelola data Mata Anggaran.');
        }

        $kegiatan->delete();
        return redirect()->route('kegiatan.index')->with('success', 'Kegiatan berhasil dihapus.');
    }

    public function byBidang($bidangId)
    {
        $user = auth()->user();
        if ($user && $user->role === 'operator' && $user->bidang_id && $bidangId != $user->bidang_id) {
            return response()->json([]);
        }

        $kegiatans = Kegiatan::where('bidang_id', $bidangId)->orderBy('nama')->get(['id', 'nama', 'kode_mata_anggaran']);
        return response()->json($kegiatans);
    }

    public function export(Request $request)
    {
        $user = auth()->user();
        $search = $request->query('search');
        $bidangId = $request->query('bidang_id');

        $query = Kegiatan::with(['bidang', 'jadwal']);

        if ($user && $user->role === 'operator' && $user->bidang_id) {
            $query->where('bidang_id', $user->bidang_id);
        } elseif ($bidangId && $bidangId !== 'all') {
            $query->where('bidang_id', $bidangId);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('kode_mata_anggaran', 'like', "%{$search}%");
            });
        }

        $kegiatans = $query->orderBy('nama', 'asc')->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Data Mata Anggaran');

        $bulanNames = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        $headers = ['No.', 'Tahun', 'Nama Kegiatan', 'Tipe Kegiatan', 'Kode MAK', 'Bidang / Tim Kerja', 'Target Volume', 'Satuan', 'Harga Satuan (Rp)', 'Total Pagu Anggaran (Rp)'];
        foreach ($bulanNames as $b) {
            $headers[] = $b;
        }

        foreach ($headers as $i => $h) {
            $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i + 1);
            $sheet->setCellValue($col . '1', $h);
        }

        $rowIdx = 2;
        foreach ($kegiatans as $idx => $keg) {
            $jadwalMap = $keg->jadwal->keyBy('bulan_angka');
            $harga = (float)($keg->harga ?? 0);
            $vol = (float)($keg->jumlah ?? 0);
            $total = (float)($keg->total ?? ($harga * $vol));

            $sheet->setCellValue('A' . $rowIdx, $idx + 1);
            $sheet->setCellValue('B' . $rowIdx, $keg->tahun ?? date('Y'));
            $sheet->setCellValue('C' . $rowIdx, $keg->nama);
            $sheet->setCellValue('D' . $rowIdx, $keg->jenis_tugas ?? 'Pencacahan');
            $sheet->setCellValueExplicit('E' . $rowIdx, (string)($keg->kode_mata_anggaran ?? '-'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('F' . $rowIdx, $keg->bidang->nama ?? '-');
            $sheet->setCellValue('G' . $rowIdx, $vol);
            $sheet->setCellValue('H' . $rowIdx, $keg->satuan ?? 'Dokumen');
            $sheet->setCellValue('I' . $rowIdx, $harga);
            $sheet->setCellValue('J' . $rowIdx, $total);

            // Bulan Jan - Des (kolom K - V / index 11 - 22)
            for ($m = 1; $m <= 12; $m++) {
                $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(10 + $m);
                $volBulan = $jadwalMap->has($m) ? ($jadwalMap[$m]->target_volume ?? 1) : '-';
                $sheet->setCellValue($colLetter . $rowIdx, $volBulan);
            }

            $rowIdx++;
        }

        $lastRow = max(2, $rowIdx - 1);

        \App\Support\ExcelStyler::applyHeaderStyle($sheet, 'A1:V1');
        \App\Support\ExcelStyler::applyTableGrid($sheet, "A1:V{$lastRow}");
        \App\Support\ExcelStyler::applyAlignCenter($sheet, "A2:B{$lastRow}");
        \App\Support\ExcelStyler::applyAlignCenter($sheet, "D2:E{$lastRow}");
        \App\Support\ExcelStyler::applyAlignCenter($sheet, "H2:H{$lastRow}");
        \App\Support\ExcelStyler::applyAlignCenter($sheet, "K2:V{$lastRow}");
        \App\Support\ExcelStyler::applyNumberFormat($sheet, "G2:G{$lastRow}");
        \App\Support\ExcelStyler::applyCurrencyFormat($sheet, "I2:J{$lastRow}");
        \App\Support\ExcelStyler::applyAutoWidth($sheet, 1, 22);
        \App\Support\ExcelStyler::freezeHeader($sheet, 'A2');
        $sheet->setAutoFilter("A1:V{$lastRow}");

        $filename = 'Master_Mata_Anggaran_BPS_' . date('Ymd_His') . '.xlsx';
        $writer = new Xlsx($spreadsheet);

        ob_start();
        $writer->save('php://output');
        $content = ob_get_clean();

        return response($content)
            ->header('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->header('Content-Disposition', 'attachment; filename="'.$filename.'"');
    }

    public function importIndex()
    {
        return view('kegiatan.import');
    }

    public function importTemplate()
    {
        $bulan = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Template Anggaran');

        $header = ['No.','Tahun','Nama Kegiatan','Tipe Kegiatan','Kode MAK','Bidang / Tim Kerja','Target Volume','Satuan','Harga Satuan (Rp)','Total Pagu Anggaran (Rp)'];
        foreach ($bulan as $b) {
            $header[] = $b;
        }

        foreach ($header as $i => $h) {
            $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i + 1);
            $sheet->setCellValue($col . '1', $h);
        }

        \App\Support\ExcelStyler::applyHeaderStyle($sheet, 'A1:V1');

        // Satu contoh baris
        $example = ['1','2024','Contoh Kegiatan Survei Lapangan','Pencacahan','054.01.GG.2903.BMA.009.005.A.521213','Distribusi','108','Dokumen','60000','6480000','9','9','9','9','9','9','9','9','9','9','9','9'];
        foreach ($example as $i => $val) {
            $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i + 1);
            $sheet->setCellValueExplicit($col . '2', (string)$val, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->getStyle($col . '2')->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFFBE6');
        }

        \App\Support\ExcelStyler::applyTableGrid($sheet, 'A1:V2');
        \App\Support\ExcelStyler::applyAlignCenter($sheet, 'A2:B2');
        \App\Support\ExcelStyler::applyAlignCenter($sheet, 'D2:E2');
        \App\Support\ExcelStyler::applyAlignCenter($sheet, 'H2:H2');
        \App\Support\ExcelStyler::applyCurrencyFormat($sheet, 'I2:J2');
        \App\Support\ExcelStyler::applyDropdownValidation($sheet, 'D2:D500', ['Pencacahan', 'Pengolahan'], 'Tipe Kegiatan', 'Pilih tipe kegiatan');
        \App\Support\ExcelStyler::applyDropdownValidation($sheet, 'F2:F500', ['Distribusi', 'Neraca', 'Produksi', 'Sosial', 'IPDS', 'Cadangan', 'Bagian Umum'], 'Bidang Kerja', 'Pilih tim kerja BPS');
        \App\Support\ExcelStyler::applyAutoWidth($sheet, 1, 22);
        \App\Support\ExcelStyler::freezeHeader($sheet, 'A2');

        // Sheet Petunjuk
        $petunjuk = $spreadsheet->createSheet();
        $petunjuk->setTitle('Petunjuk & Bidang');
        $petunjuk->setCellValue('A1', 'PETUNJUK IMPORT MATA ANGGARAN');
        $petunjuk->getStyle('A1')->getFont()->setBold(true)->setSize(13);
        $petunjuk->setCellValue('A3', '1. Kolom "No" hanya nomor urut (boleh dikosongkan).');
        $petunjuk->setCellValue('A4', '2. Kolom "Bidang" diisi nama bidang (lihat daftar di bawah). Wajib.');
        $petunjuk->setCellValue('A5', '3. Kolom "Kegiatan" nama kegiatan/uraian mata anggaran. Wajib.');
        $petunjuk->setCellValue('A6', '4. Kolom "Akun (MAK)" kode mata anggaran (opsional).');
        $petunjuk->setCellValue('A7', '5. Kolom "Tahun" tahun anggaran. Kosongkan jika diisi dari pemilihan tahun saat upload.');
        $petunjuk->setCellValue('A8', '6. "Jumlah", "Satuan", "Harga" => Total otomatis = Jumlah x Harga.');
        $petunjuk->setCellValue('A9', '7. Kolom Januari s/d Desember: isi dengan angka volume/nominal pada bulan tsb (0/kosong jika tidak ada).');
        $petunjuk->setCellValue('A11', 'DAFTAR BIDANG / TIM KERJA:');
        $petunjuk->getStyle('A11')->getFont()->setBold(true);
        $row = 12;
        $petunjuk->setCellValue('A'.$row, 'No')->getStyle('A'.$row)->getFont()->setBold(true);
        $petunjuk->setCellValue('B'.$row, 'Nama Bidang')->getStyle('B'.$row)->getFont()->setBold(true);
        foreach (Bidang::orderBy('nama')->get(['nama']) as $i => $b) {
            $petunjuk->setCellValue('A'.($row+$i+1), $i+1);
            $petunjuk->setCellValue('B'.($row+$i+1), $b->nama);
        }
        \App\Support\ExcelStyler::applyAutoWidth($petunjuk, 1, 2);

        $spreadsheet->setActiveSheetIndex(0);

        $objWriter = new Xlsx($spreadsheet);
        $filename = 'Template_Mata_Anggaran.xlsx';
        ob_start();
        $objWriter->save('php://output');
        $content = ob_get_clean();

        return response($content)
            ->header('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->header('Content-Disposition', 'attachment; filename="'.$filename.'"');
    }

    public function importPreview(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:xlsx,xls']);

        $file = $request->file('file');
        $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $file->getClientOriginalName());
        $importsDir = storage_path('app/imports');
        if (!file_exists($importsDir)) {
            mkdir($importsDir, 0777, true);
        }
        $file->move($importsDir, $filename);

        return $this->importPreviewFromPath($filename);
    }

    public function importPreviewFromPath(string $filename)
    {
        ini_set('memory_limit', '-1');
        set_time_limit(300);

        $importsDir = storage_path('app/imports');
        $fullPath = $importsDir . DIRECTORY_SEPARATOR . $filename;

        if (!file_exists($fullPath)) {
            return redirect()->route('kegiatan.index')->with('error', 'File import tidak ditemukan.');
        }

        $reader = IOFactory::createReaderForFile($fullPath);
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($fullPath);
        $sheet = $spreadsheet->getActiveSheet();

        $bulan = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];

        $rows = [];
        $rowsData = $sheet->toArray();
        $max = count($rowsData);
        for ($r = 1; $r < $max; $r++) {
            $row = array_pad($rowsData[$r], 21, null);
            $kegiatanNama = trim($row[2] ?? '');
            $bidangNama = trim($row[1] ?? '');
            if ($kegiatanNama === '' && $bidangNama === '') continue;
            if (str_starts_with($kegiatanNama, '#REF!')) continue;
            if (str_starts_with($bidangNama, '#REF!')) continue;

            $jadwal = [];
            for ($m = 0; $m < 12; $m++) {
                $val = $row[9 + $m];
                $jadwal[$m] = (is_numeric($val) ? (float) $val : 0);
            }

            $rows[] = [
                'no' => trim($row[0] ?? ''),
                'bidang' => $bidangNama,
                'kegiatan' => $kegiatanNama,
                'akun' => trim($row[3] ?? ''),
                'tahun' => trim($row[4] ?? ''),
                'jumlah' => (is_numeric($row[5] ?? null) ? (float) $row[5] : 0),
                'satuan' => trim($row[6] ?? ''),
                'harga' => (is_numeric($row[7] ?? null) ? (float) $row[7] : 0),
                'total' => (is_numeric($row[8] ?? null) ? (float) $row[8] : 0),
                'jadwal' => $jadwal,
            ];
        }

        $bidangs = Bidang::orderBy('nama')->get();
        $tahunOptions = range(date('Y') + 2, date('Y') - 2);
        $path = $filename;

        return view('kegiatan.import-preview', compact('rows', 'path', 'filename', 'bidangs', 'tahunOptions', 'bulan'));
    }

    public function importProcess(Request $request)
    {
        ini_set('memory_limit', '-1');
        set_time_limit(600);

        $request->validate(['path' => 'required|string']);

        $filename = $request->path;
        $fullPath = storage_path('app/imports/' . $filename);

        if (!file_exists($fullPath)) {
            return redirect()->route('kegiatan.index')->with('error', 'File temporari impor tidak ditemukan. Silakan upload ulang.');
        }

        $user = auth()->user();
        $tahunDefault = $request->tahun ?? date('Y');

        $reader = IOFactory::createReaderForFile($fullPath);
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($fullPath);
        $sheet = $spreadsheet->getActiveSheet();

        $rowsData = $sheet->toArray();
        $highestRow = count($rowsData);

        $bidangMap = Bidang::pluck('id', 'nama')->toArray();
        $operatorBidangId = ($user && $user->role === 'operator') ? $user->bidang_id : null;

        $imported = 0;
        $skipped = 0;

        DB::beginTransaction();
        try {
            for ($r = 1; $r < $highestRow; $r++) {
                $row = array_pad($rowsData[$r], 21, null);

                $bidangNama = trim($row[1] ?? '');
                $kegiatanNama = trim($row[2] ?? '');
                if ($kegiatanNama === '' || $bidangNama === '') continue;
                if (str_starts_with($kegiatanNama, '#REF!') || str_starts_with($bidangNama, '#REF!')) continue;

                $akun = trim($row[3] ?? '');
                $tahun = !empty(trim($row[4] ?? '')) ? trim($row[4]) : $tahunDefault;
                $jumlah = (is_numeric($row[5] ?? null) ? (float) $row[5] : 0);
                $satuan = trim($row[6] ?? '');
                $harga = (is_numeric($row[7] ?? null) ? (float) $row[7] : 0);
                $sheetTotal = (is_numeric($row[8] ?? null) ? (float) $row[8] : 0);

                // Bidang mapping (auto-create bila belum ada)
                $bidangId = $bidangMap[$bidangNama] ?? null;
                if (!$bidangId) {
                    $bidang = Bidang::create(['nama' => $bidangNama]);
                    $bidangId = $bidang->id;
                    $bidangMap[$bidangNama] = $bidangId;
                }

                // Pembatasan akses operator pada bidangnya
                if ($operatorBidangId && $bidangId != $operatorBidangId) {
                    $skipped++;
                    continue;
                }

                $total = ($jumlah * $harga) > 0 ? ($jumlah * $harga) : $sheetTotal;

                $kegiatan = Kegiatan::updateOrCreate(
                    ['nama' => $kegiatanNama, 'bidang_id' => $bidangId, 'tahun' => (string) $tahun],
                    [
                        'kode_mata_anggaran' => $akun ?: null,
                        'jumlah' => (int) $jumlah,
                        'satuan' => $satuan ?: null,
                        'harga' => $harga,
                        'total' => $total,
                    ]
                );

                // Sinkron jadwal bulanan
                KegiatanJadwal::where('kegiatan_id', $kegiatan->id)->delete();
                $months = [];
                for ($m = 0; $m < 12; $m++) {
                    $val = (is_numeric($row[9 + $m] ?? null) ? (float) $row[9 + $m] : 0);
                    if ($val > 0) {
                        $months[] = ['kegiatan_id' => $kegiatan->id, 'bulan_angka' => $m + 1, 'jumlah' => $val, 'created_at' => now(), 'updated_at' => now()];
                    }
                }
                if (!empty($months)) {
                    DB::table('kegiatan_jadwal')->insert($months);
                }

                $imported++;
            }

            DB::commit();

            $msg = "Import selesai: $imported data mata anggaran berhasil di-import.";
            if ($skipped > 0) {
                $msg .= " ($skipped baris dilewati karena di luar bidang Anda / tidak valid).";
            }
            return redirect()->route('kegiatan.index')->with('success', $msg);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('kegiatan.import.index')->with('error', 'Error: '.$e->getMessage());
        }
    }
}
