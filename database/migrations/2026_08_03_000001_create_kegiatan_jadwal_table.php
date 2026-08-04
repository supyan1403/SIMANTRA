<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kegiatan_jadwal', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kegiatan_id')->constrained('kegiatans')->onDelete('cascade');
            $table->tinyInteger('bulan_angka')->comment('1-12');
            $table->decimal('jumlah', 15, 2)->default(0)->comment('Volume/nominal terjadwal pada bulan tsb');
            $table->unique(['kegiatan_id', 'bulan_angka']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kegiatan_jadwal');
    }
};