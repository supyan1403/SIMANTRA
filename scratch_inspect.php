<?php
require __DIR__ . '/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

ini_set('memory_limit', '-1');

$filePath = 'd:\SIMANTRA\1. Input MANTRA (Monitoring Alokasi Pekerjaan Dan Honor Mitra) oleh PJ Kegiatan atau Ketua Tim - Update3.xlsx.xlsx';

echo "Reading DB Mitra sheet...\n";
$spreadsheet = IOFactory::load($filePath);

$sheetName = 'DB Mitra 2024 (2621)';
$sheet = $spreadsheet->getSheetByName($sheetName);
if (!$sheet) {
    echo "Sheet '$sheetName' not found! Sheet names:\n";
    print_r($spreadsheet->getSheetNames());
    exit(1);
}

echo "Highest Row in DB Mitra: " . $sheet->getHighestRow() . "\n";

for ($row = 1; $row <= 15; $row++) {
    $a = $sheet->getCell('A' . $row)->getCalculatedValue();
    $b = $sheet->getCell('B' . $row)->getCalculatedValue();
    $c = $sheet->getCell('C' . $row)->getCalculatedValue();
    $d = $sheet->getCell('D' . $row)->getCalculatedValue();
    echo "Row $row | A: '$a' | B: '$b' | C: '$c' | D: '$d'\n";
}
