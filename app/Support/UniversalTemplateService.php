<?php

namespace App\Support;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class UniversalTemplateService
{
    /**
     * Generate berkas Excel Template Universal All-in-One SIMANTRA
     */
    public static function generateTemplate()
    {
        $spreadsheet = new Spreadsheet();

        // -------------------------------------------------------------
        // SHEET 1: DATA MITRA
        // -------------------------------------------------------------
        $sheet1 = $spreadsheet->getActiveSheet();
        $sheet1->setTitle('DATA MITRA');

        $mitraHeaders = [
            'NO',
            'ID SOBAT',
            'NIK (KTP)',
            'NAMA LENGKAP MITRA',
            'POSISI MITRA',
            'JENIS KELAMIN (L/P)',
            'NO TELEPON / WA',
            'EMAIL',
            'NPWP',
            'PENDIDIKAN',
            'PEKERJAAN',
            'KABUPATEN / KOTA',
            'KECAMATAN',
            'DESA / KELURAHAN',
            'ALAMAT DETAIL'
        ];

        ExcelStyler::applyHeaderStyle($sheet1, 'A1:O1');
        foreach ($mitraHeaders as $idx => $header) {
            $colLetter = Coordinate::stringFromColumnIndex($idx + 1);
            $sheet1->setCellValue($colLetter . '1', $header);
        }

        // Sample Data Mitra
        $sampleMitras = [
            [1, '320601234', '3206010101900001', 'BUDI SANTOSO', 'Pendata Lapangan', 'L', '081234567890', 'budi@bps.go.id', '12.345.678.9-425.000', 'S1 / Sarjana', 'Aparat Desa', 'Kabupaten Tasikmalaya', 'Cipatujah', 'Ciheras', 'Kp. Cisarua RT 02/01'],
            [2, '320601235', '3206010202950002', 'SITI AMINAH', 'Pengolah Data', 'P', '082198765432', 'siti@bps.go.id', '', 'D3 / Diploma', 'Mengurus Rumah Tangga', 'Kabupaten Tasikmalaya', 'Puspahiang', 'Mandalasari', 'Kp. Sindangsono No 4'],
            [3, '320601236', '3206010303880003', 'AHMAD FAUZI', 'Pendata Lapangan', 'L', '085712345678', 'ahmad@bps.go.id', '', 'SMA / Sederajat', 'Wiraswasta', 'Kabupaten Tasikmalaya', 'Singaparna', 'Sukarame', 'Jl. Raya Timur No 12'],
        ];

        foreach ($sampleMitras as $rIdx => $row) {
            $rowNum = $rIdx + 2;
            foreach ($row as $cIdx => $val) {
                $colLetter = Coordinate::stringFromColumnIndex($cIdx + 1);
                if (in_array($cIdx + 1, [2, 3, 7, 9])) { // ID SOBAT, NIK, NO HP, NPWP
                    $sheet1->setCellValueExplicit($colLetter . $rowNum, (string)$val, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                } else {
                    $sheet1->setCellValue($colLetter . $rowNum, $val);
                }
            }
        }

        ExcelStyler::applyTableGrid($sheet1, 'A1:O4');
        ExcelStyler::applyAlignCenter($sheet1, 'A2:A4');
        ExcelStyler::applyAlignCenter($sheet1, 'C2:C4');
        ExcelStyler::applyAlignCenter($sheet1, 'F2:F4');
        ExcelStyler::applyTextFormat($sheet1, 'B2:C10000');
        ExcelStyler::applyTextFormat($sheet1, 'G2:G10000');
        ExcelStyler::applyTextFormat($sheet1, 'I2:I10000');
        ExcelStyler::applyDropdownValidation($sheet1, 'E2:E500', ['Pendata Lapangan', 'Pengolah Data', 'Pemeriksa Lapangan', 'Pemeriksa Pengolahan'], 'Posisi Mitra', 'Pilih peran tugas mitra');
        ExcelStyler::applyDropdownValidation($sheet1, 'F2:F500', ['L', 'P'], 'Jenis Kelamin', 'Pilih L untuk Laki-laki atau P untuk Perempuan');
        ExcelStyler::applyDropdownValidation($sheet1, 'J2:J500', ['SMA / SMK / MA / Sederajat', 'S1 / Sarjana (D4/S1)', 'D1 / D2 / D3 (Diploma)', 'S2 / Pascasarjana', 'SMP / MTs / Sederajat', 'SD / MI / Sederajat', 'S3 / Doktor', 'Lainnya'], 'Pendidikan', 'Pilih jenjang pendidikan');
        ExcelStyler::freezeHeader($sheet1, 'E2');
        ExcelStyler::applyAutoWidth($sheet1, 1, 15);


        // -------------------------------------------------------------
        // SHEET 2: MATA ANGGARAN & KEGIATAN
        // -------------------------------------------------------------
        $sheet2 = $spreadsheet->createSheet();
        $sheet2->setTitle('MATA ANGGARAN');

        $kegiatanHeaders = [
            'NO',
            'BIDANG / TIM KERJA',
            'NAMA KEGIATAN STATISTIK',
            'KODE MAK / AKUN',
            'TAHUN',
            'SATUAN',
            'TARIF SATUAN (RP)',
            'TARGET VOLUME'
        ];

        ExcelStyler::applyHeaderStyle($sheet2, 'A1:H1');
        foreach ($kegiatanHeaders as $idx => $header) {
            $colLetter = Coordinate::stringFromColumnIndex($idx + 1);
            $sheet2->setCellValue($colLetter . '1', $header);
        }

        // Sample Data Kegiatan
        $sampleKegiatans = [
            [1, 'Distribusi', 'Survei Harga Konsumen Perdesaan (HKD)', '054.01.GG.2903.BMA.009.005.A.521213', 2026, 'Dokumen', 65000, 100],
            [2, 'Produksi', 'Pendataan Lapangan Ubinan Padi & Palawija', '054.01.GG.2910.QMA.007.005.A.521213', 2026, 'Ubinan', 77000, 50],
            [3, 'Sosial', 'Survei Sosial Ekonomi Nasional (SUSENAS)', '054.01.GG.2894.BMA.001.051.A.521213', 2026, 'Dokumen', 150000, 200],
            [4, 'Neraca', 'Survei SKTNP Jasa', '054.01.GG.2899.BMA.006.005.A.521213', 2026, 'Responden', 55000, 40],
        ];

        foreach ($sampleKegiatans as $rIdx => $row) {
            $rowNum = $rIdx + 2;
            foreach ($row as $cIdx => $val) {
                $colLetter = Coordinate::stringFromColumnIndex($cIdx + 1);
                if ($cIdx + 1 === 4) { // KODE MAK
                    $sheet2->setCellValueExplicit($colLetter . $rowNum, (string)$val, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                } else {
                    $sheet2->setCellValue($colLetter . $rowNum, $val);
                }
            }
        }

        ExcelStyler::applyTableGrid($sheet2, 'A1:H5');
        ExcelStyler::applyAlignCenter($sheet2, 'A2:A5');
        ExcelStyler::applyAlignCenter($sheet2, 'E2:F5');
        ExcelStyler::applyTextFormat($sheet2, 'D2:D10000');
        ExcelStyler::applyCurrencyFormat($sheet2, 'G2:G5');
        ExcelStyler::applyNumberFormat($sheet2, 'H2:H5');
        ExcelStyler::applyDropdownValidation($sheet2, 'B2:B500', ['Distribusi', 'Neraca', 'Produksi', 'Sosial', 'IPDS', 'Cadangan'], 'Bidang Kerja', 'Pilih tim kerja BPS');
        ExcelStyler::freezeHeader($sheet2, 'A2');
        ExcelStyler::applyAutoWidth($sheet2, 1, 8);


        // -------------------------------------------------------------
        // SHEET 3: ALOKASI & PENUGASAN (TRANSAKSI BULANAN)
        // -------------------------------------------------------------
        $sheet3 = $spreadsheet->createSheet();
        $sheet3->setTitle('ALOKASI PENUGASAN');

        $alokasiHeaders = [
            'NO',
            'TAHUN',
            'BULAN',
            'NAMA MITRA',
            'NAMA KEGIATAN',
            'VOLUME TUGAS',
            'SATUAN',
            'TARIF SATUAN (RP)',
            'TOTAL HONOR (RP)',
            'TANGGAL SPK (YYYY-MM-DD)'
        ];

        ExcelStyler::applyHeaderStyle($sheet3, 'A1:J1');
        foreach ($alokasiHeaders as $idx => $header) {
            $colLetter = Coordinate::stringFromColumnIndex($idx + 1);
            $sheet3->setCellValue($colLetter . '1', $header);
        }

        // Sample Data Alokasi
        $sampleAlokasis = [
            [1, 2026, 'Januari', 'BUDI SANTOSO', 'Survei Harga Konsumen Perdesaan (HKD)', 10, 'Dokumen', 65000, 650000, '2026-01-05'],
            [2, 2026, 'Januari', 'BUDI SANTOSO', 'Pendataan Lapangan Ubinan Padi & Palawija', 5, 'Ubinan', 77000, 385000, '2026-01-05'],
            [3, 2026, 'Januari', 'SITI AMINAH', 'Survei Sosial Ekonomi Nasional (SUSENAS)', 8, 'Dokumen', 150000, 1200000, '2026-01-05'],
            [4, 2026, 'Februari', 'BUDI SANTOSO', 'Survei Harga Konsumen Perdesaan (HKD)', 10, 'Dokumen', 65000, 650000, '2026-02-02'],
            [5, 2026, 'Februari', 'AHMAD FAUZI', 'Survei SKTNP Jasa', 10, 'Responden', 55000, 550000, '2026-02-02'],
        ];

        foreach ($sampleAlokasis as $rIdx => $row) {
            $rowNum = $rIdx + 2;
            foreach ($row as $cIdx => $val) {
                $colLetter = Coordinate::stringFromColumnIndex($cIdx + 1);
                $sheet3->setCellValue($colLetter . $rowNum, $val);
            }
        }

        ExcelStyler::applyTableGrid($sheet3, 'A1:J6');
        ExcelStyler::applyAlignCenter($sheet3, 'A2:C6');
        ExcelStyler::applyAlignCenter($sheet3, 'F2:G6');
        ExcelStyler::applyAlignCenter($sheet3, 'J2:J6');
        ExcelStyler::applyCurrencyFormat($sheet3, 'H2:I6');
        ExcelStyler::applyDropdownValidation($sheet3, 'C2:C1000', ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'], 'Bulan Penugasan', 'Pilih bulan kegiatan honor');
        ExcelStyler::freezeHeader($sheet3, 'A2');
        ExcelStyler::applyAutoWidth($sheet3, 1, 10);


        // -------------------------------------------------------------
        // SHEET 4: PANDUAN PENGISIAN
        // -------------------------------------------------------------
        $sheet4 = $spreadsheet->createSheet();
        $sheet4->setTitle('PANDUAN');

        $sheet4->setCellValue('A1', 'PETUNJUK PENGISIAN TEMPLATE UNIVERSAL SIMANTRA');
        $sheet4->getStyle('A1')->getFont()->setBold(true)->setSize(14)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('0B3B60'));

        $instructions = [
            '1. Template ini bersifat All-in-One (Cukup 1 file Excel untuk mengisi seluruh sistem SIMANTRA).',
            '2. Sheet DATA MITRA: Berisi daftar seluruh petugas mitra statistik BPS yang dapat bertugas.',
            '3. Sheet MATA ANGGARAN: Berisi daftar seluruh kegiatan statistik, kode MAK akun, tim kerja/bidang, dan tarif satuan.',
            '4. Sheet ALOKASI PENUGASAN: Berisi penugasan honor bulanan. Cukup tuliskan Nama Mitra dan Nama Kegiatan sesuai Sheet 1 dan Sheet 2.',
            '5. Jika 1 mitra mengerjakan beberapa kegiatan di bulan yang sama, cukup buat beberapa baris di Sheet ALOKASI PENUGASAN.',
            '6. Kolom TOTAL HONOR otomatis terisi rumus (=Volume * Tarif). Anda juga bisa mengetik nominal langsung.',
            '7. Setelah selesai diisi, unggah (upload) file ini ke menu Import SIMANTRA, dan seluruh data akan otomatis tersinkronisasi!',
        ];

        foreach ($instructions as $iIdx => $inst) {
            $r = $iIdx + 3;
            $sheet4->setCellValue('A' . $r, $inst);
            $sheet4->getStyle('A' . $r)->getFont()->setSize(11);
        }

        $sheet4->getColumnDimension('A')->setWidth(100);

        // Set Active sheet back to DATA MITRA
        $spreadsheet->setActiveSheetIndex(0);

        $filename = 'Template_Universal_SIMANTRA_All_in_One.xlsx';
        $writer = new Xlsx($spreadsheet);

        ob_start();
        $writer->save('php://output');
        $content = ob_get_clean();

        return response($content)
            ->header('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }
}
