<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\CacheKeys;
use App\Http\Controllers\Controller;
use App\Models\OpkLaporan;
use App\Services\OpkExportService;
use App\Services\OpkStatsService;
use Illuminate\Support\Facades\Cache;

class LaporanAdminController extends Controller
{
    public function __construct(
        private readonly OpkStatsService $statsService,
        private readonly OpkExportService $exportService,
    ) {}

    public function index()
    {
        $data = Cache::remember(CacheKeys::LAPORAN_ADMIN, 120, function () {
            $stats = $this->statsService->laporanAdmin();
            $perKategori = $this->statsService->kategoriWithOpkCount();
            $perKecamatan = $this->statsService->kecamatanWithKondisiCount();
            $tren = $this->statsService->trenBulanan();

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

    public function exportCsv()
    {
        [$headers, $callback] = $this->exportService->exportCsv();

        return response()->stream($callback, 200, $headers);
    }
}
