<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('bidang_id')->nullable()->constrained('bidangs')->onDelete('set null');
        });

        Schema::table('kegiatans', function (Blueprint $table) {
            $table->string('tahun')->nullable();
            $table->integer('jumlah')->default(0);
            $table->string('satuan')->nullable();
            $table->decimal('harga', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            $table->date('tgl_mulai')->nullable();
            $table->date('tgl_selesai')->nullable();
        });
    }

    public function down(): void {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['bidang_id']);
            $table->dropColumn('bidang_id');
        });

        Schema::table('kegiatans', function (Blueprint $table) {
            $table->dropColumn(['tahun', 'jumlah', 'satuan', 'harga', 'total', 'tgl_mulai', 'tgl_selesai']);
        });
    }
};
