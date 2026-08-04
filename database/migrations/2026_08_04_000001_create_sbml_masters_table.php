<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('sbml_masters', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('tahun')->unique();
            $table->decimal('nominal', 15, 2)->default(4500000);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sbml_masters');
    }
};