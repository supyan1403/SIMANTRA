<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('bidangs', function (Blueprint $table) {
            $table->id();
            $table->string('nama')->unique();
            $table->string('kode')->nullable();
            $table->string('tim_kerja')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('bidangs');
    }
};
