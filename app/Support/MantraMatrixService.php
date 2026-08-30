<?php

namespace App\Support;

use App\Models\AlokasiHonor;
use App\Models\Kegiatan;
use App\Models\Mitra;
use App\Models\Periode;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class MantraMatrixService
{
    /**
     * Unduh template matriks MANTRA master bersih
     */
    public static function downloadBlankTemplate()
    {
        $templatePath = storage_path('app/templates/template_mantra_master.xlsx');
        if (!file_exists($templatePath)) {
            // Fallback ke file referensi asli jika ada
            $rawRef = base_path('1. Input MANTRA (Monitoring Alokasi Pekerjaan Dan Honor Mitra) oleh PJ Kegiatan atau Ketua Tim - Update3.xlsx.xlsx');
            if (file_exists($rawRef)) {
                $templatePath = $rawRef;
            }
        }

        if (!file_exists($templatePath)) {
            abort(404, 'Template Master MANTRA tidak ditemukan di server.');
        }

        $filename = 'Template_MANTRA_Matriks_MultiSheet_Siap_Isi.xlsx';

        return response()->download($templatePath, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * Export data alokasi MANTRA terisi penuh untuk tahun tertentu
     */
    public static function exportFilledMatrix(int $tahun)
    {
        $templatePath = storage_path('app/templates/template_mantra_master.xlsx');
        if (!file_exists($templatePath)) {
            $templatePath = base_path('1. Input MANTRA (Monitoring Alokasi Pekerjaan Dan Honor Mitra) oleh PJ Kegiatan atau Ketua Tim - Update3.xlsx.xlsx');
        }

        $periodes = Periode::where('tahun', $tahun)->get();
        $periodeIds = $periodes->pluck('id');

        $allocations = [];
        $allHonors = AlokasiHonor::with(['mitra', 'kegiatan', 'periode'])
            ->whereIn('periode_id', $periodeIds)
            ->get();

        foreach ($allHonors as $ah) {
            $mNum = (int)$ah->periode->bulan_angka;
            if (!isset($allocations[$mNum])) {
                $allocations[$mNum] = [];
            }
            $allocations[$mNum][] = [
                'mitra_nama' => $ah->mitra->nama ?? '',
                'kegiatan_nama' => $ah->kegiatan->nama ?? '',
                'nominal' => (float)$ah->nominal,
                'volume' => (float)($ah->volume ?? 1),
            ];
        }

        $payload = [
            'mode' => 'filled',
            'tahun' => $tahun,
            'allocations' => $allocations,
        ];

        $tempDir = storage_path('app/temp_mantra');
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0777, true);
        }

        $uid = uniqid('mantra_');
        $jsonPath = $tempDir . DIRECTORY_SEPARATOR . $uid . '.json';
        $outputPath = $tempDir . DIRECTORY_SEPARATOR . $uid . '.xlsx';

        file_put_contents($jsonPath, json_encode($payload));

        $pyScript = base_path('scripts/export_mantra_matrix.py');
        exec("python \"{$pyScript}\" \"{$templatePath}\" \"{$outputPath}\" \"{$jsonPath}\"");

        @unlink($jsonPath);

        if (file_exists($outputPath)) {
            $downloadFilename = "Matriks_Alokasi_MANTRA_Tahun_{$tahun}.xlsx";
            return response()->download($outputPath, $downloadFilename, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ])->deleteFileAfterSend(true);
        }

        // Fallback jika python gagal
        return response()->download($templatePath, "Template_MANTRA_Tahun_{$tahun}.xlsx");
    }
}
