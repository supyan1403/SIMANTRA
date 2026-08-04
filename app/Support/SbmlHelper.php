<?php

namespace App\Support;

use App\Models\Sbml;
use App\Models\SbmlMaster;
use App\Models\Periode;

class SbmlHelper
{
    public const DEFAULT_LIMIT = 4500000;

    /**
     * Batas honor maksimal efektif untuk seorang mitra pada satu periode (bulan).
     * Prioritas: nilai import per mitra (sbmls) -> nilai Master per tahun -> default.
     */
    public static function limitFor(int $mitraId, int $periodeId): float
    {
        $imported = (float) Sbml::where('mitra_id', $mitraId)
            ->where('periode_id', $periodeId)
            ->sum('nominal');

        if ($imported > 0) {
            return $imported;
        }

        $periode = Periode::find($periodeId);
        if ($periode) {
            $master = SbmlMaster::where('tahun', $periode->tahun)->value('nominal');
            if ($master !== null && (float) $master > 0) {
                return (float) $master;
            }
        }

        return self::DEFAULT_LIMIT;
    }

    /**
     * Total honor seorang mitra pada satu periode.
     */
    public static function totalHonorFor(int $mitraId, int $periodeId): float
    {
        return (float) \App\Models\AlokasiHonor::where('mitra_id', $mitraId)
            ->where('periode_id', $periodeId)
            ->sum('nominal');
    }

    /**
     * Data peringatan untuk satu mitra+periode.
     */
    public static function evaluate(int $mitraId, int $periodeId): array
    {
        $total = self::totalHonorFor($mitraId, $periodeId);
        $limit = self::limitFor($mitraId, $periodeId);

        return [
            'total' => $total,
            'limit' => $limit,
            'exceeded' => $total > $limit,
            'excess' => max(0, $total - $limit),
        ];
    }
}