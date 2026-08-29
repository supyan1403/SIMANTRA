<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpkCounter extends Model
{
    protected $fillable = [
        'format_pattern',
        'tahun',
        'last_number',
    ];

    /**
     * Get next number for a given format pattern + tahun.
     * Creates record if not exists.
     */
    public static function getNextNumber(string $formatPattern, string $tahun): int
    {
        $counter = static::firstOrCreate(
            ['format_pattern' => $formatPattern, 'tahun' => $tahun],
            ['last_number' => 0]
        );

        return $counter->last_number + 1;
    }

    /**
     * Increment counter after generating numbers.
     */
    public static function incrementTo(string $formatPattern, string $tahun, int $newLast): void
    {
        static::updateOrCreate(
            ['format_pattern' => $formatPattern, 'tahun' => $tahun],
            ['last_number' => $newLast]
        );
    }
}
