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
            if (!Schema::hasColumn('alokasi_honors', 'tanggal_spk')) {
                $table->date('tanggal_spk')->nullable()->after('nomor_spk');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('alokasi_honors', function (Blueprint $table) {
            if (Schema::hasColumn('alokasi_honors', 'tanggal_spk')) {
                $table->dropColumn('tanggal_spk');
            }
        });
    }
};
