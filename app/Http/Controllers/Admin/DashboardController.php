<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\CacheKeys;
use App\Http\Controllers\Controller;
use App\Models\KondisiOpk;
use App\Models\OpkLaporan;
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

            $nonBaikKodes = $this->nonBaikKodes();
            $perKategori = $this->statsService->kategoriWithOpkCount();
            $perKecamatan = $this->statsService->kecamatanWithOpkCount();

            $prioritas = OpkLaporan::with(['kategori', 'kecamatan', 'fotoUtama', 'kondisiOpk'])
                ->disetujui()
                ->whereIn('kondisi', $nonBaikKodes)
                ->orderByDesc('ai_urgency_score')
                ->limit(10)
                ->get();

            $antrian = OpkLaporan::with(['kategori', 'kecamatan'])
                ->menunggu()
                ->latest()
                ->limit(7)
                ->get();

            $petaData = OpkLaporan::select([
                'id', 'kode_laporan', 'nama_opk', 'kondisi',
                'latitude', 'longitude', 'kategori_id', 'kecamatan_id',
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

    private function nonBaikKodes(): array
    {
        return Cache::remember(CacheKeys::KONDISI_KODES, now()->addHours(24), function () {
            return KondisiOpk::orderBy('urutan')->pluck('kode')
                ->filter(fn ($k) => $k !== 'baik')
                ->values()
                ->toArray();
        });
    }
}
