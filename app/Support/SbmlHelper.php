<?php

namespace App\Support;

use App\Models\Sbml;
use App\Models\SbmlMaster;
use App\Models\Periode;
use App\Models\AlokasiHonor;
use App\Models\Kegiatan;

class SbmlHelper
{
    // Batas Standar Default Real BPS 2024 (File Excel MANTRA Asli)
    public const DEFAULT_PENCACAHAN_2024 = 3326000;
    public const DEFAULT_PENGOLAHAN_2024 = 3077000;
    public const DEFAULT_TOTAL_2024 = 6403000;

    // Batas Standar Default SBM 2025
    public const DEFAULT_PENCACAHAN_2025 = 4500000;
    public const DEFAULT_PENGOLAHAN_2025 = 3000000;
    public const DEFAULT_TOTAL_2025 = 7500000;

    // Fallback umum sistem
    public const DEFAULT_LIMIT = 4500000;

    /**
     * Batas honor maksimal efektif untuk seorang mitra pada satu periode (bulan).
     * @param int $mitraId
     * @param int $periodeId
     * @param string|null $jenis 'Pencacahan', 'Pengolahan', atau null (Total Gabungan)
     */
    public static function limitFor(int $mitraId, int $periodeId, ?string $jenis = null): float
    {
        $query = Sbml::where('mitra_id', $mitraId)->where('periode_id', $periodeId);
        
        if ($jenis !== null && in_array($jenis, ['Pencacahan', 'Pengolahan'])) {
            $imported = (float) $query->where('jenis', $jenis)->value('nominal');
            if ($imported > 0) {
                return $imported;
            }
        } else {
            $importedTotal = (float) $query->sum('nominal');
            if ($importedTotal > 0) {
                return $importedTotal;
            }
        }

        // Cek pengaturan Master SBML per tahun
        $periode = Periode::find($periodeId);
        $tahun = $periode ? (int) $periode->tahun : (int) date('Y');

        if ($periode) {
            $master = SbmlMaster::where('tahun', $periode->tahun)->first();
            if ($master) {
                if ($jenis === 'Pencacahan' && (float) $master->nominal_pencacahan > 0) {
                    return (float) $master->nominal_pencacahan;
                }
                if ($jenis === 'Pengolahan' && (float) $master->nominal_pengolahan > 0) {
                    return (float) $master->nominal_pengolahan;
                }
                if ($jenis === null && (float) $master->nominal > 0) {
                    return (float) $master->nominal;
                }
            }
        }

        // Fallback default per tahun & per jenis
        if ($tahun === 2024) {
            if ($jenis === 'Pencacahan') return self::DEFAULT_PENCACAHAN_2024;
            if ($jenis === 'Pengolahan') return self::DEFAULT_PENGOLAHAN_2024;
            return self::DEFAULT_TOTAL_2024;
        }

        if ($tahun >= 2025) {
            if ($jenis === 'Pencacahan') return self::DEFAULT_PENCACAHAN_2025;
            if ($jenis === 'Pengolahan') return self::DEFAULT_PENGOLAHAN_2025;
            return self::DEFAULT_TOTAL_2025;
        }

        return self::DEFAULT_LIMIT;
    }

    /**
     * Total honor seorang mitra pada satu periode (bulan).
     * @param int $mitraId
     * @param int $periodeId
     * @param string|null $jenis 'Pencacahan', 'Pengolahan', atau null (Total Gabungan)
     */
    public static function totalHonorFor(int $mitraId, int $periodeId, ?string $jenis = null): float
    {
        $query = AlokasiHonor::where('mitra_id', $mitraId)->where('periode_id', $periodeId);

        if ($jenis !== null && in_array($jenis, ['Pencacahan', 'Pengolahan'])) {
            $alokasis = $query->with('kegiatan')->get();
            return (float) $alokasis->filter(function ($a) use ($jenis) {
                return $a->kegiatan && $a->kegiatan->jenis_tugas === $jenis;
            })->sum('nominal');
        }

        return (float) $query->sum('nominal');
    }

    /**
     * Data evaluasi peringatan untuk satu mitra + periode.
     * Mengembalikan struktur array yang 100% kompatibel dengan view eksisting.
     */
    public static function evaluate(int $mitraId, int $periodeId, ?int $kegiatanId = null): array
    {
        $total = self::totalHonorFor($mitraId, $periodeId);
        $limit = self::limitFor($mitraId, $periodeId);

        $totalPencacahan = self::totalHonorFor($mitraId, $periodeId, 'Pencacahan');
        $limitPencacahan = self::limitFor($mitraId, $periodeId, 'Pencacahan');

        $totalPengolahan = self::totalHonorFor($mitraId, $periodeId, 'Pengolahan');
        $limitPengolahan = self::limitFor($mitraId, $periodeId, 'Pengolahan');

        $exceededPencacahan = $totalPencacahan > $limitPencacahan;
        $exceededPengolahan = $totalPengolahan > $limitPengolahan;
        $exceededTotal = $total > $limit;
        $isExceeded = $exceededTotal || $exceededPencacahan || $exceededPengolahan;

        $excessPencacahan = max(0, $totalPencacahan - $limitPencacahan);
        $excessPengolahan = max(0, $totalPengolahan - $limitPengolahan);
        $excessTotal = max(0, $total - $limit);
        $maxExcess = max($excessTotal, $excessPencacahan, $excessPengolahan);

        $activeLimit = $limit;
        $activeTotal = $total;
        $categoryLabel = 'SBML';

        if ($exceededPencacahan && ($excessPencacahan >= $excessTotal && $excessPencacahan >= $excessPengolahan)) {
            $activeLimit = $limitPencacahan;
            $activeTotal = $totalPencacahan;
            $categoryLabel = 'Pencacahan';
        } elseif ($exceededPengolahan && ($excessPengolahan >= $excessTotal && $excessPengolahan >= $excessPencacahan)) {
            $activeLimit = $limitPengolahan;
            $activeTotal = $totalPengolahan;
            $categoryLabel = 'Pengolahan';
        }

        // Pesan peringatan kontekstual spesifik
        $warningReason = null;
        if ($exceededPencacahan) {
            $warningReason = "Honor Pencacahan Lapangan (Rp " . number_format($totalPencacahan, 0, ',', '.') . ") melebihi batas SBML Pencacahan (Rp " . number_format($limitPencacahan, 0, ',', '.') . ") lebih Rp " . number_format($excessPencacahan, 0, ',', '.');
        } elseif ($exceededPengolahan) {
            $warningReason = "Honor Pengolahan Data (Rp " . number_format($totalPengolahan, 0, ',', '.') . ") melebihi batas SBML Pengolahan (Rp " . number_format($limitPengolahan, 0, ',', '.') . ") lebih Rp " . number_format($excessPengolahan, 0, ',', '.');
        } elseif ($exceededTotal) {
            $warningReason = "Total Honor Bulanan (Rp " . number_format($total, 0, ',', '.') . ") melebihi batas SBML Gabungan (Rp " . number_format($limit, 0, ',', '.') . ") lebih Rp " . number_format($excessTotal, 0, ',', '.');
        }

        return [
            'total' => $total,
            'limit' => $limit,
            'active_total' => $activeTotal,
            'active_limit' => $activeLimit,
            'category_label' => $categoryLabel,
            'exceeded' => $isExceeded,
            'excess' => $maxExcess,
            'total_pencacahan' => $totalPencacahan,
            'limit_pencacahan' => $limitPencacahan,
            'total_pengolahan' => $totalPengolahan,
            'limit_pengolahan' => $limitPengolahan,
            'warning_reason' => $warningReason,
        ];
    }
}