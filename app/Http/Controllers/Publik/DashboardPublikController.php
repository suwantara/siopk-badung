<?php

namespace App\Http\Controllers\Publik;

use App\Http\Controllers\Controller;
use App\Models\{OpkLaporan, OpkCategory, Kecamatan};
use App\Helpers\CacheKeys;
use App\Services\{OpkStatsService, PetaDataService};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class DashboardPublikController extends Controller
{
    public function __construct(
        private readonly OpkStatsService $statsService,
        private readonly PetaDataService $petaService
    ) {}

    public function index()
    {
        $data = Cache::remember(CacheKeys::PUBLIK_DASHBOARD, 120, function () {
            $stats     = $this->statsService->dashboardPublik();
            $kategori   = $this->statsService->kategoriWithOpkCount();
            $kecamatans = $this->statsService->kecamatanWithOpkCount();

            $terbaru = OpkLaporan::with(['kategori', 'kecamatan', 'fotoUtama'])
                ->disetujui()
                ->latest()
                ->limit(6)
                ->get();

            return compact('stats', 'kategori', 'kecamatans', 'terbaru');
        });

        return view('publik.dashboard', $data);
    }

    public function petaJson(Request $request)
    {
        return response()->json(
            $this->petaService->getPetaData($request)
        );
    }

    // Detail OPK publik
    public function showOpk(OpkLaporan $opk)
    {
        if ($opk->status_verifikasi !== 'disetujui') {
            abort(404);
        }
        $opk->load(['kategori', 'kecamatan', 'desaDinas', 'fotoUtama', 'fotos', 'videos']);
        return view('publik.opk-detail', compact('opk'));
    }

    public function daftarOpk(Request $request)
    {
        $query = OpkLaporan::with(['kategori', 'kecamatan', 'fotoUtama'])
            ->disetujui();

        if ($request->filled('cari')) {
            $q = $request->cari;
            $query->where(function ($qry) use ($q) {
                $qry->where('nama_opk', 'like', "%{$q}%")
                    ->orWhere('deskripsi_umum', 'like', "%{$q}%")
                    ->orWhere('nama_desa_adat', 'like', "%{$q}%");
            });
        }

        if ($request->filled('kategori')) {
            $query->where('kategori_id', $request->kategori);
        }

        if ($request->filled('kecamatan')) {
            $query->where('kecamatan_id', $request->kecamatan);
        }

        if ($request->filled('kondisi')) {
            $query->where('kondisi', $request->kondisi);
        }

        $sort = $request->get('urut', 'terbaru');
        match ($sort) {
            'terbaru'  => $query->latest(),
            'terlama'  => $query->oldest(),
            'nama'     => $query->orderBy('nama_opk'),
            'kritis'   => $query->orderByRaw("FIELD(kondisi, 'kritis', 'waspada', 'baik')"),
            default    => $query->latest(),
        };

        $opks = $query->paginate(20)->withQueryString();

        $kategori   = Cache::remember(CacheKeys::DAFTAR_OPK_FILTERS . '_kategori', 300, fn() =>
            OpkCategory::withCount(['laporans as total' => fn($q) => $q->disetujui()])->orderByDesc('total')->get()
        );
        $kecamatans = Cache::remember(CacheKeys::DAFTAR_OPK_FILTERS . '_kecamatan', 300, fn() =>
            Kecamatan::withCount(['laporans as total' => fn($q) => $q->disetujui()])->orderByDesc('total')->get()
        );

        return view('publik.daftar-opk', compact('opks', 'kategori', 'kecamatans'));
    }
}
