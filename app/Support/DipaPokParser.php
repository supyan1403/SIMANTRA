<?php

namespace App\Support;

use PhpOffice\PhpSpreadsheet\IOFactory;

class DipaPokParser
{
    /**
     * Mapping kode kegiatan (4 digit) ke nama bidang
     */
    private const BIDANG_MAP = [
        '2886' => 'Bagian Umum',
        '2896' => 'IPDS',
        '2897' => 'IPDS',
        '2898' => 'Neraca',
        '2899' => 'Neraca',
        '2900' => 'IPDS',
        '2901' => 'IPDS',
        '2902' => 'Distribusi',
        '2903' => 'Harga',
        '2904' => 'Produksi',
        '2905' => 'Sosial',
        '2906' => 'Sosial',
        '2907' => 'Sosial',
        '2908' => 'Produksi',
        '2909' => 'Produksi',
        '2910' => 'Produksi',
    ];

    /**
     * Parse file xlsx DIPA/POK menjadi array kegiatan
     *
     * @param string $filePath Path ke file xlsx
     * @param string $jenisDokumen "DIPA" atau "POK"
     * @param string $revisiLabel Label revisi (misal: "DIPA Awal", "Revisi 9", "POK Rev 7")
     * @param string $tahun Tahun anggaran
     * @return array ['rows' => [...], 'stats' => [...]]
     */
    public static function parse(string $filePath, string $jenisDokumen, string $revisiLabel, string $tahun): array
    {
        $reader = IOFactory::createReader('Xlsx');
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($filePath);
        $sheet = $spreadsheet->getActiveSheet();

        // Hierarchy tracking
        $currentProgram = '';
        $currentKegiatan = '';
        $currentKegiatanNama = '';
        $currentSubKegiatan = '';
        $currentOutput = '';
        $currentInput = '';
        $currentSubKomponen = '';
        $currentAkun = '';

        $rows = [];
        $maxRow = $sheet->getHighestRow();

        for ($row = 1; $row <= $maxRow; $row++) {
            $colA = trim((string)($sheet->getCell('A' . $row)->getValue() ?? ''));
            $colD = trim((string)($sheet->getCell('D' . $row)->getValue() ?? ''));
            $colE = trim((string)($sheet->getCell('E' . $row)->getValue() ?? ''));
            $colG = trim((string)($sheet->getCell('G' . $row)->getValue() ?? ''));
            $colH = $sheet->getCell('H' . $row)->getValue();
            $colJ = $sheet->getCell('J' . $row)->getValue();
            $colK = trim((string)($sheet->getCell('K' . $row)->getValue() ?? ''));
            $colL = trim((string)($sheet->getCell('L' . $row)->getValue() ?? ''));

            // Skip empty rows
            if (empty($colA) && empty($colD)) {
                continue;
            }

            // Detect hierarchy level by column A patterns
            if (self::isProgram($colA)) {
                $currentProgram = $colA;
                continue;
            }

            if (self::isKegiatan($colA)) {
                $currentKegiatan = $colA;
                $currentKegiatanNama = $colD;
                // Reset sub-levels
                $currentSubKegiatan = '';
                $currentOutput = '';
                $currentInput = '';
                $currentSubKomponen = '';
                $currentAkun = '';
                continue;
            }

            if (self::isSubKegiatan($colA)) {
                $currentSubKegiatan = $colA;
                $currentOutput = '';
                $currentInput = '';
                $currentSubKomponen = '';
                $currentAkun = '';
                continue;
            }

            if (self::isOutput($colA)) {
                $currentOutput = $colA;
                $currentInput = '';
                $currentSubKomponen = '';
                $currentAkun = '';
                continue;
            }

            if (self::isInput($colA)) {
                $currentInput = $colA;
                $currentSubKomponen = '';
                $currentAkun = '';
                continue;
            }

            if (self::isSubKomponen($colA)) {
                $currentSubKomponen = $colA;
                $currentAkun = '';
                continue;
            }

            if (self::isAkun($colA)) {
                $currentAkun = $colA;
                continue;
            }

            // Skip KPPN line, location line, etc
            if (str_starts_with($colD, '(KPPN') || str_contains($colD, 'Lokasi')) {
                continue;
            }

            // Detect detail line (starts with -)
            if (str_starts_with($colD, '-') || str_starts_with($colD, '-')) {
                $nama = trim(ltrim($colD, '- '));
                if (empty($nama) && !empty($colE)) {
                    $nama = $colE;
                }

                // Parse volume + satuan from column G
                $volume = 0;
                $satuan = '';
                if (!empty($colG)) {
                    $parsed = self::parseVolumeSatuan($colG);
                    $volume = $parsed['volume'];
                    $satuan = $parsed['satuan'];
                }

                // Harga satuan
                $harga = is_numeric($colH) ? (float)$colH : 0;

                // Total
                $total = is_numeric($colJ) ? (float)$colJ : 0;

                // Jika total tidak ada tapi volume x harga ada, hitung
                if ($total == 0 && $volume > 0 && $harga > 0) {
                    $total = $volume * $harga;
                }

                // Skip baris yang tidak ada harganya
                if ($total == 0 && $harga == 0) {
                    continue;
                }

                // Build kode MAK
                $kodeMAK = self::buildKodeMAK(
                    $currentProgram,
                    $currentKegiatan,
                    $currentSubKegiatan,
                    $currentOutput,
                    $currentInput,
                    $currentSubKomponen,
                    $currentAkun
                );

                // Get bidang from kegiatan code
                $bidang = self::getBidang($currentKegiatan);

                $rows[] = [
                    'kode_mata_anggaran' => $kodeMAK,
                    'nama' => $nama,
                    'bidang' => $bidang,
                    'jumlah' => $volume,
                    'satuan' => $satuan,
                    'harga' => $harga,
                    'total' => $total,
                    'kegiatan_kode' => $currentKegiatan,
                    'kegiatan_nama' => $currentKegiatanNama,
                    'source_file' => basename($filePath),
                    'revisi_ke' => $revisiLabel,
                    'jenis_dokumen' => $jenisDokumen,
                    'tahun' => $tahun,
                ];
            }
        }

        // Stats
        $totalAnggaran = array_sum(array_column($rows, 'total'));
        $bidangStats = [];
        foreach ($rows as $r) {
            $b = $r['bidang'];
            if (!isset($bidangStats[$b])) {
                $bidangStats[$b] = ['count' => 0, 'total' => 0];
            }
            $bidangStats[$b]['count']++;
            $bidangStats[$b]['total'] += $r['total'];
        }

        return [
            'rows' => $rows,
            'stats' => [
                'total_items' => count($rows),
                'total_anggaran' => $totalAnggaran,
                'per_bidang' => $bidangStats,
                'jenis_dokumen' => $jenisDokumen,
                'revisi_label' => $revisiLabel,
                'tahun' => $tahun,
            ],
        ];
    }

