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

        // Automatically seed/update database with MANTRA data on php artisan migrate
        try {
            Artisan::call('db:seed', ['--force' => true]);
        } catch (\Throwable $e) {}
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
