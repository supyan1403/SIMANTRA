<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('spk_counters', function (Blueprint $table) {
            $table->id();
            $table->string('format_pattern');
            $table->string('jenis_dokumen')->default('spk');
            $table->string('tahun');
            $table->unsignedInteger('last_number')->default(0);
            $table->timestamps();

            $table->unique(['format_pattern', 'jenis_dokumen', 'tahun']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('spk_counters');
    }
};
