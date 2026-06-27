<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\CacheKeys;
use App\Http\Controllers\Controller;
use App\Models\Kecamatan;
use App\Models\OpkCategory;
use App\Models\OpkLaporan;
use App\Services\OpkStatsService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class LaporanAdminController extends Controller
{
    public function __construct(
        private readonly OpkStatsService $statsService
    ) {}

    public function index()
    {
        $data = Cache::remember(CacheKeys::LAPORAN_ADMIN, 120, function () {
            $stats = $this->statsService->laporanAdmin();

            $perKategori = OpkCategory::withCount([
                'laporans as total' => fn ($q) => $q->disetujui(),
                'laporans as kritis' => fn ($q) => $q->disetujui()->kritis(),
                'laporans as waspada' => fn ($q) => $q->disetujui()->waspada(),
            ])->orderByDesc('total')->get();

            $perKecamatan = Kecamatan::withCount([
                'laporans as total' => fn ($q) => $q->disetujui(),
                'laporans as kritis' => fn ($q) => $q->disetujui()->kritis(),
            ])->orderByDesc('total')->get();

            $driver = DB::connection()->getDriverName();
            $monthExpr = $driver === 'sqlite'
                ? "CAST(strftime('%m', created_at) AS INTEGER)"
                : 'MONTH(created_at)';
            $yearExpr = $driver === 'sqlite'
                ? "CAST(strftime('%Y', created_at) AS INTEGER)"
                : 'YEAR(created_at)';

            $tren = OpkLaporan::select(
                DB::raw("{$monthExpr} as bulan"),
                DB::raw("{$yearExpr} as tahun"),
                DB::raw('COUNT(*) as total')
            )
                ->where('created_at', '>=', now()->subMonths(6))
                ->groupBy('tahun', 'bulan')
                ->orderBy('tahun')->orderBy('bulan')
                ->get()
                ->map(fn ($r) => [
                    'label' => Carbon::createFromDate($r->tahun, $r->bulan, 1)->isoFormat('MMM Y'),
                    'total' => $r->total,
                ]);

            $topUrgensi = OpkLaporan::with(['kategori', 'kecamatan'])
                ->disetujui()
                ->whereNotNull('ai_urgency_score')
                ->orderByDesc('ai_urgency_score')
                ->limit(10)
                ->get();

            return compact('stats', 'perKategori', 'perKecamatan', 'tren', 'topUrgensi');
        });

        return view('admin.laporan.index', $data);
    }

    // Export CSV — streaming dengan cursor
    public function exportCsv()
    {
        $filename = 'opk-badung-'.now()->format('Y-m-d').'.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () {
            $out = fopen('php://output', 'w');
            // BOM untuk Excel
            fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($out, [
                'Kode', 'Nama OPK', 'Jenis OPK', 'Kondisi',
                'Kecamatan', 'Desa Dinas', 'Desa Adat',
                'Latitude', 'Longitude',
                'Status Pelindungan', 'AI Score',
                'Tanggal Lapor',
            ], ';');

            OpkLaporan::with(['kategori', 'kecamatan', 'desaDinas'])
                ->disetujui()
                ->cursor()
                ->each(function ($o) use ($out) {
                    fputcsv($out, [
                        $o->kode_laporan,
                        $o->nama_opk,
                        $o->kategori?->nama,
                        $o->kondisi,
                        $o->kecamatan?->nama,
                        $o->desaDinas?->nama,
                        $o->nama_desa_adat,
                        $o->latitude,
                        $o->longitude,
                        $o->status_pelindungan,
                        $o->ai_urgency_score,
                        $o->created_at->format('Y-m-d'),
                    ], ';');
                });

            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }
}
