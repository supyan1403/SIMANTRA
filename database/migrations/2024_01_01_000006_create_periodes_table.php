<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('periodes', function (Blueprint $table) {
            $table->id();
            $table->string('tahun');
            $table->string('bulan');
            $table->integer('bulan_angka');
            $table->unique(['tahun', 'bulan']);
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('periodes');
    }
};
