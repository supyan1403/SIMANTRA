<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('sbml_masters', function (Blueprint $table) {
            $table->decimal('nominal_pencacahan', 15, 2)->nullable()->default(0)->after('tahun');
            $table->decimal('nominal_pengolahan', 15, 2)->nullable()->default(0)->after('nominal_pencacahan');
        });
    }

    public function down(): void
    {
        Schema::table('sbml_masters', function (Blueprint $table) {
            $table->dropColumn(['nominal_pencacahan', 'nominal_pengolahan']);
        });
    }
};
