<?php

namespace App\Services;

use App\Models\{OpkLaporan, OpkCategory, Kecamatan};
use Illuminate\Support\Facades\DB;

class OpkStatsService
{
    public function dashboardAdmin(): array
    {
        $row = DB::table('opk_laporans')
            ->selectRaw("
                SUM(CASE WHEN status_verifikasi = 'disetujui' THEN 1 ELSE 0 END) as total_opk,
                SUM(CASE WHEN status_verifikasi = 'disetujui' AND kondisi = 'kritis' THEN 1 ELSE 0 END) as kritis,
                SUM(CASE WHEN status_verifikasi IN ('menunggu', 'review_dinas') THEN 1 ELSE 0 END) as menunggu,
                SUM(CASE WHEN status_verifikasi = 'disetujui' AND kondisi = 'baik' THEN 1 ELSE 0 END) as terlindungi,
                SUM(CASE WHEN MONTH(created_at) = MONTH(NOW()) AND YEAR(created_at) = YEAR(NOW()) THEN 1 ELSE 0 END) as bulan_ini
            ")
            ->whereNull('deleted_at')
            ->first();

        return [
            'total_opk'   => (int) ($row->total_opk ?? 0),
            'kritis'      => (int) ($row->kritis ?? 0),
            'menunggu'    => (int) ($row->menunggu ?? 0),
            'terlindungi' => (int) ($row->terlindungi ?? 0),
            'bulan_ini'   => (int) ($row->bulan_ini ?? 0),
        ];
    }

    public function dashboardPublik(): array
    {
        $row = OpkLaporan::selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN kondisi = 'kritis'  THEN 1 ELSE 0 END) as kritis,
                SUM(CASE WHEN kondisi = 'waspada' THEN 1 ELSE 0 END) as waspada,
                SUM(CASE WHEN kondisi = 'baik'    THEN 1 ELSE 0 END) as baik
            ")
            ->disetujui()
            ->first();

        return [
            'total'   => (int) ($row->total ?? 0),
            'kritis'  => (int) ($row->kritis ?? 0),
            'waspada' => (int) ($row->waspada ?? 0),
            'baik'    => (int) ($row->baik ?? 0),
        ];
    }

    public function laporanAdmin(): array
    {
        $row = DB::table('opk_laporans')
            ->selectRaw("
                SUM(CASE WHEN status_verifikasi = 'disetujui' THEN 1 ELSE 0 END) as disetujui,
                SUM(CASE WHEN status_verifikasi = 'disetujui' AND kondisi = 'kritis' THEN 1 ELSE 0 END) as kritis,
                SUM(CASE WHEN status_verifikasi = 'disetujui' AND kondisi = 'waspada' THEN 1 ELSE 0 END) as waspada,
                SUM(CASE WHEN status_verifikasi = 'disetujui' AND kondisi = 'baik' THEN 1 ELSE 0 END) as baik,
                SUM(CASE WHEN status_verifikasi = 'ditolak' THEN 1 ELSE 0 END) as ditolak,
                SUM(CASE WHEN status_verifikasi IN ('menunggu', 'review_dinas') THEN 1 ELSE 0 END) as menunggu,
                SUM(CASE WHEN MONTH(created_at) = MONTH(NOW()) AND YEAR(created_at) = YEAR(NOW()) THEN 1 ELSE 0 END) as bulan_ini
            ")
            ->whereNull('deleted_at')
            ->first();

        return [
            'total'     => (int) ($row->disetujui ?? 0),
            'kritis'    => (int) ($row->kritis ?? 0),
            'waspada'   => (int) ($row->waspada ?? 0),
            'baik'      => (int) ($row->baik ?? 0),
            'disetujui' => (int) ($row->disetujui ?? 0),
            'ditolak'   => (int) ($row->ditolak ?? 0),
            'menunggu'  => (int) ($row->menunggu ?? 0),
            'bulan_ini' => (int) ($row->bulan_ini ?? 0),
        ];
    }

    public function ringkasanEksekutif(): array
    {
        $row = DB::table('opk_laporans')
            ->selectRaw("
                SUM(CASE WHEN status_verifikasi = 'disetujui' THEN 1 ELSE 0 END) as total_opk,
                SUM(CASE WHEN status_verifikasi = 'disetujui' AND kondisi = 'kritis' THEN 1 ELSE 0 END) as kritis,
                SUM(CASE WHEN status_verifikasi = 'disetujui' AND kondisi = 'waspada' THEN 1 ELSE 0 END) as waspada,
                SUM(CASE WHEN status_verifikasi = 'disetujui' AND ai_urgency_score >= 7 THEN 1 ELSE 0 END) as prioritas_tinggi,
                SUM(CASE WHEN status_verifikasi = 'disetujui' AND DATE(updated_at) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) THEN 1 ELSE 0 END) as disetujui_7hari,
                SUM(CASE WHEN status_verifikasi = 'ditolak' AND DATE(updated_at) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) THEN 1 ELSE 0 END) as ditolak_7hari,
                SUM(CASE WHEN status_verifikasi IN ('menunggu', 'review_dinas') THEN 1 ELSE 0 END) as menunggu,
                SUM(CASE WHEN DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) THEN 1 ELSE 0 END) as laporan_baru
            ")
            ->whereNull('deleted_at')
            ->first();

        $kritis = OpkLaporan::where('status_verifikasi', 'disetujui')
            ->kritis()
            ->with('kecamatan')
            ->orderByDesc('ai_urgency_score')
            ->limit(5)
            ->get()
            ->map(fn($o) => "- {$o->nama_opk} (Kec. {$o->kecamatan?->nama}, score: " . number_format($o->ai_urgency_score ?? 0, 1) . ")")
            ->implode("\n");

        return [
            'total_opk'       => (int) ($row->total_opk ?? 0),
            'laporan_baru'    => (int) ($row->laporan_baru ?? 0),
            'kritis'          => (int) ($row->kritis ?? 0),
            'waspada'         => (int) ($row->waspada ?? 0),
            'disetujui'       => (int) ($row->disetujui_7hari ?? 0),
            'ditolak'         => (int) ($row->ditolak_7hari ?? 0),
            'menunggu'        => (int) ($row->menunggu ?? 0),
            'prioritas_tinggi'=> (int) ($row->prioritas_tinggi ?? 0),
            'opk_kritis_list' => $kritis ?: '(Tidak ada OPK kritis)',
        ];
    }

    public function kategoriWithOpkCount(): \Illuminate\Database\Eloquent\Collection
    {
        return OpkCategory::withCount([
            'laporans as total' => fn($q) => $q->disetujui()
        ])->orderByDesc('total')->get();
    }

    public function kecamatanWithOpkCount(): \Illuminate\Database\Eloquent\Collection
    {
        return Kecamatan::withCount([
            'laporans as total' => fn($q) => $q->disetujui()
        ])->orderByDesc('total')->get();
    }
}
