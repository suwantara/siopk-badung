<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('opk_laporans', function (Blueprint $table) {
            $table->index(['status_verifikasi', 'kondisi'], 'idx_verifikasi_kondisi');
            $table->index(['status_verifikasi', 'ai_urgency_score'], 'idx_verifikasi_urgensi');
            $table->index(['latitude', 'longitude'], 'idx_lat_lng');
        });
    }

    public function down(): void
    {
        Schema::table('opk_laporans', function (Blueprint $table) {
            $table->dropIndex('idx_verifikasi_kondisi');
            $table->dropIndex('idx_verifikasi_urgensi');
            $table->dropIndex('idx_lat_lng');
        });
    }
};
