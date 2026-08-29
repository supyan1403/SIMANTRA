<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('alokasi_honors', function (Blueprint $table) {
            if (!Schema::hasColumn('alokasi_honors', 'volume')) {
                $table->decimal('volume', 10, 2)->default(1)->after('nominal');
            }
            if (!Schema::hasColumn('alokasi_honors', 'satuan')) {
                $table->string('satuan', 50)->default('dokumen')->after('volume');
            }
            if (!Schema::hasColumn('alokasi_honors', 'tarif_satuan')) {
                $table->decimal('tarif_satuan', 15, 2)->nullable()->after('satuan');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('alokasi_honors', function (Blueprint $table) {
            $table->dropColumn(['volume', 'satuan', 'tarif_satuan']);
        });
    }
};
