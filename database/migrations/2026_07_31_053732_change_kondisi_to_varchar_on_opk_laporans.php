<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('opk_laporans', function (Blueprint $table) {
            $table->string('kondisi', 50)->default('baik')->change();
        });
    }

    public function down(): void
    {
        Schema::table('opk_laporans', function (Blueprint $table) {
            $table->enum('kondisi', ['baik', 'waspada', 'kritis'])->default('baik')->change();
        });
    }
};
