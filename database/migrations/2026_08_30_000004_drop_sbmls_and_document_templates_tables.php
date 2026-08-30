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
        // Hanya hapus tabel sbmls yang redundan
        Schema::dropIfExists('sbmls');

        // Pastikan tabel document_templates tetap ada
        if (!Schema::hasTable('document_templates')) {
            Schema::create('document_templates', function (Blueprint $table) {
                $table->id();
                $table->string('nama');
                $table->enum('jenis_dokumen', ['spk', 'bast'])->default('spk');
                $table->enum('kategori_kegiatan', ['sensus', 'survei', 'umum'])->default('umum');
                $table->string('file_path')->nullable();
                $table->text('deskripsi')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // Isi dengan 3 template standar resmi sistem
        $defaultTemplates = [
            [
                'nama' => 'Template SPK (Surat Perintah Kerja) Standar BPS',
                'jenis_dokumen' => 'spk',
                'kategori_kegiatan' => 'umum',
                'deskripsi' => 'Format baku Surat Perintah / Perjanjian Kerja Mitra Statistik BPS',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'Template BAST (Berita Acara Serah Terima) Standar BPS',
                'jenis_dokumen' => 'bast',
                'kategori_kegiatan' => 'umum',
                'deskripsi' => 'Format baku Berita Acara Serah Terima Hasil Pekerjaan Mitra BPS',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'Template Lampiran Rincian Honor Mitra',
                'jenis_dokumen' => 'spk',
                'kategori_kegiatan' => 'umum',
                'deskripsi' => 'Format lampiran rincian honor dan daftar alokasi kegiatan mitra',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($defaultTemplates as $dt) {
            DB::table('document_templates')->insertOrIgnore($dt);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }
};
