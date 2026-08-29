<?php

namespace App\Traits;

use App\Models\AlokasiHonor;
use App\Models\Kegiatan;

trait HasBidangScope
{
    /**
     * Validate that the given kegiatan belongs to the operator's bidang.
     */
    protected function validateOperatorKegiatan(int $kegiatanId, $operator = null): bool
    {
        $operator = $operator ?? auth()->user();

        if ($operator->role === 'admin') return true;
        if (!$operator->bidang_id) return true;

        $kegiatan = Kegiatan::find($kegiatanId);
        if (!$kegiatan || $kegiatan->bidang_id != $operator->bidang_id) {
            return false;
        }

        return true;
    }

    /**
     * Validate that all alokasi records belong to the operator's bidang.
     */
    protected function validateOperatorAlokasi(array $alokasiIds, $operator = null): bool
    {
        $operator = $operator ?? auth()->user();

        if ($operator->role === 'admin') return true;
        if (!$operator->bidang_id) return true;

        $count = AlokasiHonor::whereIn('id', $alokasiIds)
            ->whereHas('kegiatan', fn($q) => $q->where('bidang_id', $operator->bidang_id))
            ->count();

        return $count === count($alokasiIds);
    }

    /**
     * Validate that a single alokasi record belongs to the operator's bidang.
     */
    protected function validateSingleAlokasi(int $alokasiId, $operator = null): bool
    {
        $operator = $operator ?? auth()->user();

        if ($operator->role === 'admin') return true;
        if (!$operator->bidang_id) return true;

        return AlokasiHonor::where('id', $alokasiId)
            ->whereHas('kegiatan', fn($q) => $q->where('bidang_id', $operator->bidang_id))
            ->exists();
    }

    /**
     * Validate that all mitra IDs have alokasi in the operator's bidang.
     */
    protected function validateOperatorMitra(array $mitraIds, $periodeIds, $operator = null): bool
    {
        $operator = $operator ?? auth()->user();

        if ($operator->role === 'admin') return true;
        if (!$operator->bidang_id) return true;

        $count = AlokasiHonor::whereIn('mitra_id', $mitraIds)
            ->whereIn('periode_id', $periodeIds)
            ->whereHas('kegiatan', fn($q) => $q->where('bidang_id', $operator->bidang_id))
            ->distinct('mitra_id')
            ->count('mitra_id');

        return $count === count($mitraIds);
    }

    /**
     * Return redirect with access denied message.
     */
    protected function bidangAccessDenied()
    {
        return redirect()->back()->with('error', 'Anda tidak memiliki izin untuk mengakses data di luar bidang Anda.');
    }
}
