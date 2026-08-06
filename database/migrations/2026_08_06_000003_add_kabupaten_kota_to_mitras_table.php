<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('mitras', function (Blueprint $table) {
            $table->string('kabupaten_kota')->nullable()->default('Kabupaten Tasikmalaya')->after('desa');
        });
    }

    public function down(): void
    {
        Schema::table('mitras', function (Blueprint $table) {
            $table->dropColumn('kabupaten_kota');
        });
    }
};
