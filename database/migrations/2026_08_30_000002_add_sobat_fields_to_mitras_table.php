<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('mitras', function (Blueprint $table) {
            $table->string('nik', 30)->nullable()->after('id_sobat');
            $table->string('posisi', 100)->nullable()->after('nama');
            $table->string('email', 150)->nullable()->after('no_hp');
            $table->string('npwp', 50)->nullable()->after('email');
            $table->string('tanggal_lahir', 50)->nullable()->after('npwp');
            $table->string('agama', 50)->nullable()->after('jk');
            $table->string('status_perkawinan', 50)->nullable()->after('agama');
            $table->string('pendidikan', 100)->nullable()->after('status_perkawinan');
            $table->string('posisi_daftar', 100)->nullable()->after('posisi');
            $table->string('status_seleksi', 50)->nullable()->after('posisi_daftar');
            $table->decimal('nilai_ujian', 6, 2)->nullable()->after('status_seleksi');
            $table->boolean('exp_sp')->default(false)->after('alamat_detail');
            $table->boolean('exp_st')->default(false)->after('exp_sp');
            $table->boolean('exp_se')->default(false)->after('exp_st');
            $table->boolean('exp_susenas')->default(false)->after('exp_se');
            $table->boolean('exp_sakernas')->default(false)->after('exp_susenas');
            $table->boolean('exp_sbh')->default(false)->after('exp_sakernas');
            $table->text('catatan_mitra')->nullable()->after('exp_sbh');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mitras', function (Blueprint $table) {
            $table->dropColumn([
                'nik',
                'posisi',
                'email',
                'npwp',
                'tanggal_lahir',
                'agama',
                'status_perkawinan',
                'pendidikan',
                'posisi_daftar',
                'status_seleksi',
                'nilai_ujian',
                'exp_sp',
                'exp_st',
                'exp_se',
                'exp_susenas',
                'exp_sakernas',
                'exp_sbh',
                'catatan_mitra',
            ]);
        });
    }
};
