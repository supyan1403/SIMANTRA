<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Mitra;
use PhpOffice\PhpSpreadsheet\IOFactory;

$file = 'd:/SIMANTRA/1. Input MANTRA (Monitoring Alokasi Pekerjaan Dan Honor Mitra) oleh PJ Kegiatan atau Ketua Tim - Update3.xlsx.xlsx';

if (!file_exists($file)) {
    echo "Berkas tidak ditemukan: $file\n";
    exit(1);
}

echo "Membaca berkas Excel master SOBAT BPS...\n";
$spreadsheet = IOFactory::load($file);
$sheet = $spreadsheet->getSheetByName('DB Mitra 2024 (2621)');

if (!$sheet) {
    echo "Sheet DB Mitra 2024 (2621) tidak ditemukan!\n";
    exit(1);
}

$max = $sheet->getHighestRow();
echo "Memproses {$max} baris data mitra...\n";
$count = 0;

for ($r = 3; $r <= $max; $r++) {
    $nama = trim((string)$sheet->getCell('B' . $r)->getValue());
    if (empty($nama) || $nama === 'Nama') continue;

    $nik = trim((string)$sheet->getCell('G' . $r)->getValue());
    $posisi = trim((string)$sheet->getCell('H' . $r)->getValue());
    $email = trim((string)$sheet->getCell('J' . $r)->getValue());
    $npwp = trim((string)$sheet->getCell('R' . $r)->getValue());
    $tglLahir = trim((string)$sheet->getCell('Q' . $r)->getValue());
    $pendidikan = trim((string)$sheet->getCell('V' . $r)->getValue());
    $sobatId = trim((string)$sheet->getCell('AJ' . $r)->getValue());
    
    $sp = (int)$sheet->getCell('AA' . $r)->getValue() === 1;
    $st = (int)$sheet->getCell('AB' . $r)->getValue() === 1;
    $se = (int)$sheet->getCell('AC' . $r)->getValue() === 1;
    $susenas = (int)$sheet->getCell('AD' . $r)->getValue() === 1;
    $sakernas = (int)$sheet->getCell('AE' . $r)->getValue() === 1;
    $sbh = (int)$sheet->getCell('AF' . $r)->getValue() === 1;

    $mitra = Mitra::where('nama', $nama)
        ->orWhere(function($q) use ($sobatId, $nik) {
            if (!empty($sobatId)) $q->where('id_sobat', $sobatId);
            if (!empty($nik)) $q->orWhere('nik', $nik);
        })->first();

    $payload = array_filter([
        'nik' => $nik ?: null,
        'posisi' => $posisi ?: null,
        'id_sobat' => $sobatId ?: null,
        'email' => $email ?: null,
        'npwp' => $npwp ?: null,
        'tanggal_lahir' => $tglLahir ?: null,
        'pendidikan' => $pendidikan ?: null,
    ]);
    $payload['exp_sp'] = $sp;
    $payload['exp_st'] = $st;
    $payload['exp_se'] = $se;
    $payload['exp_susenas'] = $susenas;
    $payload['exp_sakernas'] = $sakernas;
    $payload['exp_sbh'] = $sbh;

    if ($mitra) {
        $mitra->update($payload);
        $count++;
    }
}

echo "SELESAI! Berhasil menyinkronkan data lengkap SOBAT untuk {$count} mitra!\n";