    // === Hierarchy Detection Methods ===

    private static function isProgram(string $val): bool
    {
        // Format: 054.01.GG
        return (bool) preg_match('/^\d{3}\.\d{2}\.\w{2}$/', $val);
    }

    private static function isKegiatan(string $val): bool
    {
        // Format: 4 digit angka (2896, 2897, etc)
        return (bool) preg_match('/^\d{4}$/', $val);
    }

    private static function isSubKegiatan(string $val): bool
    {
        // Format: 2896.BMA, 2897.QDB
        return (bool) preg_match('/^\d{4}\.\w{2,5}$/', $val);
    }

    private static function isOutput(string $val): bool
    {
        // Format: 2896.BMA.004
        return (bool) preg_match('/^\d{4}\.\w{2,5}\.\d{3}$/', $val);
    }

    private static function isInput(string $val): bool
    {
        // Format: 3 digit angka (005, 051, 052)
        return (bool) preg_match('/^\d{3}$/', $val);
    }

    private static function isSubKomponen(string $val): bool
    {
        // Format: "A" (single letter)
        return (bool) preg_match('/^[A-Z]$/', $val);
    }

    private static function isAkun(string $val): bool
    {
        // Format: 6 digit angka (524113, 521213, etc)
        return (bool) preg_match('/^\d{6}$/', $val);
    }

    /**
     * Parse kolom G yang berisi "40.0 Dok" menjadi volume dan satuan
     */
    private static function parseVolumeSatuan(string $raw): array
    {
        $raw = trim($raw);

        // Pattern: "40.0 Dok", "1.0 OK", "10 RUTA", etc
        if (preg_match('/^([\d.,]+)\s*(.+)$/', $raw, $m)) {
            $volume = (float) str_replace(',', '', $m[1]);
            $satuan = trim($m[2]);
            return ['volume' => $volume, 'satuan' => $satuan];
        }

        // Just a number
        if (is_numeric($raw)) {
            return ['volume' => (float) $raw, 'satuan' => ''];
        }

        return ['volume' => 0, 'satuan' => $raw];
    }

    /**
     * Build kode MAK lengkap dari hierarchy
     */
    private static function buildKodeMAK(
        string $program,
        string $kegiatan,
        string $subKegiatan,
        string $output,
        string $input,
        string $subKomponen,
        string $akun
    ): string {
        $parts = [];
        if ($program) $parts[] = $program;
        if ($subKegiatan) {
            $parts[] = $subKegiatan;
        } elseif ($kegiatan) {
            $parts[] = $kegiatan;
        }
        if ($output) $parts[] = ltrim($output, $kegiatan . '.');
        if ($input) $parts[] = $input;
        if ($subKomponen) $parts[] = $subKomponen;
        if ($akun) $parts[] = $akun;

        return implode('.', $parts);
    }

    /**
     * Get bidang dari kode kegiatan
     */
    private static function getBidang(string $kodeKegiatan): string
    {
        return self::BIDANG_MAP[$kodeKegiatan] ?? 'IPDS';
    }
}
