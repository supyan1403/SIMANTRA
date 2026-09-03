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
                  ->orWhere('nik', 'like', "%{$search}%")
                  ->orWhere('posisi', 'like', "%{$search}%")
                  ->orWhere('no_hp', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
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
        $posisiList = \App\Models\PosisiMitra::orderBy('nama')->get();

        return view('mitra.form', compact('kecamatans', 'kabupatenKotaList', 'posisiList'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'id_sobat' => 'nullable|string|max:100|unique:mitras,id_sobat',
            'nik' => 'nullable|string|max:30|unique:mitras,nik',
            'posisi' => 'nullable|string|max:100',
            'no_hp' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:150',
            'npwp' => 'nullable|string|max:50',
            'tanggal_lahir' => 'nullable|string|max:50',
            'pendidikan' => 'nullable|string|max:100',
            'agama' => 'nullable|string|max:50',
            'status_perkawinan' => 'nullable|string|max:50',
            'kabupaten_kota' => 'nullable|string|max:255',
            'kecamatan' => 'nullable|string|max:255',
            'desa' => 'nullable|string|max:255',
            'alamat_detail' => 'nullable|string',
            'alamat' => 'nullable|string',
            'pekerjaan' => 'nullable|string|max:255',
            'kode_alamat' => 'nullable|string|max:50',
            'jk' => 'nullable|in:L,P',
            'exp_sp' => 'nullable|boolean',
            'exp_st' => 'nullable|boolean',
            'exp_se' => 'nullable|boolean',
            'exp_susenas' => 'nullable|boolean',
            'exp_sakernas' => 'nullable|boolean',
            'exp_sbh' => 'nullable|boolean',
        ]);

        $validated['kabupaten_kota'] = $validated['kabupaten_kota'] ?: 'Kabupaten Tasikmalaya';
        $validated['exp_sp'] = $request->has('exp_sp');
        $validated['exp_st'] = $request->has('exp_st');
        $validated['exp_se'] = $request->has('exp_se');
        $validated['exp_susenas'] = $request->has('exp_susenas');
        $validated['exp_sakernas'] = $request->has('exp_sakernas');
        $validated['exp_sbh'] = $request->has('exp_sbh');

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
        $posisiList = \App\Models\PosisiMitra::orderBy('nama')->get();

        return view('mitra.form', compact('mitra', 'kecamatans', 'kabupatenKotaList', 'posisiList'));
    }

    public function update(Request $request, Mitra $mitra)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'id_sobat' => 'nullable|string|max:100|unique:mitras,id_sobat,'.$mitra->id,
            'nik' => 'nullable|string|max:30|unique:mitras,nik,'.$mitra->id,
            'posisi' => 'nullable|string|max:100',
            'no_hp' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:150',
            'npwp' => 'nullable|string|max:50',
            'tanggal_lahir' => 'nullable|string|max:50',
            'pendidikan' => 'nullable|string|max:100',
            'agama' => 'nullable|string|max:50',
            'status_perkawinan' => 'nullable|string|max:50',
            'kabupaten_kota' => 'nullable|string|max:255',
            'kecamatan' => 'nullable|string|max:255',
            'desa' => 'nullable|string|max:255',
            'alamat_detail' => 'nullable|string',
            'alamat' => 'nullable|string',
            'pekerjaan' => 'nullable|string|max:255',
            'kode_alamat' => 'nullable|string|max:50',
            'jk' => 'nullable|in:L,P',
            'exp_sp' => 'nullable|boolean',
            'exp_st' => 'nullable|boolean',
            'exp_se' => 'nullable|boolean',
            'exp_susenas' => 'nullable|boolean',
            'exp_sakernas' => 'nullable|boolean',
            'exp_sbh' => 'nullable|boolean',
        ]);

        $validated['kabupaten_kota'] = $validated['kabupaten_kota'] ?: 'Kabupaten Tasikmalaya';
        $validated['exp_sp'] = $request->has('exp_sp');
        $validated['exp_st'] = $request->has('exp_st');
        $validated['exp_se'] = $request->has('exp_se');
        $validated['exp_susenas'] = $request->has('exp_susenas');
        $validated['exp_sakernas'] = $request->has('exp_sakernas');
        $validated['exp_sbh'] = $request->has('exp_sbh');

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
        return redirect()->route('mitra.index')->with('success', 'Data mitra berhasil diperbarui');
    }

    public function destroy(Mitra $mitra)
    {
        DB::beginTransaction();
        try {
            \App\Models\AlokasiHonor::where('mitra_id', $mitra->id)->delete();
            $mitra->delete();
            DB::commit();
            return redirect()->route('mitra.index')->with('success', 'Mitra ' . $mitra->nama . ' berhasil dihapus.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->route('mitra.index')->with('error', 'Gagal menghapus mitra: ' . $e->getMessage());
        }
    }

    /**
     * Hapus banyak mitra sekaligus (Bulk Delete)
     */
    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'required|integer|exists:mitras,id',
        ]);

        $ids = $request->ids;
        $count = count($ids);

        DB::beginTransaction();
        try {
            // Hapus relasi alokasi honor terkait
            \App\Models\AlokasiHonor::whereIn('mitra_id', $ids)->delete();
            
            // Hapus data mitra
            Mitra::whereIn('id', $ids)->delete();

            DB::commit();
            return redirect()->route('mitra.index')->with('success', "Berhasil menghapus {$count} data mitra sekaligus.");
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->route('mitra.index')->with('error', 'Gagal menghapus data mitra secara massal: ' . $e->getMessage());
        }
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

    public function export(Request $request)
    {
        $search = $request->search;
        $kecamatan = $request->kecamatan;

        $query = Mitra::query();

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('id_sobat', 'like', "%{$search}%")
                  ->orWhere('nik', 'like', "%{$search}%")
                  ->orWhere('no_hp', 'like', "%{$search}%")
                  ->orWhere('pekerjaan', 'like', "%{$search}%");
            });
        }

        if ($kecamatan) {
            $query->where('kecamatan', $kecamatan);
        }

        $mitras = $query->orderBy('nama', 'asc')->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Data Mitra BPS');

        $headers = [
            'No.',
            'ID Sobat',
            'NIK',
            'Nama Lengkap Mitra',
            'Posisi Mitra',
            'Kabupaten / Kota',
            'Kecamatan',
            'Desa / Kelurahan',
            'Alamat Lengkap',
            'Pekerjaan',
            'Jenis Kelamin (L/P)',
            'Pendidikan',
            'Tanggal Lahir',
            'NPWP',
            'No. Handphone / WhatsApp',
            'Email',
            'Pengalaman SP',
            'Pengalaman ST',
            'Pengalaman SE',
            'Pengalaman Susenas',
            'Pengalaman Sakernas',
            'Pengalaman SBH',
        ];

        foreach ($headers as $i => $h) {
            $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i + 1);
            $sheet->setCellValue($col . '1', $h);
        }

        $rowIdx = 2;
        foreach ($mitras as $idx => $m) {
            $sheet->setCellValue('A' . $rowIdx, $idx + 1);
            $sheet->setCellValueExplicit('B' . $rowIdx, (string)($m->id_sobat ?? '-'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('C' . $rowIdx, (string)($m->nik ?? '-'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('D' . $rowIdx, $m->nama);
            $sheet->setCellValue('E' . $rowIdx, $m->posisi ?? '-');
            $sheet->setCellValue('F' . $rowIdx, $m->kabupaten_kota ?: 'Kabupaten Tasikmalaya');
            $sheet->setCellValue('G' . $rowIdx, $m->kecamatan ?: '-');
            $sheet->setCellValue('H' . $rowIdx, $m->desa ?: '-');
            $sheet->setCellValue('I' . $rowIdx, $m->alamat_clean);
            $sheet->setCellValue('J' . $rowIdx, $m->pekerjaan_clean);
            $sheet->setCellValue('K' . $rowIdx, $m->jk ? strtoupper($m->jk) : '-');
            $sheet->setCellValue('L' . $rowIdx, $m->pendidikan_clean);
            $sheet->setCellValue('M' . $rowIdx, $m->tanggal_lahir_clean);
            $sheet->setCellValueExplicit('N' . $rowIdx, (string)($m->npwp ?: '-'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('O' . $rowIdx, (string)($m->no_hp ?? '-'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('P' . $rowIdx, $m->email ?: '-');
            $sheet->setCellValue('Q' . $rowIdx, $m->exp_sp ? 'Ya' : 'Tidak');
            $sheet->setCellValue('R' . $rowIdx, $m->exp_st ? 'Ya' : 'Tidak');
            $sheet->setCellValue('S' . $rowIdx, $m->exp_se ? 'Ya' : 'Tidak');
            $sheet->setCellValue('T' . $rowIdx, $m->exp_susenas ? 'Ya' : 'Tidak');
            $sheet->setCellValue('U' . $rowIdx, $m->exp_sakernas ? 'Ya' : 'Tidak');
            $sheet->setCellValue('V' . $rowIdx, $m->exp_sbh ? 'Ya' : 'Tidak');

            $rowIdx++;
        }

        $lastRow = max(2, $rowIdx - 1);

        \App\Support\ExcelStyler::applyHeaderStyle($sheet, 'A1:V1');
        \App\Support\ExcelStyler::applyTableGrid($sheet, "A1:V{$lastRow}");
        \App\Support\ExcelStyler::applyAlignCenter($sheet, "A2:A{$lastRow}");
        \App\Support\ExcelStyler::applyAlignCenter($sheet, "K2:K{$lastRow}");
        \App\Support\ExcelStyler::applyAlignCenter($sheet, "Q2:V{$lastRow}");
        \App\Support\ExcelStyler::applyTextFormat($sheet, "B2:C{$lastRow}");
        \App\Support\ExcelStyler::applyTextFormat($sheet, "N2:O{$lastRow}");
        \App\Support\ExcelStyler::applyAutoWidth($sheet, 1, 22);

        // Sticky Freeze Panes: Kunci Baris Header (Row 1) & 4 Kolom Pertama (A, B, C, D)
        \App\Support\ExcelStyler::freezeHeader($sheet, 'E2');
        $sheet->setAutoFilter("A1:V{$lastRow}");

        $filename = 'Master_Data_Mitra_BPS_' . date('Ymd_His') . '.xlsx';
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
        return view('mitra.import');
    }

    public function importTemplate()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Template Mitra');

        $header = [
            'Nomor',
            'ID Sobat',
            'NIK',
            'Nama Lengkap Mitra',
            'Posisi Mitra',
            'Kabupaten / Kota',
            'Kecamatan',
            'Desa',
            'Alamat Detail',
            'Pekerjaan',
            'Jenis Kelamin (L/P)',
            'Pendidikan',
            'Tanggal Lahir',
            'NPWP',
            'No. Handphone / WhatsApp',
            'Email',
            'Pengalaman SP (Ya/Tidak)',
            'Pengalaman ST (Ya/Tidak)',
            'Pengalaman SE (Ya/Tidak)',
            'Pengalaman Susenas (Ya/Tidak)',
            'Pengalaman Sakernas (Ya/Tidak)',
            'Pengalaman SBH (Ya/Tidak)'
        ];

        foreach ($header as $i => $h) {
            $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i + 1);
            $sheet->setCellValue($col . '1', $h);
        }

        \App\Support\ExcelStyler::applyHeaderStyle($sheet, 'A1:V1');

        $example = [
            '1',
            '320601234',
            '3206010101900001',
            'BUDI SANTOSO',
            'Pendata Lapangan',
            'Kabupaten Tasikmalaya',
            'CIPATUJAH',
            'CIHERAS',
            'Kp. Cisarua RT 02 RW 01',
            'Aparat Desa/ Kelurahan',
            'L',
            'S1 / Sarjana (D4/S1)',
            '1990-01-01',
            '12.345.678.9-425.000',
            '081234567890',
            'budi@bps.go.id',
            'Ya',
            'Ya',
            'Tidak',
            'Ya',
            'Tidak',
            'Tidak'
        ];

        foreach ($example as $i => $val) {
            $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i + 1);
            $sheet->setCellValueExplicit($col . '2', (string)$val, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->getStyle($col . '2')->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFFBE6');
        }

        \App\Support\ExcelStyler::applyTableGrid($sheet, 'A1:V2');
        \App\Support\ExcelStyler::applyAlignCenter($sheet, 'A2:A2');
        \App\Support\ExcelStyler::applyAlignCenter($sheet, 'C2:C2');
        \App\Support\ExcelStyler::applyAlignCenter($sheet, 'K2:K2');
        \App\Support\ExcelStyler::applyAlignCenter($sheet, 'M2:M2');
        \App\Support\ExcelStyler::applyAlignCenter($sheet, 'Q2:V2');

        \App\Support\ExcelStyler::applyTextFormat($sheet, 'B2:C10000');
        \App\Support\ExcelStyler::applyTextFormat($sheet, 'M2:M10000');
        \App\Support\ExcelStyler::applyTextFormat($sheet, 'N2:O10000');

        \App\Support\ExcelStyler::applyDropdownValidation($sheet, 'E2:E500', ['Pendata Lapangan', 'Pengolah Data', 'Pemeriksa Lapangan', 'Pemeriksa Pengolahan'], 'Posisi Mitra', 'Pilih peran tugas');
        \App\Support\ExcelStyler::applyDropdownValidation($sheet, 'K2:K500', ['L', 'P'], 'Jenis Kelamin', 'Pilih L atau P');
        \App\Support\ExcelStyler::applyDropdownValidation($sheet, 'L2:L500', ['SMA / SMK / MA / Sederajat', 'S1 / Sarjana (D4/S1)', 'D1 / D2 / D3 (Diploma)', 'S2 / Pascasarjana', 'SMP / MTs / Sederajat', 'SD / MI / Sederajat', 'S3 / Doktor', 'Lainnya'], 'Pendidikan', 'Pilih jenjang');
        
        // Dropdown Ya / Tidak untuk Pengalaman Survei BPS
        \App\Support\ExcelStyler::applyDropdownValidation($sheet, 'Q2:V500', ['Ya', 'Tidak'], 'Pengalaman Survei', 'Pilih Ya jika pernah mengikuti atau Tidak jika belum');

        \App\Support\ExcelStyler::applyAutoWidth($sheet, 1, 22);

        // Sticky Freeze Panes: Kunci Baris Header (Row 1) & 4 Kolom Pertama (A, B, C, D)
        \App\Support\ExcelStyler::freezeHeader($sheet, 'E2');

        $petunjuk = $spreadsheet->createSheet();
        $petunjuk->setTitle('Petunjuk');
        $petunjuk->setCellValue('A1', 'PETUNJUK IMPORT MASTER DATA MITRA (SOBAT BPS)');
        $petunjuk->getStyle('A1')->getFont()->setBold(true)->setSize(13);
        $petunjuk->setCellValue('A3', '1. Kolom "ID Sobat" = ID/kode mitra resmi BPS (unik).');
        $petunjuk->setCellValue('A4', '2. Kolom "NIK" = 16 digit Nomor Induk Kependudukan KTP.');
        $petunjuk->setCellValue('A5', '3. Kolom "Nama Lengkap Mitra" wajib diisi.');
        $petunjuk->setCellValue('A6', '4. Kolom "Jenis Kelamin" diisi L (Laki-laki) atau P (Perempuan).');
        $petunjuk->setCellValue('A7', '5. Kolom "Tanggal Lahir" bebas diisi format tanggal apa saja (DD/MM/YYYY atau YYYY-MM-DD), sistem otomatis mendeteksi.');
        $petunjuk->setCellValue('A8', '6. Kolom "Pengalaman SP/ST/SE/Susenas/Sakernas/SBH" diisi Ya atau Tidak.');
        \App\Support\ExcelStyler::applyAutoWidth($petunjuk, 1, 2);

        $spreadsheet->setActiveSheetIndex(0);

        $objWriter = new Xlsx($spreadsheet);
        $filename = 'Template_Master_Data_Mitra_BPS.xlsx';
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
            return redirect()->route('mitra.index')->with('error', 'File import tidak ditemukan.');
        }

        $reader = IOFactory::createReaderForFile($fullPath);
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($fullPath);
        $sheet = $spreadsheet->getActiveSheet();

        $rows = [];
        $rowsData = $sheet->toArray();
        $max = count($rowsData);
        for ($r = 1; $r < $max; $r++) {
            $row = array_pad($rowsData[$r], 22, null);
            $nama = trim((string)($row[3] ?? $row[2] ?? ''));
            $idSobat = \App\Support\ExcelStyler::cleanStringNumber($row[1] ?? '');
            $nik = \App\Support\ExcelStyler::cleanStringNumber($row[2] ?? '');
            if ($nama === '' && $idSobat === '') continue;

            $parsedDate = \App\Support\ExcelStyler::parseDate($row[12] ?? null);

            $rows[] = [
                'no' => trim((string)($row[0] ?? $r)),
                'id_sobat' => $idSobat,
                'nik' => $nik,
                'nama' => $nama,
                'posisi' => trim((string)($row[4] ?? '')),
                'kabupaten_kota' => trim((string)($row[5] ?? '')) ?: 'Kabupaten Tasikmalaya',
                'kecamatan' => trim((string)($row[6] ?? '')),
                'desa' => trim((string)($row[7] ?? '')),
                'alamat_detail' => trim((string)($row[8] ?? '')),
                'pekerjaan' => trim((string)($row[9] ?? '')),
                'jk' => $this->normalizeJk($row[10] ?? ''),
                'pendidikan' => trim((string)($row[11] ?? '')),
                'tanggal_lahir' => $parsedDate ?: trim((string)($row[12] ?? '')),
                'npwp' => \App\Support\ExcelStyler::cleanStringNumber($row[13] ?? ''),
                'no_hp' => \App\Support\ExcelStyler::cleanStringNumber($row[14] ?? ''),
                'email' => trim((string)($row[15] ?? '')),
                'exp_sp' => $this->normalizeExp($row[16] ?? ''),
                'exp_st' => $this->normalizeExp($row[17] ?? ''),
                'exp_se' => $this->normalizeExp($row[18] ?? ''),
                'exp_susenas' => $this->normalizeExp($row[19] ?? ''),
                'exp_sakernas' => $this->normalizeExp($row[20] ?? ''),
                'exp_sbh' => $this->normalizeExp($row[21] ?? ''),
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
                $row = array_pad($rowsData[$r], 22, null);

                $nama = trim((string)($row[3] ?? $row[2] ?? ''));
                if ($nama === '') {
                    $skipped++;
                    continue;
                }

                $idSobat = \App\Support\ExcelStyler::cleanStringNumber($row[1] ?? '');
                $nik = \App\Support\ExcelStyler::cleanStringNumber($row[2] ?? '');
                $kabupatenKota = trim((string)($row[5] ?? '')) ?: 'Kabupaten Tasikmalaya';
                $kecamatan = trim((string)($row[6] ?? '')) ?: null;
                $desa = trim((string)($row[7] ?? '')) ?: null;
                $alamatDetail = trim((string)($row[8] ?? '')) ?: null;

                $kodeAlamat = null;
                if ($kecamatan && $desa) {
                    $desaObj = Desa::whereHas('kecamatan', function($q) use ($kecamatan) {
                        $q->where('nama', $kecamatan);
                    })->where('nama', $desa)->first();
                    if ($desaObj) {
                        $kodeAlamat = $desaObj->kode_full;
                    }
                }

                $tanggalLahir = \App\Support\ExcelStyler::parseDate($row[12] ?? null);

                $data = [
                    'id_sobat' => $idSobat ?: null,
                    'nik' => $nik ?: null,
                    'posisi' => trim((string)($row[4] ?? '')) ?: null,
                    'kabupaten_kota' => $kabupatenKota,
                    'kecamatan' => $kecamatan,
                    'desa' => $desa,
                    'alamat_detail' => $alamatDetail,
                    'kode_alamat' => $kodeAlamat,
                    'pekerjaan' => trim((string)($row[9] ?? '')) ?: null,
                    'jk' => $this->normalizeJk($row[10] ?? ''),
                    'pendidikan' => trim((string)($row[11] ?? '')) ?: null,
                    'tanggal_lahir' => $tanggalLahir,
                    'npwp' => \App\Support\ExcelStyler::cleanStringNumber($row[13] ?? '') ?: null,
                    'no_hp' => \App\Support\ExcelStyler::cleanStringNumber($row[14] ?? '') ?: null,
                    'email' => trim((string)($row[15] ?? '')) ?: null,
                    'exp_sp' => $this->normalizeExp($row[16] ?? ''),
                    'exp_st' => $this->normalizeExp($row[17] ?? ''),
                    'exp_se' => $this->normalizeExp($row[18] ?? ''),
                    'exp_susenas' => $this->normalizeExp($row[19] ?? ''),
                    'exp_sakernas' => $this->normalizeExp($row[20] ?? ''),
                    'exp_sbh' => $this->normalizeExp($row[21] ?? ''),
                ];

                if (!empty($idSobat)) {
                    $mitra = Mitra::where('id_sobat', $idSobat)->first();
                    if ($mitra) {
                        $mitra->update($data);
                    } else {
                        Mitra::create(array_merge(['nama' => $nama], $data));
                    }
                } elseif (!empty($nik)) {
                    $mitra = Mitra::where('nik', $nik)->first();
                    if ($mitra) {
                        $mitra->update($data);
                    } else {
                        Mitra::create(array_merge(['nama' => $nama], $data));
                    }
                } else {
                    Mitra::create(array_merge(['nama' => $nama], $data));
                }

                $imported++;
            }
            DB::commit();

            return redirect()->route('mitra.index')->with('success', "Berhasil mengimpor {$imported} data mitra lengkap.");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('mitra.index')->with('error', 'Gagal memproses import data: ' . $e->getMessage());
        }
    }

    private function normalizeExp($val): int
    {
        if ($val === null) return 0;
        $str = strtoupper(trim((string)$val));
        return in_array($str, ['1', 'YA', 'Y', 'TRUE', 'YES', 'ADA']) ? 1 : 0;
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

    public function ajaxStoreKecamatan(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
        ]);

        $nama = strtoupper(trim($request->nama));

        $kecamatan = Kecamatan::firstOrCreate(
            ['nama' => $nama],
            ['kode_kec' => strtoupper(substr(md5($nama), 0, 6))]
        );

        return response()->json([
            'success' => true,
            'kecamatan' => ['id' => $kecamatan->id, 'nama' => $kecamatan->nama],
        ]);
    }
}