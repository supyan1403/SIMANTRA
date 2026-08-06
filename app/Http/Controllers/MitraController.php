<?php

namespace App\Http\Controllers;

use App\Models\Mitra;
use App\Models\Kecamatan;
use App\Models\Desa;
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

        if ($request->filled('kabupaten_kota')) {
            $query->where('kabupaten_kota', $request->kabupaten_kota);
        }

        if ($request->filled('kecamatan')) {
            $query->where('kecamatan', $request->kecamatan);
        }

        if ($request->filled('desa')) {
            $query->where('desa', $request->desa);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('id_sobat', 'like', "%{$search}%")
                  ->orWhere('no_hp', 'like', "%{$search}%")
                  ->orWhere('kabupaten_kota', 'like', "%{$search}%")
                  ->orWhere('kecamatan', 'like', "%{$search}%")
                  ->orWhere('desa', 'like', "%{$search}%")
                  ->orWhere('alamat_detail', 'like', "%{$search}%")
                  ->orWhere('alamat', 'like', "%{$search}%")
                  ->orWhere('kode_alamat', 'like', "%{$search}%");
            });
        }

        $pekerjaanList = Mitra::whereNotNull('pekerjaan')
            ->where('pekerjaan', '!=', '')
            ->distinct()
            ->pluck('pekerjaan')
            ->sort();

        $kabupatenKotaList = Mitra::whereNotNull('kabupaten_kota')
            ->where('kabupaten_kota', '!=', '')
            ->distinct()
            ->pluck('kabupaten_kota')
            ->sort();

        $kecamatans = Kecamatan::orderBy('nama')->get();

        $desasList = collect();
        if ($request->filled('kecamatan')) {
            $kecObj = Kecamatan::where('nama', $request->kecamatan)->first();
            if ($kecObj) {
                $desasList = Desa::where('kecamatan_id', $kecObj->id)->orderBy('nama')->pluck('nama');
            }
        }

        $mitras = $query->orderBy('nama')
            ->paginate(20)
            ->onEachSide(1)
            ->withQueryString();

        return view('mitra.index', compact('mitras', 'pekerjaanList', 'kabupatenKotaList', 'kecamatans', 'desasList'));
    }

    public function create()
    {
        $kecamatans = Kecamatan::with(['desas' => function($q) {
            $q->orderBy('nama');
        }])->orderBy('nama')->get();

        $kabupatenKotaList = ['Kabupaten Tasikmalaya', 'Kota Tasikmalaya', 'Kabupaten Garut', 'Kabupaten Ciamis', 'Kabupaten Bandung', 'Kabupaten Bandung Barat', 'Kabupaten Bogor', 'Kabupaten Kuningan', 'Kabupaten Majalengka', 'Kabupaten Indramayu', 'Kabupaten Karawang', 'Kabupaten Bekasi'];

        return view('mitra.form', compact('kecamatans', 'kabupatenKotaList'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'id_sobat' => 'nullable|string|max:100|unique:mitras,id_sobat',
            'no_hp' => 'nullable|string|max:30',
            'kabupaten_kota' => 'nullable|string|max:255',
            'kecamatan' => 'nullable|string|max:255',
            'desa' => 'nullable|string|max:255',
            'alamat_detail' => 'nullable|string',
            'alamat' => 'nullable|string',
            'pekerjaan' => 'nullable|string|max:255',
            'kode_alamat' => 'nullable|string|max:50',
            'jk' => 'nullable|in:L,P',
        ]);

        $validated['kabupaten_kota'] = $validated['kabupaten_kota'] ?: 'Kabupaten Tasikmalaya';

        // Auto lookup kode_alamat if kecamatan & desa are specified and kode_alamat is blank
        if (!empty($validated['kecamatan']) && !empty($validated['desa']) && empty($validated['kode_alamat'])) {
            $desaObj = Desa::whereHas('kecamatan', function($q) use ($validated) {
                $q->where('nama', $validated['kecamatan']);
            })->where('nama', $validated['desa'])->first();

            if ($desaObj) {
                $validated['kode_alamat'] = $desaObj->kode_full;
            }
        }

        if (empty($validated['alamat']) && !empty($validated['alamat_detail'])) {
            $validated['alamat'] = $validated['alamat_detail'];
        }

        Mitra::create($validated);
        return redirect()->route('mitra.index')->with('success', 'Mitra berhasil ditambahkan');
    }

    public function edit(Mitra $mitra)
    {
        $kecamatans = Kecamatan::with(['desas' => function($q) {
            $q->orderBy('nama');
        }])->orderBy('nama')->get();

        $kabupatenKotaList = ['Kabupaten Tasikmalaya', 'Kota Tasikmalaya', 'Kabupaten Garut', 'Kabupaten Ciamis', 'Kabupaten Bandung', 'Kabupaten Bandung Barat', 'Kabupaten Bogor', 'Kabupaten Kuningan', 'Kabupaten Majalengka', 'Kabupaten Indramayu', 'Kabupaten Karawang', 'Kabupaten Bekasi'];

        return view('mitra.form', compact('mitra', 'kecamatans', 'kabupatenKotaList'));
    }

    public function update(Request $request, Mitra $mitra)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'id_sobat' => 'nullable|string|max:100|unique:mitras,id_sobat,'.$mitra->id,
            'no_hp' => 'nullable|string|max:30',
            'kabupaten_kota' => 'nullable|string|max:255',
            'kecamatan' => 'nullable|string|max:255',
            'desa' => 'nullable|string|max:255',
            'alamat_detail' => 'nullable|string',
            'alamat' => 'nullable|string',
            'pekerjaan' => 'nullable|string|max:255',
            'kode_alamat' => 'nullable|string|max:50',
            'jk' => 'nullable|in:L,P',
        ]);

        $validated['kabupaten_kota'] = $validated['kabupaten_kota'] ?: 'Kabupaten Tasikmalaya';

        if (!empty($validated['kecamatan']) && !empty($validated['desa']) && empty($validated['kode_alamat'])) {
            $desaObj = Desa::whereHas('kecamatan', function($q) use ($validated) {
                $q->where('nama', $validated['kecamatan']);
            })->where('nama', $validated['desa'])->first();

            if ($desaObj) {
                $validated['kode_alamat'] = $desaObj->kode_full;
            }
        }

        if (empty($validated['alamat']) && !empty($validated['alamat_detail'])) {
            $validated['alamat'] = $validated['alamat_detail'];
        }

        $mitra->update($validated);
        return redirect()->route('mitra.index')->with('success', 'Mitra berhasil diupdate');
    }

    public function destroy(Mitra $mitra)
    {
        $mitra->delete();
        return redirect()->route('mitra.index')->with('success', 'Mitra berhasil dihapus');
    }

    public function getDesasByKecamatan($kecamatanNama)
    {
        $kecamatan = Kecamatan::where('nama', urldecode($kecamatanNama))->first();
        if (!$kecamatan) {
            return response()->json([]);
        }

        $desas = Desa::where('kecamatan_id', $kecamatan->id)
            ->orderBy('nama')
            ->get(['id', 'nama', 'kode_full', 'kode_desa']);

        return response()->json($desas);
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

        $header = ['Nomor', 'ID Sobat', 'Nama', 'No. HP', 'Kabupaten / Kota', 'Kecamatan', 'Desa / Kelurahan', 'Alamat Detail', 'Kode Alamat', 'Pekerjaan', 'Jenis Kelamin'];
        foreach ($header as $i => $h) {
            $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i + 1);
            $sheet->setCellValue($col . '1', $h);
            $sheet->getColumnDimension($col)->setWidth(in_array($h, ['Nama', 'Alamat Detail', 'Kabupaten / Kota', 'Kecamatan', 'Desa / Kelurahan']) ? 25 : 16);
            $sheet->getStyle($col . '1')->getFont()->setBold(true);
        }
        $sheet->getStyle('A1:K1')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('2D5FA8');
        $sheet->getStyle('A1:K1')->getFont()->getColor()->setARGB('FFFFFFFF');

        $example = ['1', '1234567', 'Contoh Nama Mitra', '081234567890', 'Kabupaten Tasikmalaya', 'CIPATUJAH', 'CIHERAS', 'Jl. Raya Cipatujah No. 12', '3206010001', 'Pencacah', 'L'];
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
        $petunjuk->setCellValue('A5', '3. Kolom "Kabupaten / Kota" contoh: Kabupaten Tasikmalaya, Kota Tasikmalaya, Kabupaten Garut, dll.');
        $petunjuk->setCellValue('A6', '4. Kolom "Kecamatan", "Desa / Kelurahan", dan "Alamat Detail" dipisah untuk kemudahan pengelolaan.');
        $petunjuk->setCellValue('A7', '5. Kolom "Jenis Kelamin" bisa diisi: 1 / L / Laki-laki  atau  2 / P / Perempuan.');

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
            $row = array_pad($rowsData[$r], 11, null);
            $nama = trim($row[2] ?? '');
            $idSobat = trim($row[1] ?? '');
            if ($nama === '' && $idSobat === '') continue;

            $rows[] = [
                'no' => trim($row[0] ?? ''),
                'id_sobat' => $idSobat,
                'nama' => $nama,
                'no_hp' => trim($row[3] ?? ''),
                'kabupaten_kota' => trim($row[4] ?? '') ?: 'Kabupaten Tasikmalaya',
                'kecamatan' => trim($row[5] ?? ''),
                'desa' => trim($row[6] ?? ''),
                'alamat_detail' => trim($row[7] ?? ''),
                'kode_alamat' => trim($row[8] ?? ''),
                'pekerjaan' => trim($row[9] ?? ''),
                'jk' => $this->normalizeJk($row[10] ?? ''),
                'jk_raw' => trim((string) ($row[10] ?? '')),
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
            return redirect()->route('mitra.index')->with('error', 'File temporari import tidak ditemukan. Silakan upload ulang.');
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
                $row = array_pad($rowsData[$r], 11, null);

                $nama = trim($row[2] ?? '');
                if ($nama === '') {
                    $skipped++;
                    continue;
                }

                $idSobat = trim($row[1] ?? '');
                $kabupatenKota = trim($row[4] ?? '') ?: 'Kabupaten Tasikmalaya';
                $kecamatan = trim($row[5] ?? '') ?: null;
                $desa = trim($row[6] ?? '') ?: null;
                $alamatDetail = trim($row[7] ?? '') ?: null;
                $kodeAlamat = trim($row[8] ?? '') ?: null;

                if ($kecamatan && $desa && !$kodeAlamat) {
                    $desaObj = Desa::whereHas('kecamatan', function($q) use ($kecamatan) {
                        $q->where('nama', $kecamatan);
                    })->where('nama', $desa)->first();
                    if ($desaObj) {
                        $kodeAlamat = $desaObj->kode_full;
                    }
                }

                $data = [
                    'id_sobat' => $idSobat ?: null,
                    'no_hp' => trim($row[3] ?? '') ?: null,
                    'kabupaten_kota' => $kabupatenKota,
                    'kecamatan' => $kecamatan,
                    'desa' => $desa,
                    'alamat_detail' => $alamatDetail,
                    'kode_alamat' => $kodeAlamat,
                    'pekerjaan' => trim($row[9] ?? '') ?: null,
                    'jk' => $this->normalizeJk($row[10] ?? ''),
                ];

                if (!empty($idSobat)) {
                    $mitra = Mitra::where('id_sobat', $idSobat)->first();
                    if ($mitra) {
                        $mitra->update($data);
                    } else {
                        Mitra::create(array_merge(['nama' => $nama], $data));
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