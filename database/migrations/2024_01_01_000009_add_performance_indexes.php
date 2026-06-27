<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. ai_urgency_score — menghilangkan filesort pada antrian verifikasi & scope prioritas
        Schema::table('opk_laporans', function (Blueprint $table) {
            $table->index('ai_urgency_score', 'idx_ai_urgency_score');
        });

        // 2. nama_opk — menghilangkan filesort pada daftar OPK (sort by name) & autocomplete suggest
        Schema::table('opk_laporans', function (Blueprint $table) {
            $table->index('nama_opk', 'idx_nama_opk');
        });

        // 3. users.role — menghilangkan full table scan pada manajemen pengguna
        Schema::table('users', function (Blueprint $table) {
            $table->index('role', 'idx_users_role');
        });

        // 4. opk_riwayat_status — menghilangkan filesort pada setiap halaman detail/show
        Schema::table('opk_riwayat_status', function (Blueprint $table) {
            $table->index(['laporan_id', 'created_at'], 'idx_riwayat_laporan_created');
        });

        // 5 & 6. opk_fotos — mempercepat fotoUtama (is_utama=true) dan fotos (ORDER BY urutan)
        Schema::table('opk_fotos', function (Blueprint $table) {
            $table->index(['laporan_id', 'is_utama', 'urutan'], 'idx_fotos_laporan_utama_urutan');
        });

        // 7. desa_dinas — menghilangkan filesort pada dropdown AJAX
        Schema::table('desa_dinas', function (Blueprint $table) {
            $table->index(['kecamatan_id', 'nama'], 'idx_desa_dinas_kec_nama');
        });

        // 8. desa_adats — menghilangkan filesort pada dropdown AJAX
        Schema::table('desa_adats', function (Blueprint $table) {
            $table->index(['kecamatan_id', 'nama'], 'idx_desa_adat_kec_nama');
        });
    }

    public function down(): void
    {
        Schema::table('opk_laporans', function (Blueprint $table) {
            $table->dropIndex('idx_ai_urgency_score');
            $table->dropIndex('idx_nama_opk');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('idx_users_role');
        });

        Schema::table('opk_riwayat_status', function (Blueprint $table) {
            $table->dropIndex('idx_riwayat_laporan_created');
        });

        Schema::table('opk_fotos', function (Blueprint $table) {
            $table->dropIndex('idx_fotos_laporan_utama_urutan');
        });

        Schema::table('desa_dinas', function (Blueprint $table) {
            $table->dropIndex('idx_desa_dinas_kec_nama');
        });

        Schema::table('desa_adats', function (Blueprint $table) {
            $table->dropIndex('idx_desa_adat_kec_nama');
        });
    }
};
