<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpkCounter extends Model
{
    protected $fillable = [
        'kegiatan_id',
        'jenis_dokumen',
        'tahun',
        'last_number',
    ];

    /**
     * Get next number for a given kegiatan + jenis dokumen + tahun.
     */
    public static function getNextNumber(int $kegiatanId, string $jenisDokumen, string $tahun): int
    {
        $counter = static::firstOrCreate(
            ['kegiatan_id' => $kegiatanId, 'jenis_dokumen' => $jenisDokumen, 'tahun' => $tahun],
            ['last_number' => 0]
        );

        return $counter->last_number + 1;
    }

    /**
     * Update counter after generating numbers.
     */
    public static function incrementTo(int $kegiatanId, string $jenisDokumen, string $tahun, int $newLast): void
    {
        static::updateOrCreate(
            ['kegiatan_id' => $kegiatanId, 'jenis_dokumen' => $jenisDokumen, 'tahun' => $tahun],
            ['last_number' => $newLast]
        );
    }
}
