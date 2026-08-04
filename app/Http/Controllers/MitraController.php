<?php

namespace App\Http\Controllers;

use App\Models\Mitra;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;

class MitraController extends Controller
{
    public function index(Request $request)
    {
        $query = Mitra::query();

        if ($request->filled('jk')) {
            $query->where('jk', $request->jk);
        }

        if ($request->filled('pekerjaan')) {
            $query->where('pekerjaan', $request->pekerjaan);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('id_sobat', 'like', "%{$search}%")
                  ->orWhere('no_hp', 'like', "%{$search}%")
                  ->orWhere('alamat', 'like', "%{$search}%")
                  ->orWhere('kode_alamat', 'like', "%{$search}%");
            });
        }

        $pekerjaanList = Mitra::whereNotNull('pekerjaan')
            ->where('pekerjaan', '!=', '')
            ->distinct()
            ->pluck('pekerjaan')
            ->sort();

        $mitras = $query->orderBy('nama')
            ->paginate(20)
            ->onEachSide(1)
            ->withQueryString();

        return view('mitra.index', compact('mitras', 'pekerjaanList'));
    }

    public function create()
    {
        return view('mitra.form');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'id_sobat' => 'nullable|string|max:100|unique:mitras,id_sobat',
            'no_hp' => 'nullable|string|max:30',
            'alamat' => 'nullable|string',
            'pekerjaan' => 'nullable|string|max:255',
            'kode_alamat' => 'nullable|string|max:50',
            'jk' => 'nullable|in:L,P',
        ]);
        Mitra::create($validated);
        return redirect()->route('mitra.index')->with('success', 'Mitra berhasil ditambahkan');
    }

    public function edit(Mitra $mitra)
    {
        return view('mitra.form', compact('mitra'));
    }

    public function update(Request $request, Mitra $mitra)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'id_sobat' => 'nullable|string|max:100|unique:mitras,id_sobat,'.$mitra->id,
            'no_hp' => 'nullable|string|max:30',
            'alamat' => 'nullable|string',
            'pekerjaan' => 'nullable|string|max:255',
            'kode_alamat' => 'nullable|string|max:50',
            'jk' => 'nullable|in:L,P',
        ]);
        $mitra->update($validated);
        return redirect()->route('mitra.index')->with('success', 'Mitra berhasil diupdate');
    }

    public function destroy(Mitra $mitra)
    {
        $mitra->delete();
        return redirect()->route('mitra.index')->with('success', 'Mitra berhasil dihapus');
    }

    public function importIndex()
    {
        return view('mitra.import');
    }

    public function importTemplate()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Template Mitra');

        $header = ['Nomor', 'ID Sobat', 'Nama', 'No. HP', 'Alamat Detail', 'Kode Alamat', 'Pekerjaan', 'Jenis Kelamin'];
        foreach ($header as $i => $h) {
            $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i + 1);
            $sheet->setCellValue($col . '1', $h);
            $sheet->getColumnDimension($col)->setWidth($h === 'Nama' || $h === 'Alamat Detail' ? 30 : 18);
            $sheet->getStyle($col . '1')->getFont()->setBold(true);
        }
        $sheet->getStyle('A1:H1')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('2D5FA8');
        $sheet->getStyle('A1:H1')->getFont()->getColor()->setARGB('FFFFFFFF');

        $example = ['1', '1234567', 'Contoh Nama Mitra', '081234567890', 'Jl. Contoh No. 1, Tasikmalaya', '3206120001', 'Pencacah', 'L'];
        foreach ($example as $i => $val) {
            $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i + 1) . '2', $val);
            $sheet->getStyle(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i + 1) . '2')->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFFBE6');
        }

        $petunjuk = $spreadsheet->createSheet();
        $petunjuk->setTitle('Petunjuk');
        $petunjuk->setCellValue('A1', 'PETUNJUK IMPORT DATA MITRA (SOBAT BPS)');
        $petunjuk->getStyle('A1')->getFont()->setBold(true)->setSize(13);
        $petunjuk->setCellValue('A3', '1. Kolom "ID Sobat" = ID/kode mitra (unik, dipakai untuk SPK). Wajib diisi agar mudah dicari & di-update.');
        $petunjuk->setCellValue('A4', '2. Kolom "Nama" wajib diisi.');
        $petunjuk->setCellValue('A5', '3. Kolom "Jenis Kelamin" bisa diisi: 1 / L / Laki-laki  atau  2 / P / Perempuan.');
        $petunjuk->setCellValue('A6', '4. Baris dengan ID Sobat yang sama akan "diperbarui", bukan membuat duplikat.');
        $petunjuk->setCellValue('A7', '5. Jika ID Sobat kosong, pencocokan memakai Nama.');
        $petunjuk->setCellValue('A9', 'Format Jenis Kelamin:');
        $petunjuk->setCellValue('B10', 'Laki-laki')->getStyle('B10')->getFont()->setBold(true);
        $petunjuk->setCellValue('C10', 'Perempuan')->getStyle('C10')->getFont()->setBold(true);
        $petunjuk->setCellValue('B11', '1, L, l, Laki-laki');
        $petunjuk->setCellValue('C11', '2, P, p, Perempuan');

        $objWriter = new Xlsx($spreadsheet);
        $filename = 'Template_Mitra_Sobat_BPS.xlsx';
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

        $rows = [];
        $rowsData = $sheet->toArray();
        $max = count($rowsData);
        for ($r = 1; $r < $max; $r++) {
            $row = array_pad($rowsData[$r], 8, null);
            $nama = trim($row[2] ?? '');
            $idSobat = trim($row[1] ?? '');
            if ($nama === '' && $idSobat === '') continue;

            $rows[] = [
                'no' => trim($row[0] ?? ''),
                'id_sobat' => $idSobat,
                'nama' => $nama,
                'no_hp' => trim($row[3] ?? ''),
                'alamat' => trim($row[4] ?? ''),
                'kode_alamat' => trim($row[5] ?? ''),
                'pekerjaan' => trim($row[6] ?? ''),
                'jk' => $this->normalizeJk($row[7] ?? ''),
                'jk_raw' => trim((string) ($row[7] ?? '')),
            ];
        }

        $path = $filename;
        return view('mitra.import-preview', compact('rows', 'path'));
    }

    public function importProcess(Request $request)
    {
        ini_set('memory_limit', '-1');
        set_time_limit(600);

        $request->validate(['path' => 'required|string']);

        $filename = $request->path;
        $fullPath = storage_path('app/imports/' . $filename);

        if (!file_exists($fullPath)) {
            return redirect()->route('mitra.index')->with('error', 'File temporari implor tidak ditemukan. Silakan upload ulang.');
        }

        $reader = IOFactory::createReaderForFile($fullPath);
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($fullPath);
        $sheet = $spreadsheet->getActiveSheet();

        $rowsData = $sheet->toArray();
        $highestRow = count($rowsData);

        $imported = 0;
        $skipped = 0;

        DB::beginTransaction();
        try {
            for ($r = 1; $r < $highestRow; $r++) {
                $row = array_pad($rowsData[$r], 8, null);

                $nama = trim($row[2] ?? '');
                if ($nama === '') {
                    $skipped++;
                    continue;
                }

                $idSobat = trim($row[1] ?? '');
                $data = [
                    'id_sobat' => $idSobat ?: null,
                    'no_hp' => trim($row[3] ?? '') ?: null,
                    'alamat' => trim($row[4] ?? '') ?: null,
                    'kode_alamat' => trim($row[5] ?? '') ?: null,
                    'pekerjaan' => trim($row[6] ?? '') ?: null,
                    'jk' => $this->normalizeJk($row[7] ?? ''),
                ];

                if (!empty($idSobat)) {
                    $mitra = Mitra::where('id_sobat', $idSobat)->first();
                    if ($mitra) {
                        $mitra->update($data);
                    } else {
                        Mitra::create($data);
                    }
                } else {
                    Mitra::updateOrCreate(['nama' => $nama], $data);
                }

                $imported++;
            }

            DB::commit();

            $msg = "Import selesai: $imported data mitra berhasil di-import.";
            if ($skipped > 0) {
                $msg .= " ($skipped baris dilewati karena Nama kosong / tidak valid).";
            }
            return redirect()->route('mitra.index')->with('success', $msg);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('mitra.import.index')->with('error', 'Error: '.$e->getMessage());
        }
    }

    private function normalizeJk($value)
    {
        $val = strtolower(trim((string) $value));
        if ($val === '1' || $val === 'l' || $val === 'laki-laki' || $val === 'laki laki' || $val === 'laki' || $val === 'laki2') {
            return 'L';
        }
        if ($val === '2' || $val === 'p' || $val === 'perempuan') {
            return 'P';
        }
        return null;
    }
}