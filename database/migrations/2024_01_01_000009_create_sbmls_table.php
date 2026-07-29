<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('sbmls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mitra_id')->constrained('mitras')->onDelete('cascade');
            $table->foreignId('periode_id')->constrained('periodes')->onDelete('cascade');
            $table->enum('jenis', ['Pencacahan', 'Pengolahan']);
            $table->decimal('nominal', 15, 2)->default(0);
            $table->unique(['mitra_id', 'periode_id', 'jenis']);
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('sbmls');
    }
};
