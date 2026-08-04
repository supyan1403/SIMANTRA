<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;
use App\Models\Mitra;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Skip auto-import during unit testing environment to keep tests ultra fast
        if (app()->environment('testing')) {
            return;
        }

        // Automatically seed database on php artisan migrate if no mitra data exists
        if (Mitra::count() === 0) {
            try {
                Artisan::call('mantra:import');
            } catch (\Throwable $e) {}
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
