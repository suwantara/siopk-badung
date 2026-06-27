<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Helpers\CacheKeys;
use App\Models\{OpkLaporan, OpkCategory, Kecamatan};
use App\Services\OpkStatsService;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function __construct(
        private readonly OpkStatsService $statsService
    ) {}

    public function index()
    {
        $cacheKey = CacheKeys::adminDashboard(auth()->id());

        $data = Cache::remember($cacheKey, 60, function () {
            $stats = $this->statsService->dashboardAdmin();

            $perKategori  = $this->statsService->kategoriWithOpkCount();
            $perKecamatan = $this->statsService->kecamatanWithOpkCount();

            $prioritas = OpkLaporan::with(['kategori', 'kecamatan', 'fotoUtama'])
                ->disetujui()
                ->whereIn('kondisi', ['kritis', 'waspada'])
                ->orderByDesc('ai_urgency_score')
                ->orderBy('kondisi')
                ->limit(10)
                ->get();

            $antrian = OpkLaporan::with(['kategori', 'kecamatan'])
                ->menunggu()
                ->latest()
                ->limit(7)
                ->get();

            $petaData = OpkLaporan::select([
                    'id', 'kode_laporan', 'nama_opk', 'kondisi',
                    'latitude', 'longitude', 'kategori_id', 'kecamatan_id'
                ])
                ->with(['kategori:id,nama,ikon', 'kecamatan:id,nama'])
                ->disetujui()
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->get();

            return compact(
                'stats', 'perKategori', 'perKecamatan',
                'prioritas', 'antrian', 'petaData'
            );
        });

        return view('admin.dashboard.index', $data);
    }
}
