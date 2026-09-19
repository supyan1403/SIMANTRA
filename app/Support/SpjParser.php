<?php

namespace App\Support;

use Smalot\PdfParser\Parser;

class SpjParser
{
    public static function parse(string $pdfPath): array
    {
        $parser = new Parser();
        $pdf = $parser->parseFile($pdfPath);
        $text = $pdf->getText();

        $result = [
            'header' => self::parseHeader($text),
            'items' => self::parseTable($text),
            'total' => self::parseTotal($text),
        ];

        return $result;
    }

    protected static function parseHeader(string $text): array
    {
        $header = [];
        $patterns = [
            'program' => '/Program\s*:\s*(.+)/i',
            'kegiatan' => '/Kegiatan\s*:\s*(.+)/i',
            'output' => '/Output\s*:\s*(.+)/i',
            'sub_output' => '/Sub\s*Output\s*:\s*(.+)/i',
            'komponen' => '/Komponen\s*:\s*(?!Sub)(.+)/i',
            'sub_komponen' => '/Sub\s*Komponen\s*:\s*(.+)/i',
            'akun' => '/Akun\s*:\s*(.+)/i',
        ];
        foreach ($patterns as $key => $pattern) {
            if (preg_match($pattern, $text, $m)) {
                $header[$key] = trim($m[1]);
            }
        }
        return $header;
    }

    protected static function parseTable(string $text): array
    {
        $lines = explode("\n", $text);
        $items = [];

        $tableStart = -1;
        for ($i = 0; $i < count($lines); $i++) {
            $t = trim($lines[$i]);
            if (preg_match('/^No\s+Nama/i', $t)) {
                $tableStart = $i;
                break;
            }
        }
        if ($tableStart === -1) return [];

        $rawRows = [];
        $currentRow = null;

        for ($i = $tableStart + 1; $i < count($lines); $i++) {
            $t = trim($lines[$i]);
            if ($t === '') continue;
            if (preg_match('/^Jumlah\s/i', $t)) break;
            if (preg_match('/^Setuju\s/i', $t)) break;
            if (preg_match('/^Pejabat\s/i', $t)) break;
            if (preg_match('/^Bendahara\s/i', $t)) break;

            if (preg_match('/^(\d+)\s+/', $t, $m)) {
                $num = (int) $m[1];
                if ($currentRow !== null && $currentRow['no'] === $num) {
                    $currentRow['text'] .= ' ' . $t;
                } else {
                    if ($currentRow !== null) $rawRows[] = $currentRow;
                    $currentRow = ['no' => $num, 'text' => $t];
                }
            } elseif ($currentRow !== null) {
                $currentRow['text'] .= ' ' . $t;
            }
        }
        if ($currentRow !== null) $rawRows[] = $currentRow;

        foreach ($rawRows as $row) {
            $item = self::parseRow($row['text'], $row['no']);
            if ($item !== null) {
                $items[] = $item;
            }
        }

        return $items;
    }

    protected static function parseRow(string $text, int $expectedNo): ?array
    {
        $text = trim($text);

        $pattern = '/^' . $expectedNo . '\s+(.+?)\s+Petugas\s+Pendataan\s+Lapangan\s*\(?\s*PPL\s*(?:Survei)?\s*\)?\s+(\d[\d.,]*)\s+(\d+)\s+(\d[\d.,]*)\s+(\d+)\s+(\d[\d.,]*)\s*\(0/i';

        if (preg_match($pattern, $text, $m)) {
            return [
                'no' => $expectedNo,
                'nama' => trim($m[1]),
                'jabatan' => 'Petugas Pendataan Lapangan (PPL Survei)',
                'biaya_satuan' => self::parseNumber($m[2]),
                'volume' => (int) $m[3],
                'bruto' => self::parseNumber($m[4]),
                'pajak' => self::parseNumber($m[5]),
                'netto' => self::parseNumber($m[6]),
            ];
        }

        $pattern2 = '/^' . $expectedNo . '\s+(.+?)\s+(\d[\d.,]*)\s+(\d+)\s+(\d[\d.,]*)\s+(\d+)\s+(\d[\d.,]*)\s*\(0/i';
        if (preg_match($pattern2, $text, $m)) {
            return [
                'no' => $expectedNo,
                'nama' => trim($m[1]),
                'jabatan' => 'Petugas Pendataan Lapangan (PPL Survei)',
                'biaya_satuan' => self::parseNumber($m[2]),
                'volume' => (int) $m[3],
                'bruto' => self::parseNumber($m[4]),
                'pajak' => self::parseNumber($m[5]),
                'netto' => self::parseNumber($m[6]),
            ];
        }

        $pattern3 = '/^' . $expectedNo . '\s+(.+?)\s+(\d[\d.,]*)\s+(\d+)\s+(\d[\d.,]*)\s+(\d+)\s+(\d[\d.,]*)/';
        if (preg_match($pattern3, $text, $m)) {
            $nama = trim($m[1]);
            if (strlen($nama) < 2) return null;
            return [
                'no' => $expectedNo,
                'nama' => $nama,
                'jabatan' => 'Petugas Pendataan Lapangan (PPL Survei)',
                'biaya_satuan' => self::parseNumber($m[2]),
                'volume' => (int) $m[3],
                'bruto' => self::parseNumber($m[4]),
                'pajak' => self::parseNumber($m[5]),
                'netto' => self::parseNumber($m[6]),
            ];
        }

        return null;
    }

    protected static function parseTotal(string $text): array
    {
        if (preg_match('/Jumlah\s+([\d.,]+)\s+(\d+)\s+([\d.,]+)/i', $text, $m)) {
            return [
                'bruto' => self::parseNumber($m[1]),
                'pajak' => self::parseNumber($m[2]),
                'netto' => self::parseNumber($m[3]),
            ];
        }
        return ['bruto' => 0, 'pajak' => 0, 'netto' => 0];
    }

    protected static function parseNumber(string $str): float
    {
        $clean = preg_replace('/[^\d.,]/', '', $str);
        $clean = str_replace('.', '', $clean);
        $clean = str_replace(',', '.', $clean);
        return (float) $clean;
    }
}
