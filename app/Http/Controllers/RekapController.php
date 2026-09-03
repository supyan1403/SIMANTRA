<?php

namespace App\Http\Controllers;

use App\Models\Periode;
use App\Models\Bidang;
use App\Models\Kegiatan;
use App\Models\AlokasiHonor;
use App\Traits\HasBidangScope;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class RekapController extends Controller
{
    use HasBidangScope;

    public function index(Request $request)
    {
        $user = auth()->user();
        $isOperatorScoped = ($user->role === 'operator' && $user->bidang_id);

        $latestTahun = Periode::max('tahun') ?? date('Y');
        $tahun = $request->tahun ?? $latestTahun;

        if ($isOperatorScoped) {
            $bidangs = Bidang::where('id', $user->bidang_id)->get();
        } else {
            $bidangs = Bidang::all();
        }
        $periodes = Periode::where('tahun', $tahun)->orderBy('bulan_angka')->get();

        $rekap = [];
        $paguPerBidang = [];
        $totalPaguAll = 0;
        foreach ($bidangs as $bidang) {
            $row = ['bidang' => $bidang->nama];
            $total = 0;
            foreach ($periodes as $periode) {
                $sum = AlokasiHonor::whereHas('kegiatan', fn ($q) => $q->where('bidang_id', $bidang->id))
                    ->where('periode_id', $periode->id)
                    ->sum('nominal');
                $row[$periode->id] = (float) $sum;
                $total += $sum;
            }
            $row['total'] = (float) $total;
            $rekap[] = $row;

            $pagu = (float) Kegiatan::where('bidang_id', $bidang->id)
                ->where('tahun', $tahun)
                ->sum('total');
            $paguPerBidang[$bidang->id] = $pagu;
            $totalPaguAll += $pagu;
        }

        $tahunList = Periode::select('tahun')->distinct()->orderBy('tahun')->pluck('tahun');
        if ($tahunList->isEmpty()) {
            $tahunList = collect([$tahun]);
        }

        return view('rekap.index', compact('rekap', 'periodes', 'tahun', 'tahunList', 'bidangs', 'paguPerBidang', 'totalPaguAll'));
    }

    public function export(Request $request)
    {
        $user = auth()->user();
        $isOperatorScoped = ($user->role === 'operator' && $user->bidang_id);

        $latestTahun = Periode::max('tahun') ?? date('Y');
        $tahun = $request->tahun ?? $latestTahun;
        $jenis = $request->jenis ?? 'tahun'; // tahun, triwulan, semester, bulan
        $triwulan = $request->triwulan ?? 'Q1';
        $semester = $request->semester ?? 'S1';
        $bulanAngka = $request->bulan_angka ?? 1;
        $bulanMulti = $request->bulan_multi ?? []; // Multi-select bulan Bab 4.9
        $bidangId = $isOperatorScoped ? $user->bidang_id : ($request->bidang_id ?? 'all');

        // Query Periodes based on Jenis
        $periodesQuery = Periode::where('tahun', $tahun)->orderBy('bulan_angka');

        $judulPeriode = "TAHUN {$tahun}";
        if ($jenis === 'triwulan') {
            $triwulanMap = [
                'Q1' => [1, 2, 3],
                'Q2' => [4, 5, 6],
                'Q3' => [7, 8, 9],
                'Q4' => [10, 11, 12],
            ];
            $months = $triwulanMap[$triwulan] ?? [1, 2, 3];
            $periodesQuery->whereIn('bulan_angka', $months);
            $judulPeriode = "TRIWULAN {$triwulan} ({$tahun})";
        } elseif ($jenis === 'semester') {
            $semesterMap = [
                'S1' => [1, 2, 3, 4, 5, 6],
                'S2' => [7, 8, 9, 10, 11, 12],
            ];
            $months = $semesterMap[$semester] ?? [1, 2, 3, 4, 5, 6];
            $periodesQuery->whereIn('bulan_angka', $months);
            $namaSem = $semester === 'S1' ? 'SEMESTER I' : 'SEMESTER II';
            $judulPeriode = "{$namaSem} ({$tahun})";
        } elseif ($jenis === 'bulan') {
            if (is_array($bulanMulti) && count($bulanMulti) > 0) {
                $periodesQuery->whereIn('bulan_angka', $bulanMulti);
                $judulPeriode = "MULTI-BULAN (" . implode(', ', $bulanMulti) . ") {$tahun}";
            } else {
                $periodesQuery->where('bulan_angka', $bulanAngka);
                $pObj = Periode::where('tahun', $tahun)->where('bulan_angka', $bulanAngka)->first();
                $namaBulanStr = $pObj ? strtoupper($pObj->bulan) : "BULAN {$bulanAngka}";
                $judulPeriode = "BULAN {$namaBulanStr} {$tahun}";
            }
        }

        $periodes = $periodesQuery->get();
        $periodeIds = $periodes->pluck('id');

        // Bidang Filter
        if ($bidangId !== 'all') {
            $bidangs = Bidang::where('id', $bidangId)->get();
        } else {
            $bidangs = Bidang::all();
        }
        $bidangIds = $bidangs->pluck('id');

        $spreadsheet = new Spreadsheet();

        // ==========================================
        // SHEET 1: Matriks Rekapitulasi Per Bidang
        // ==========================================
        $sheet1 = $spreadsheet->getActiveSheet();
        $sheet1->setTitle("Matriks Rekapitulasi");

        // Title Header
        $sheet1->mergeCells('A1:N1');
        $sheet1->setCellValue('A1', "REKAPITULASI ALOKASI HONOR PER BIDANG - {$judulPeriode}");
        $sheet1->getStyle('A1')->getFont()->setBold(true)->setSize(14)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('0F172A'));
        $sheet1->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Table Column Headers (Row 3)
        $sheet1->setCellValue('A3', 'BIDANG / TIM KERJA');
        $colIndex = 2; // Column B
        foreach ($periodes as $p) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex);
            $sheet1->setCellValue($colLetter . '3', strtoupper($p->bulan));
            $colIndex++;
        }
        $lastColLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex);
        $sheet1->setCellValue($lastColLetter . '3', 'TOTAL (RP)');

        // Style Table Header (Row 3)
        $sheet1->getStyle("A3:{$lastColLetter}3")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0F172A']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet1->getRowDimension(3)->setRowHeight(28);

        // Data Rows
        $currentRow = 4;
        $rekapData = [];
        foreach ($bidangs as $bidang) {
            $sheet1->setCellValue("A{$currentRow}", $bidang->nama);
            $sheet1->getStyle("A{$currentRow}")->getFont()->setBold(true);

            $colIndex = 2;
            $rowSum = 0;
            $rowValues = [];
            foreach ($periodes as $periode) {
                $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex);
                $sum = AlokasiHonor::whereHas('kegiatan', fn ($q) => $q->where('bidang_id', $bidang->id))
                    ->where('periode_id', $periode->id)
                    ->sum('nominal');

                $val = (float) $sum;
                $sheet1->setCellValue("{$colLetter}{$currentRow}", $val);
                $sheet1->getStyle("{$colLetter}{$currentRow}")->getNumberFormat()->setFormatCode('"Rp "#,##0');

                $rowValues[$periode->id] = $val;
                $rowSum += $val;
                $colIndex++;
            }

            // Total Column
            $sheet1->setCellValue("{$lastColLetter}{$currentRow}", $rowSum);
            $sheet1->getStyle("{$lastColLetter}{$currentRow}")->getNumberFormat()->setFormatCode('"Rp "#,##0');
            $sheet1->getStyle("{$lastColLetter}{$currentRow}")->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('047857'));

            $rowValues['total'] = $rowSum;
            $rekapData[] = $rowValues;

            $currentRow++;
        }

        // Grand Total Row
        $sheet1->setCellValue("A{$currentRow}", 'TOTAL KESELURUHAN');
        $colIndex = 2;
        $grandTotal = 0;
        foreach ($periodes as $p) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex);
            $colSum = array_sum(array_column($rekapData, $p->id));
            $grandTotal += $colSum;

            $sheet1->setCellValue("{$colLetter}{$currentRow}", $colSum);
            $sheet1->getStyle("{$colLetter}{$currentRow}")->getNumberFormat()->setFormatCode('"Rp "#,##0');
            $colIndex++;
        }

        $sheet1->setCellValue("{$lastColLetter}{$currentRow}", $grandTotal);
        $sheet1->getStyle("{$lastColLetter}{$currentRow}")->getNumberFormat()->setFormatCode('"Rp "#,##0');

        // Style Grand Total Row
        $sheet1->getStyle("A{$currentRow}:{$lastColLetter}{$currentRow}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => '047857']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D1FAE5']],
        ]);
        $sheet1->getRowDimension($currentRow)->setRowHeight(24);

        // Add Grid Borders Sheet 1
        $sheet1->getStyle("A3:{$lastColLetter}{$currentRow}")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'CBD5E1'],
                ],
            ],
        ]);

        for ($i = 1; $i <= $colIndex; $i++) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i);
            $sheet1->getColumnDimension($colLetter)->setAutoSize(true);
        }
        $sheet1->freezePane('C4');

        // ==========================================
        // SHEET 2: Rincian Detail Kegiatan
        // ==========================================
        $sheet2 = $spreadsheet->createSheet();
        $sheet2->setTitle("Rincian Kegiatan");

        $sheet2->mergeCells('A1:G1');
        $sheet2->setCellValue('A1', "RINCIAN DETAIL KEGIATAN STATISTIK - {$judulPeriode}");
        $sheet2->getStyle('A1')->getFont()->setBold(true)->setSize(13)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('0F172A'));
        $sheet2->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet2Headers = ['NO', 'KODE MAK', 'NAMA KEGIATAN STATISTIK', 'BIDANG / TIM KERJA', 'PERIODE BULAN', 'JUMLAH MITRA TERLIBAT', 'TOTAL HONOR KEGIATAN (RP)'];
        foreach ($sheet2Headers as $idx => $hText) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($idx + 1);
            $sheet2->setCellValue("{$colLetter}3", $hText);
        }

        $sheet2->getStyle("A3:G3")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E3A8A']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet2->getRowDimension(3)->setRowHeight(25);

        // Fetch Kegiatan details
        $kegiatanList = Kegiatan::with(['bidang'])
            ->where('nama', 'NOT LIKE', '%#REF!%')
            ->where('kode_mata_anggaran', 'NOT LIKE', '%#REF!%')
            ->whereIn('bidang_id', $bidangIds)
            ->get();

        $rIdx = 4;
        $totalKegiatanHonor = 0;
        foreach ($kegiatanList as $idx => $keg) {
            $sumHonor = AlokasiHonor::where('kegiatan_id', $keg->id)
                ->whereIn('periode_id', $periodeIds)
                ->sum('nominal');

            $mitraCount = AlokasiHonor::where('kegiatan_id', $keg->id)
                ->whereIn('periode_id', $periodeIds)
                ->distinct('mitra_id')
                ->count('mitra_id');

            if ($sumHonor > 0 || count($kegiatanList) < 50) {
                $sheet2->setCellValue("A{$rIdx}", $idx + 1);
                $sheet2->setCellValue("B{$rIdx}", $keg->kode_mata_anggaran ?: '-');
                $sheet2->setCellValue("C{$rIdx}", $keg->nama);
                $sheet2->setCellValue("D{$rIdx}", $keg->bidang->nama ?? '-');
                
                $kegPeriodeNames = AlokasiHonor::where('kegiatan_id', $keg->id)
                    ->whereIn('periode_id', $periodeIds)
                    ->with('periode')
                    ->get()
                    ->pluck('periode.bulan')
                    ->unique()
                    ->filter()
                    ->implode(', ');

                $sheet2->setCellValue("E{$rIdx}", $kegPeriodeNames ?: $judulPeriode);
                $sheet2->setCellValue("F{$rIdx}", $mitraCount);
                $sheet2->setCellValue("G{$rIdx}", (float) $sumHonor);
                $sheet2->getStyle("G{$rIdx}")->getNumberFormat()->setFormatCode('"Rp "#,##0');

                $totalKegiatanHonor += $sumHonor;
                $rIdx++;
            }
        }

        // Sheet 2 Total Row
        $sheet2->setCellValue("A{$rIdx}", 'TOTAL RINCIAN KEGIATAN');
        $sheet2->mergeCells("A{$rIdx}:F{$rIdx}");
        $sheet2->getStyle("A{$rIdx}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet2->setCellValue("G{$rIdx}", $totalKegiatanHonor);
        $sheet2->getStyle("G{$rIdx}")->getNumberFormat()->setFormatCode('"Rp "#,##0');

        $sheet2->getStyle("A{$rIdx}:G{$rIdx}")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => '1E3A8A']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'DBEAFE']],
        ]);

        $sheet2->getStyle("A3:G{$rIdx}")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'CBD5E1'],
                ],
            ],
        ]);

        for ($i = 1; $i <= 7; $i++) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i);
            $sheet2->getColumnDimension($colLetter)->setAutoSize(true);
        }
        $sheet2->freezePane('A4');
        $sheet2->setAutoFilter("A3:G{$rIdx}");

        // ==========================================
        // SHEET 3: Rincian Detail Alokasi Honor Mitra
        // ==========================================
        $sheet3 = $spreadsheet->createSheet();
        $sheet3->setTitle("Rincian Detail Mitra");

        // Sheet 3 Title
        $sheet3->mergeCells('A1:L1');
        $sheet3->setCellValue('A1', "RINCIAN DETAIL ALOKASI HONOR MITRA BPS - {$judulPeriode}");
        $sheet3->getStyle('A1')->getFont()->setBold(true)->setSize(13)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('0F172A'));
        $sheet3->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Sheet 3 Headers (Row 3)
        $sheet3Headers = [
            'NO', 'NAMA MITRA', 'ALAMAT DETAIL', 'PEKERJAAN', 'PERIODE', 
            'NAMA KEGIATAN', 'KODE MAK', 'BIDANG', 'VOLUME', 'SATUAN', 
            'HONOR SATUAN (RP)', 'TOTAL HONOR (RP)'
        ];
        foreach ($sheet3Headers as $idx => $hText) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($idx + 1);
            $sheet3->setCellValue("{$colLetter}3", $hText);
        }

        $sheet3->getStyle("A3:L3")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E293B']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet3->getRowDimension(3)->setRowHeight(25);

        // Query Detailed Alokasi Honor
        $detailHonors = AlokasiHonor::with(['mitra', 'periode', 'kegiatan.bidang'])
            ->whereIn('periode_id', $periodeIds)
            ->whereHas('kegiatan', function ($q) use ($bidangIds) {
                $q->whereIn('bidang_id', $bidangIds)
                  ->where('nama', 'NOT LIKE', '%#REF!%');
            })
            ->orderBy('periode_id')
            ->get();

        $rIndex = 4;
        $totalDetailHonor = 0;
        foreach ($detailHonors as $no => $item) {
            $sheet3->setCellValue("A{$rIndex}", $no + 1);
            $sheet3->setCellValue("B{$rIndex}", $item->mitra->nama ?? '-');
            $sheet3->setCellValue("C{$rIndex}", $item->mitra->alamat ?? '-');
            $sheet3->setCellValue("D{$rIndex}", $item->mitra->pekerjaan ?? '-');
            $sheet3->setCellValue("E{$rIndex}", ($item->periode->bulan ?? '') . ' ' . ($item->periode->tahun ?? ''));
            $sheet3->setCellValue("F{$rIndex}", $item->kegiatan->nama ?? '-');
            $sheet3->setCellValue("G{$rIndex}", $item->kegiatan->kode_mata_anggaran ?? '-');
            $sheet3->setCellValue("H{$rIndex}", $item->kegiatan->bidang->nama ?? '-');
            $sheet3->setCellValue("I{$rIndex}", $item->volume ?? 1);
            $sheet3->setCellValue("J{$rIndex}", $item->satuan ?? 'Kegiatan');
            
            $satuanVal = (float) ($item->nominal_satuan ?? $item->nominal);
            $sheet3->setCellValue("K{$rIndex}", $satuanVal);
            $sheet3->getStyle("K{$rIndex}")->getNumberFormat()->setFormatCode('"Rp "#,##0');

            $totalVal = (float) $item->nominal;
            $sheet3->setCellValue("L{$rIndex}", $totalVal);
            $sheet3->getStyle("L{$rIndex}")->getNumberFormat()->setFormatCode('"Rp "#,##0');

            $totalDetailHonor += $totalVal;
            $rIndex++;
        }

        // Sheet 3 Total Row
        $sheet3->setCellValue("A{$rIndex}", 'TOTAL DETAIL HONOR');
        $sheet3->mergeCells("A{$rIndex}:K{$rIndex}");
        $sheet3->getStyle("A{$rIndex}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet3->setCellValue("L{$rIndex}", $totalDetailHonor);
        $sheet3->getStyle("L{$rIndex}")->getNumberFormat()->setFormatCode('"Rp "#,##0');

        $sheet3->getStyle("A{$rIndex}:L{$rIndex}")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => '047857']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D1FAE5']],
        ]);

        // Sheet 3 Borders & Auto-fit
        $sheet3->getStyle("A3:L{$rIndex}")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'CBD5E1'],
                ],
            ],
        ]);

        for ($i = 1; $i <= 12; $i++) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i);
            $sheet3->getColumnDimension($colLetter)->setAutoSize(true);
        }
        $sheet3->freezePane('A4');
        $sheet3->setAutoFilter("A3:L{$rIndex}");

        // Set Active Sheet back to 0 (Matriks)
        $spreadsheet->setActiveSheetIndex(0);

        // Export to XLSX
        $cleanJudul = str_replace([' ', '(', ')'], '_', $judulPeriode);
        $filename = "Laporan_Honor_SIMANTRA_{$cleanJudul}.xlsx";
        $writer = new Xlsx($spreadsheet);

        ob_start();
        $writer->save('php://output');
        $content = ob_get_clean();

        return response($content)
            ->header('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->header('Content-Disposition', 'attachment; filename="'.$filename.'"');
    }

    public function exportMantraMatrix(Request $request)
    {
        $tahun = (int)($request->tahun ?? date('Y'));
        return \App\Support\MantraMatrixService::exportFilledMatrix($tahun);
    }
}
