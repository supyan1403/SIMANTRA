<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('spk_counters', function (Blueprint $table) {
            $table->unsignedBigInteger('kegiatan_id')->nullable()->after('id');
        });

        // Drop old unique constraint
        Schema::dropIfExists('spk_counters');
        Schema::create('spk_counters', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('kegiatan_id');
            $table->string('jenis_dokumen')->default('spk');
            $table->string('tahun');
            $table->unsignedInteger('last_number')->default(0);
            $table->timestamps();

            $table->unique(['kegiatan_id', 'jenis_dokumen', 'tahun']);
            $table->foreign('kegiatan_id')->references('id')->on('kegiatans')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('spk_counters');
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
};
