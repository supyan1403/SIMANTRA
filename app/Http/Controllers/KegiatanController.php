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

        $query = Kegiatan::with(['bidang', 'jadwal', 'alokasiHonors.mitra', 'alokasiHonors.periode'])
            ->withCount('alokasiHonors as total_alokasi');

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

        return view('kegiatan.index', compact('kegiatans', 'bidangs', 'search', 'bidangId'));
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
            'bidang_id' => 'required|exists:bidangs,id',
            'kode_mata_anggaran' => 'nullable|string|max:100',
            'tahun' => 'nullable|string|max:4',
            'jumlah' => 'nullable|numeric|min:0',
            'satuan' => 'nullable|string|max:50',
            'harga' => 'nullable|numeric|min:0',
            'tgl_mulai' => 'nullable|date',
            'tgl_selesai' => 'nullable|date',
        ]);

        $validated['jumlah'] = $validated['jumlah'] ?? 0;
        $validated['harga'] = $validated['harga'] ?? 0;
        $validated['total'] = $validated['jumlah'] * $validated['harga'];

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
            'bidang_id' => 'required|exists:bidangs,id',
            'kode_mata_anggaran' => 'nullable|string|max:100',
            'tahun' => 'nullable|string|max:4',
            'jumlah' => 'nullable|numeric|min:0',
            'satuan' => 'nullable|string|max:50',
            'harga' => 'nullable|numeric|min:0',
            'tgl_mulai' => 'nullable|date',
            'tgl_selesai' => 'nullable|date',
        ]);

        $validated['jumlah'] = $validated['jumlah'] ?? 0;
        $validated['harga'] = $validated['harga'] ?? 0;
        $validated['total'] = $validated['jumlah'] * $validated['harga'];

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
        $kegiatans = Kegiatan::where('bidang_id', $bidangId)->orderBy('nama')->get(['id', 'nama', 'kode_mata_anggaran']);
        return response()->json($kegiatans);
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

        $header = ['Nomor','Bidang','Kegiatan','Akun (MAK)','Tahun','Jumlah','Satuan','Harga','Total'];
        foreach ($bulan as $b) {
            $header[] = $b;
        }

        $colLetters = [];
        for ($c = 1; $c <= 21; $c++) {
            $colLetters[] = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($c);
        }

        foreach ($header as $i => $h) {
            $col = $colLetters[$i];
            $sheet->setCellValue($col . '1', $h);
            $sheet->getColumnDimension($col)->setWidth($h === 'Kegiatan' ? 40 : ($h === 'Akun (MAK)' ? 24 : 13));
            $sheet->getStyle($col . '1')->getFont()->setBold(true);
        }
        $sheet->getStyle('A1:U1')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('2D5FA8');
        $sheet->getStyle('A1:U1')->getFont()->getColor()->setARGB('FFFFFFFF');

        // Satu contoh baris
        $example = ['1','Distribusi','Contoh Kegiatan Survei','2894.BMA.001.051.A','2025','12','Kegiatan','500000','6000000', '1','1','1','1','1','1','1','1','1','1','1'];
        foreach ($example as $i => $val) {
            $sheet->setCellValue($colLetters[$i] . '2', $val);
            $sheet->getStyle($colLetters[$i] . '2')->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFFBE6');
        }

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
        $sheet->getColumnDimension('U')->setWidth(14);

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
        ini_set('memory_limit', '-1');
        set_time_limit(300);

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
