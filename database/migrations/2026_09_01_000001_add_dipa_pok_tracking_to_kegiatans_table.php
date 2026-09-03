<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kegiatans', function (Blueprint $table) {
            $table->string('source_file')->nullable()->after('tgl_selesai');
            $table->string('revisi_ke')->nullable()->after('source_file');
            $table->string('jenis_dokumen')->nullable()->after('revisi_ke');
        });
    }

    public function down(): void
    {
        Schema::table('kegiatans', function (Blueprint $table) {
            $table->dropColumn(['source_file', 'revisi_ke', 'jenis_dokumen']);
        });
    }
};
