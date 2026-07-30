<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\CacheKeys;
use App\Http\Controllers\Controller;
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
            $perKategori = $this->statsService->kategoriWithOpkCount();
            $perKecamatan = $this->statsService->kecamatanWithOpkCount();
            $prioritas = $this->statsService->prioritas();
            $antrian = $this->statsService->antrian();
            $petaData = $this->statsService->petaDataDashboard();

            return compact(
                'stats', 'perKategori', 'perKecamatan',
                'prioritas', 'antrian', 'petaData'
            );
        });

        return view('admin.dashboard.index', $data);
    }
}
