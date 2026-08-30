<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('posisi_mitras', function (Blueprint $table) {
            $table->id();
            $table->string('nama')->unique();
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });

        // Seed dengan data posisi awal yang ada di database mitra
        $initialPositions = [
            'Mitra (Pendataan dan Pengolahan)' => 'Mitra yang bertugas dalam pendataan dan pengolahan sensus/survei',
            'Mitra Pendataan' => 'Mitra pendata lapangan (PCL/PML)',
            'Mitra Pengolahan' => 'Mitra pengolah data / operator entri',
            'Pendata Lapangan' => 'Petugas pencacah / pendata lapangan',
            'Pemeriksa Lapangan' => 'Petugas pengawas / pemeriksa lapangan',
            'Pengolah Data' => 'Petugas entri dan validasi dokumen',
        ];

        $now = now();
        foreach ($initialPositions as $nama => $ket) {
            DB::table('posisi_mitras')->insertOrIgnore([
                'nama' => $nama,
                'keterangan' => $ket,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // Ambil juga posisi unik yang mungkin sudah ada di tabel mitras
        if (Schema::hasTable('mitras')) {
            $existing = DB::table('mitras')
                ->whereNotNull('posisi')
                ->where('posisi', '!=', '')
                ->distinct()
                ->pluck('posisi');

            foreach ($existing as $pos) {
                DB::table('posisi_mitras')->insertOrIgnore([
                    'nama' => trim($pos),
                    'keterangan' => 'Master posisi mitra',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posisi_mitras');
    }
};
