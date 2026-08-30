<?php

namespace App\Support;

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ExcelStyler
{
    /**
     * Terapkan format header BPS (Navy Blue, Teks Putih Tebal, Alignment Tengah, Tinggi Baris Proporsional)
     */
    public static function applyHeaderStyle(Worksheet $sheet, string $range, int $rowHeight = 28): void
    {
        $sheet->getStyle($range)->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['argb' => 'FFFFFFFF'],
                'size' => 11,
                'name' => 'Calibri',
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => '0B3B60'], // BPS Navy Blue
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
        ]);

        // Ekstrak baris pertama dari range untuk set row height
        if (preg_match('/[A-Z]+(\d+):[A-Z]+(\d+)/', $range, $matches)) {
            $startRow = (int) $matches[1];
            $endRow = (int) $matches[2];
            for ($r = $startRow; $r <= $endRow; $r++) {
                $sheet->getRowDimension($r)->setRowHeight($rowHeight);
            }
        }
    }

    /**
     * Terapkan border grid tipis pada seluruh tabel data
     */
    public static function applyTableGrid(Worksheet $sheet, string $range, string $borderColor = 'D9D9D9'): void
    {
        $sheet->getStyle($range)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => $borderColor],
                ],
            ],
        ]);
    }

    /**
     * Terapkan Auto Width ke seluruh kolom dengan kalkulasi teks + padding ekstra agar tidak sempit
     */
    public static function applyAutoWidth(Worksheet $sheet, int $startCol = 1, ?int $endCol = null, int $padding = 4): void
    {
        $highestColStr = $sheet->getHighestColumn();
        $highestColNum = Coordinate::columnIndexFromString($highestColStr);
        $highestRow = $sheet->getHighestRow();
        $lastCol = $endCol ?? $highestColNum;

        for ($col = $startCol; $col <= $lastCol; $col++) {
            $colLetter = Coordinate::stringFromColumnIndex($col);
            $maxLen = 0;

            for ($row = 1; $row <= $highestRow; $row++) {
                $val = (string) $sheet->getCell($colLetter . $row)->getValue();
                $len = mb_strlen($val);
                if ($len > $maxLen) {
                    $maxLen = $len;
                }
            }

            // Set lebar kolom eksplisit dengan buffer padding yang lega
            $calculatedWidth = max(12, $maxLen + $padding);
            $sheet->getColumnDimension($colLetter)->setWidth($calculatedWidth);
            $sheet->getColumnDimension($colLetter)->setAutoSize(true);
        }
    }

    /**
     * Terapkan format dropdown validation (Data Validation) di dalam sel Excel
     */
    public static function applyDropdownValidation(Worksheet $sheet, string $range, array $options, string $promptTitle = 'Pilihan', string $promptMessage = 'Silakan pilih dari daftar'): void
    {
        $formula = '"' . implode(',', $options) . '"';
        
        // Cek range
        $cells = Coordinate::extractAllCellReferencesInRange($range);
        foreach ($cells as $cellRef) {
            $validation = $sheet->getCell($cellRef)->getDataValidation();
            $validation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
            $validation->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_INFORMATION);
            $validation->setAllowBlank(true);
            $validation->setShowInputMessage(true);
            $validation->setShowErrorMessage(true);
            $validation->setShowDropDown(true);
            $validation->setErrorTitle('Input Tidak Valid');
            $validation->setError('Nilai yang dimasukkan tidak ada dalam daftar pilihan.');
            $validation->setPromptTitle($promptTitle);
            $validation->setPrompt($promptMessage);
            $validation->setFormula1($formula);
        }
    }

    /**
     * Terapkan format angka / rupiah
     */
    public static function applyCurrencyFormat(Worksheet $sheet, string $range): void
    {
        $sheet->getStyle($range)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle($range)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
    }

    /**
     * Terapkan format angka desimal / volume
     */
    public static function applyNumberFormat(Worksheet $sheet, string $range): void
    {
        $sheet->getStyle($range)->getNumberFormat()->setFormatCode('#,##0.##');
        $sheet->getStyle($range)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
    }

    /**
     * Terapkan format teks center
     */
    public static function applyAlignCenter(Worksheet $sheet, string $range): void
    {
        $sheet->getStyle($range)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    }

    /**
     * Terapkan format sel Teks Eksplisit (@) agar angka panjang seperti NIK/ID Sobat/No HP tidak berubah jadi scientific notation (3.206E+15)
     */
    public static function applyTextFormat(Worksheet $sheet, string $range): void
    {
        $sheet->getStyle($range)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);
    }

    /**
     * Pembersih nomor string: hapus tanda petik awalan (') dan ubah notasi ilmiah (3.2E+15) menjadi digit string utuh
     */
    public static function cleanStringNumber($val): string
    {
        if ($val === null) return '';
        $str = trim((string)$val);
        if (empty($str)) return '';

        // Hapus tanda petik satu di awal jika ada
        if (str_starts_with($str, "'")) {
            $str = ltrim($str, "'");
        }

        // Jika berbentuk scientific notation (misal: 3.20601E+15 atau 3,20601E+15)
        if (preg_match('/^[0-9]+[.,]?[0-9]*[eE][+-]?[0-9]+$/', $str)) {
            $normalized = str_replace(',', '.', $str);
            $floatVal = (float)$normalized;
            $str = sprintf('%.0f', $floatVal);
        }

        return trim($str);
    }

    /**
     * Parsing tanggal dari Excel secara fleksibel:
     * - Mendukung Excel Serial Date (e.g. 38059 -> 2004-03-14)
     * - Mendukung DD/MM/YYYY atau DD-MM-YYYY (e.g. 14/03/2004 -> 2004-03-14)
     * - Mendukung YYYY-MM-DD atau YYYY/MM/DD (e.g. 2004-03-14)
     * - Mendukung teks tanggal umum melalui Carbon
     * Output selalu format standar: YYYY-MM-DD (atau null jika kosong/invalid)
     */
    public static function parseDate($val): ?string
    {
        if ($val === null) return null;
        $str = trim((string)$val);
        if ($str === '' || $str === '-' || str_starts_with($str, '=')) return null;

        // 1. Jika numerik murni (kemungkinan Excel Serial Date Number, misal: 38059 untuk 14-03-2004)
        if (is_numeric($str) && (float)$str > 1000 && (float)$str < 100000) {
            try {
                $dt = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float)$str);
                return $dt->format('Y-m-d');
            } catch (\Throwable $e) {}
        }

        // 2. Format DD/MM/YYYY atau DD-MM-YYYY (misal: 14/03/2004 atau 14-03-2004)
        if (preg_match('/^(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})$/', $str, $matches)) {
            $day = (int)$matches[1];
            $month = (int)$matches[2];
            $year = (int)$matches[3];
            if (checkdate($month, $day, $year)) {
                return sprintf('%04d-%02d-%02d', $year, $month, $day);
            }
        }

        // 3. Format YYYY-MM-DD atau YYYY/MM/DD (misal: 2004-03-14 atau 2004/03/14)
        if (preg_match('/^(\d{4})[\/\-](\d{1,2})[\/\-](\d{1,2})$/', $str, $matches)) {
            $year = (int)$matches[1];
            $month = (int)$matches[2];
            $day = (int)$matches[3];
            if (checkdate($month, $day, $year)) {
                return sprintf('%04d-%02d-%02d', $year, $month, $day);
            }
        }

        // 4. Fallback dengan Carbon
        try {
            return \Carbon\Carbon::parse($str)->format('Y-m-d');
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Kunci baris header (Freeze Panes)
     */
    public static function freezeHeader(Worksheet $sheet, string $cell = 'A2'): void
    {
        $sheet->freezePane($cell);
    }
}
