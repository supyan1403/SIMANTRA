<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('mitras', function (Blueprint $table) {
            $table->string('kecamatan')->nullable()->after('no_hp');
            $table->string('desa')->nullable()->after('kecamatan');
            $table->text('alamat_detail')->nullable()->after('desa');
            $table->string('alamat')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('mitras', function (Blueprint $table) {
            $table->dropColumn(['kecamatan', 'desa', 'alamat_detail']);
        });
    }
};
