<?php

require __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;

$spreadsheet = new Spreadsheet();
$spreadsheet->removeSheetByIndex(0);

$sheetNames = ['JANUARI', 'FEBRUARI', 'MARET'];

$dummyMitras = [
    [
        'no' => 1,
        'nama' => 'Budi Santoso (Dummy 2025)',
        'alamat' => 'Kec. Singaparna',
        'pekerjaan' => 'Mitra Statistik 2025',
        'kode_alamat' => '3206010',
        'jk' => '1',
        'total_honor' => 3500000,
        'kegiatan' => 'Pendataan Lapangan Susenas 2025',
        'mak' => '054.01.GG.2894.BMA.001.051.A.521213',
        'honor_neraca' => 0,
        'honor_produksi' => 0,
        'honor_distribusi' => 0,
        'honor_sosial' => 3500000,
    ],
    [
        'no' => 2,
        'nama' => 'Siti Nurhaliza (Dummy 2025)',
        'alamat' => 'Kec. Ciawi',
        'pekerjaan' => 'Mitra Pengolahan 2025',
        'kode_alamat' => '3206020',
        'jk' => '2',
        'total_honor' => 2800000,
        'kegiatan' => 'Pengolahan Data Sensus Ekonomi 2025',
        'mak' => '054.01.GG.2898.BMA.002.052.A.521213',
        'honor_neraca' => 2800000,
        'honor_produksi' => 0,
        'honor_distribusi' => 0,
        'honor_sosial' => 0,
    ],
    [
        'no' => 3,
        'nama' => 'Ahmad Hidayat (Dummy 2025)',
        'alamat' => 'Kec. Manonjaya',
        'pekerjaan' => 'Mitra Lapangan 2025',
        'kode_alamat' => '3206030',
        'jk' => '1',
        'total_honor' => 4200000,
        'kegiatan' => 'Survei Konstruksi Triwulanan 2025',
        'mak' => '054.01.GG.2910.QMA.003.053.A.521213',
        'honor_neraca' => 0,
        'honor_produksi' => 4200000,
        'honor_distribusi' => 0,
        'honor_sosial' => 0,
    ]
];

foreach ($sheetNames as $sheetName) {
    $sheet = $spreadsheet->createSheet();
    $sheet->setTitle($sheetName);

    $sheet->setCellValue('A1', 'BADAN PUSAT STATISTIK KABUPATEN TASIKMALAYA');
    $sheet->setCellValue('A2', "MONITORING ALOKASI PEKERJAAN & HONOR MITRA (MANTRA) - TAHUN 2025 ({$sheetName})");

    $headers = [
        'A6' => 'NO',
        'B6' => 'NAMA MITRA',
        'C6' => 'ALAMAT',
        'D6' => 'PEKERJAAN',
        'E6' => 'KODE ALAMAT',
        'F6' => 'JK',
        'G6' => 'TOTAL HONOR',
        'I6' => 'NAMA KEGIATAN STATISTIK',
        'J6' => 'KODE MAK',
        'L6' => 'NERACA',
        'M6' => 'PRODUKSI',
        'N6' => 'DISTRIBUSI',
        'O6' => 'SOSIAL',
        'BO6' => 'SBML PENCACAHAN',
        'BS6' => 'SBML PENGOLAHAN',
    ];

    foreach ($headers as $cell => $text) {
        $sheet->setCellValue($cell, $text);
        $sheet->getStyle($cell)->getFont()->setBold(true);
        $sheet->getStyle($cell)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('2563EB');
        $sheet->getStyle($cell)->getFont()->getColor()->setRGB('FFFFFF');
    }

    $row = 7;
    foreach ($dummyMitras as $data) {
        $sheet->setCellValue('A' . $row, $data['no']);
        $sheet->setCellValue('B' . $row, $data['nama']);
        $sheet->setCellValue('C' . $row, $data['alamat']);
        $sheet->setCellValue('D' . $row, $data['pekerjaan']);
        $sheet->setCellValue('E' . $row, $data['kode_alamat']);
        $sheet->setCellValue('F' . $row, $data['jk']);
        $sheet->setCellValue('G' . $row, $data['total_honor']);
        $sheet->setCellValue('I' . $row, $data['kegiatan']);
        $sheet->setCellValue('J' . $row, $data['mak']);
        $sheet->setCellValue('L' . $row, $data['honor_neraca']);
        $sheet->setCellValue('M' . $row, $data['honor_produksi']);
        $sheet->setCellValue('N' . $row, $data['honor_distribusi']);
        $sheet->setCellValue('O' . $row, $data['honor_sosial']);
        $sheet->setCellValue('BO' . $row, 4500000);
        $sheet->setCellValue('BS' . $row, 3000000);
        $row++;
    }

    foreach (range('A', 'J') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }
}

$outputFile = __DIR__ . '/../public/sample_mantra_dummy_2025.xlsx';
$writer = new Xlsx($spreadsheet);
$writer->save($outputFile);

echo "File Excel dummy berhasil dibuat: {$outputFile}\n";
