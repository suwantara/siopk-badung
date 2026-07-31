<?php

namespace App\Services;

use App\Helpers\CacheKeys;
use App\Models\Kecamatan;
use App\Models\KondisiOpk;
use App\Models\OpkCategory;
use App\Models\OpkLaporan;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class OpkStatsService
{
    public function kondisiKodes(): array
    {
        return Cache::remember(CacheKeys::KONDISI_KODES, now()->addHours(24), function () {
            return KondisiOpk::orderBy('urutan')->pluck('kode')->toArray();
        });
    }

    public function kondisiNonBaikKodes(): array
    {
        $all = $this->kondisiKodes();

        return array_values(array_filter($all, fn ($k) => $k !== 'baik'));
    }

    public function dashboardAdmin(): array
    {
        $startOfMonth = now()->startOfMonth()->toDateString();
        $endOfMonth = now()->endOfMonth()->toDateString();
        $nonBaik = $this->kondisiNonBaikKodes();
        $nonBaikIn = $nonBaik ? "'".implode("','", $nonBaik)."'" : "'__none__'";
        $nonBaikCondition = $nonBaik ? "SUM(CASE WHEN status_verifikasi = 'disetujui' AND kondisi IN ({$nonBaikIn}) THEN 1 ELSE 0 END) as kritis," : '0 as kritis,';

        $kodus = $this->kondisiKodes();
        $kondisiSelects = [];
        foreach ($kodus as $k) {
            $kondisiSelects[] = "SUM(CASE WHEN status_verifikasi = 'disetujui' AND kondisi = '{$k}' THEN 1 ELSE 0 END) as kondisi_{$k}";
        }
        $kondisiSql = $kondisiSelects ? implode(', ', $kondisiSelects).',' : '';

        $row = DB::table('opk_laporans')
            ->selectRaw("
                SUM(CASE WHEN status_verifikasi = 'disetujui' THEN 1 ELSE 0 END) as total_opk,
                {$nonBaikCondition}
                SUM(CASE WHEN status_verifikasi IN ('menunggu', 'ai_review', 'review_dinas') THEN 1 ELSE 0 END) as menunggu,
                SUM(CASE WHEN status_verifikasi = 'disetujui' AND kondisi = 'baik' THEN 1 ELSE 0 END) as terlindungi,
                SUM(CASE WHEN DATE(created_at) BETWEEN ? AND ? THEN 1 ELSE 0 END) as bulan_ini
                {$kondisiSql}
            ", [$startOfMonth, $endOfMonth])
            ->whereNull('deleted_at')
            ->first();

        $kecamatanCount = OpkLaporan::disetujui()
            ->distinct('kecamatan_id')
            ->count('kecamatan_id');

        $perKondisi = [];
        foreach ($kodus as $k) {
            $key = "kondisi_{$k}";
            $perKondisi[$k] = (int) ($row->{$key} ?? 0);
        }

        return [
            'total_opk' => (int) ($row->total_opk ?? 0),
            'kritis' => (int) ($row->kritis ?? 0),
            'menunggu' => (int) ($row->menunggu ?? 0),
            'terlindungi' => (int) ($row->terlindungi ?? 0),
            'bulan_ini' => (int) ($row->bulan_ini ?? 0),
            'kecamatan_count' => $kecamatanCount,
            'per_kondisi' => $perKondisi,
        ];
    }

    public function dashboardPublik(): array
    {
        $kodus = $this->kondisiKodes();
        if (empty($kodus)) {
            return ['total' => 0, 'kritis' => 0, 'waspada' => 0, 'baik' => 0];
        }

        $counts = OpkLaporan::selectRaw('kondisi, COUNT(*) as jumlah')
            ->disetujui()
            ->groupBy('kondisi')
            ->pluck('jumlah', 'kondisi')
            ->toArray();

        $result = ['total' => 0];
        foreach ($kodus as $k) {
            $result[$k] = (int) ($counts[$k] ?? 0);
            $result['total'] += $result[$k];
        }

        return $result;
    }

    public function laporanAdmin(): array
    {
        $startOfMonth = now()->startOfMonth()->toDateString();
        $endOfMonth = now()->endOfMonth()->toDateString();
        $kodus = $this->kondisiKodes();

        $caseStatements = [];
        foreach ($kodus as $k) {
            $caseStatements[] = "SUM(CASE WHEN status_verifikasi = 'disetujui' AND kondisi = '{$k}' THEN 1 ELSE 0 END) as {$k}";
        }

        $kondisiSql = implode(', ', $caseStatements);
        $selectPrefix = $kondisiSql ? "{$kondisiSql}," : '';
        $earlyNonBaik = array_values(array_filter($kodus, fn ($k) => $k !== 'baik'));
        $earlyIn = "'".implode("','", $earlyNonBaik)."'";

        $nonBaikSql = $earlyNonBaik
            ? "SUM(CASE WHEN status_verifikasi = 'disetujui' AND kondisi IN ({$earlyIn}) THEN 1 ELSE 0 END) as kritis,"
            : '0 as kritis,';

        $row = DB::table('opk_laporans')
            ->selectRaw("
                SUM(CASE WHEN status_verifikasi = 'disetujui' THEN 1 ELSE 0 END) as disetujui,
                {$nonBaikSql}
                {$selectPrefix}
                SUM(CASE WHEN status_verifikasi = 'ditolak' THEN 1 ELSE 0 END) as ditolak,
                SUM(CASE WHEN status_verifikasi IN ('menunggu', 'ai_review', 'review_dinas') THEN 1 ELSE 0 END) as menunggu,
                SUM(CASE WHEN DATE(created_at) BETWEEN ? AND ? THEN 1 ELSE 0 END) as bulan_ini
            ", [$startOfMonth, $endOfMonth])
            ->whereNull('deleted_at')
            ->first();

        $result = [
            'total' => (int) ($row->disetujui ?? 0),
            'disetujui' => (int) ($row->disetujui ?? 0),
            'ditolak' => (int) ($row->ditolak ?? 0),
            'menunggu' => (int) ($row->menunggu ?? 0),
            'kritis' => (int) ($row->kritis ?? 0),
            'bulan_ini' => (int) ($row->bulan_ini ?? 0),
        ];

        foreach ($kodus as $k) {
            $result[$k] = (int) ($row->{$k} ?? 0);
        }

        return $result;
    }

    public function ringkasanEksekutif(): array
    {
        $sevenDaysAgo = now()->subDays(7)->format('Y-m-d');
        $nonBaik = $this->kondisiNonBaikKodes();
        $nonBaikIn = "'".implode("','", $nonBaik)."'";

        $row = DB::table('opk_laporans')
            ->selectRaw("
                SUM(CASE WHEN status_verifikasi = 'disetujui' THEN 1 ELSE 0 END) as total_opk,
                SUM(CASE WHEN status_verifikasi = 'disetujui' AND kondisi IN ({$nonBaikIn}) THEN 1 ELSE 0 END) as kritis,
                SUM(CASE WHEN status_verifikasi = 'disetujui' AND kondisi = 'waspada' THEN 1 ELSE 0 END) as waspada,
                SUM(CASE WHEN status_verifikasi = 'disetujui' AND ai_urgency_score >= 7 THEN 1 ELSE 0 END) as prioritas_tinggi,
                SUM(CASE WHEN status_verifikasi = 'disetujui' AND DATE(updated_at) >= ? THEN 1 ELSE 0 END) as disetujui_7hari,
                SUM(CASE WHEN status_verifikasi = 'ditolak' AND DATE(updated_at) >= ? THEN 1 ELSE 0 END) as ditolak_7hari,
                SUM(CASE WHEN status_verifikasi IN ('menunggu', 'ai_review', 'review_dinas') THEN 1 ELSE 0 END) as menunggu,
                SUM(CASE WHEN DATE(created_at) >= ? THEN 1 ELSE 0 END) as laporan_baru
            ", [$sevenDaysAgo, $sevenDaysAgo, $sevenDaysAgo])
            ->whereNull('deleted_at')
            ->first();

        $kritis = OpkLaporan::where('status_verifikasi', 'disetujui')
            ->whereIn('kondisi', $nonBaik)
            ->with('kecamatan')
            ->orderByDesc('ai_urgency_score')
            ->limit(5)
            ->get()
            ->map(fn ($o) => "- {$o->nama_opk} (Kec. {$o->kecamatan?->nama}, score: ".number_format($o->ai_urgency_score ?? 0, 1).')')
            ->implode("\n");

        return [
            'total_opk' => (int) ($row->total_opk ?? 0),
            'laporan_baru' => (int) ($row->laporan_baru ?? 0),
            'kritis' => (int) ($row->kritis ?? 0),
            'waspada' => (int) ($row->waspada ?? 0),
            'disetujui' => (int) ($row->disetujui_7hari ?? 0),
            'ditolak' => (int) ($row->ditolak_7hari ?? 0),
            'menunggu' => (int) ($row->menunggu ?? 0),
            'prioritas_tinggi' => (int) ($row->prioritas_tinggi ?? 0),
            'opk_kritis_list' => $kritis ?: '(Tidak ada OPK kritis)',
        ];
    }

    public function kategoriWithOpkCount(): Collection
    {
        return OpkCategory::withCount([
            'laporans as total' => fn ($q) => $q->disetujui(),
        ])->orderByDesc('total')->get();
    }

    public function kecamatanWithOpkCount(): Collection
    {
        return Kecamatan::withCount([
            'laporans as total' => fn ($q) => $q->disetujui(),
        ])->orderByDesc('total')->get();
    }

    public function kecamatanWithKondisiCount(): Collection
    {
        $counts = ['laporans as total' => fn ($q) => $q->disetujui()];
        foreach ($this->kondisiKodes() as $kode) {
            $counts["laporans as {$kode}"] = fn ($q) => $q->disetujui()->where('kondisi', $kode);
        }

        return Kecamatan::withCount($counts)
            ->orderByDesc('total')
            ->get();
    }

    public function prioritas(int $limit = 10): Collection
    {
        return OpkLaporan::with(['kategori', 'kecamatan', 'fotoUtama', 'kondisiOpk'])
            ->disetujui()
            ->whereIn('kondisi', $this->kondisiNonBaikKodes())
            ->orderByDesc('ai_urgency_score')
            ->limit($limit)
            ->get();
    }

    public function antrian(int $limit = 7): Collection
    {
        return OpkLaporan::with(['kategori', 'kecamatan'])
            ->menunggu()
            ->latest()
            ->limit($limit)
            ->get();
    }

    public function petaDataDashboard(): Collection
    {
        return OpkLaporan::select([
            'id', 'kode_laporan', 'nama_opk', 'kondisi',
            'latitude', 'longitude', 'kategori_id', 'kecamatan_id',
        ])
            ->with(['kategori:id,nama,ikon', 'kecamatan:id,nama'])
            ->disetujui()
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get();
    }

    public static function kondisiPrioritasOrderRaw(): \Illuminate\Support\Facades\DB
    {
        $kodes = Cache::remember(CacheKeys::KONDISI_KODES, now()->addHours(24), function () {
            return KondisiOpk::orderBy('urutan')->pluck('kode')->toArray();
        });
        $nonBaik = array_filter($kodes, fn ($k) => $k !== 'baik');
        $baik = in_array('baik', $kodes) ? ['baik'] : [];
        $ordered = array_merge($nonBaik, $baik);
        $quoted = "'".implode("','", $ordered)."'";

        return \Illuminate\Support\Facades\DB::raw("FIELD(kondisi, {$quoted})");
    }

    public function trenBulanan(int $bulan = 6): \Illuminate\Support\Collection
    {
        $driver = DB::connection()->getDriverName();
        $monthExpr = $driver === 'sqlite'
            ? "CAST(strftime('%m', created_at) AS INTEGER)"
            : 'MONTH(created_at)';
        $yearExpr = $driver === 'sqlite'
            ? "CAST(strftime('%Y', created_at) AS INTEGER)"
            : 'YEAR(created_at)';

        return OpkLaporan::select(
            DB::raw("{$monthExpr} as bulan"),
            DB::raw("{$yearExpr} as tahun"),
            DB::raw('COUNT(*) as total')
        )
            ->where('created_at', '>=', now()->subMonths($bulan))
            ->groupBy('tahun', 'bulan')
            ->orderBy('tahun')->orderBy('bulan')
            ->get()
            ->map(fn ($r) => [
                'label' => Carbon::createFromDate($r->tahun, $r->bulan, 1)->isoFormat('MMM Y'),
                'total' => $r->total,
            ]);
    }
}
