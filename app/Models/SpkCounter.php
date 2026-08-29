<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpkCounter extends Model
{
    protected $fillable = [
        'format_pattern',
        'jenis_dokumen',
        'tahun',
        'last_number',
    ];

    /**
     * Get next number for a given format pattern + jenis dokumen + tahun.
     */
    public static function getNextNumber(string $formatPattern, string $jenisDokumen, string $tahun): int
    {
        $counter = static::firstOrCreate(
            ['format_pattern' => $formatPattern, 'jenis_dokumen' => $jenisDokumen, 'tahun' => $tahun],
            ['last_number' => 0]
        );

        return $counter->last_number + 1;
    }

    /**
     * Update counter after generating numbers.
     */
    public static function incrementTo(string $formatPattern, string $jenisDokumen, string $tahun, int $newLast): void
    {
        static::updateOrCreate(
            ['format_pattern' => $formatPattern, 'jenis_dokumen' => $jenisDokumen, 'tahun' => $tahun],
            ['last_number' => $newLast]
        );
    }
}
