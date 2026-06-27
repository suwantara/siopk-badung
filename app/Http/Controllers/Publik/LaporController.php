<?php

namespace App\Http\Controllers\Publik;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLaporanRequest;
use App\Helpers\CacheKeys;
use App\Jobs\AnalisisOpkJob;
use App\Events\LaporanCreated;
use App\Models\{OpkLaporan, OpkCategory, Kecamatan, DesaDinas, DesaAdat};
use App\Services\LaporanService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Cache, DB, Log};

class LaporController extends Controller
{
    public function __construct(
        private readonly LaporanService $laporanService
    ) {}

    // Halaman form laporan publik
    public function index()
    {
        $kategori   = Cache::remember(CacheKeys::KATEGORI_LIST, 86400, fn() => OpkCategory::orderBy('nomor')->get());
        $kecamatans = Cache::remember(CacheKeys::KECAMATAN_LIST, 86400, fn() => Kecamatan::orderBy('nama')->get());
        return view('publik.lapor', compact('kategori', 'kecamatans'));
    }

    // API: ambil desa dinas berdasarkan kecamatan (AJAX)
    public function getDesaDinas(Request $request)
    {
        $request->validate(['kecamatan_id' => 'required|exists:kecamatans,id']);
        $desa = DesaDinas::where('kecamatan_id', $request->kecamatan_id)
                         ->orderBy('nama')
                         ->get(['id', 'nama']);
        return response()->json($desa);
    }

    // API: ambil desa adat berdasarkan kecamatan (AJAX)
    public function getDesaAdat(Request $request)
    {
        $request->validate(['kecamatan_id' => 'required|exists:kecamatans,id']);
        $desa = DesaAdat::where('kecamatan_id', $request->kecamatan_id)
                        ->orderBy('nama')
                        ->get(['id', 'nama']);
        return response()->json($desa);
    }

    // Simpan laporan baru
    public function store(StoreLaporanRequest $request)
    {
        $validated = $request->validated();

        DB::beginTransaction();
        try {
            $laporan = $this->laporanService->createLaporan($validated);

            if ($request->hasFile('fotos')) {
                $this->laporanService->uploadFotos(
                    $laporan,
                    $request->file('fotos'),
                    $validated['keterangan_foto_utama'] ?? null
                );
            }

            if ($request->hasFile('dokumen')) {
                $this->laporanService->uploadDokumen($laporan, $request->file('dokumen'));
            }

            $this->laporanService->saveVideoLink($laporan, $validated['link_video'] ?? null);

            DB::commit();

            AnalisisOpkJob::dispatch($laporan->id);
            LaporanCreated::dispatch($laporan);

            return redirect()->route('publik.lapor.sukses', ['kode' => $laporan->kode_laporan])
                             ->with('success', 'Laporan berhasil dikirim!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal menyimpan laporan OPK', ['error' => $e->getMessage()]);
            return back()->with('error', 'Terjadi kesalahan saat menyimpan laporan. Silakan coba lagi.')->withInput();
        }
    }

    // Halaman sukses setelah kirim laporan
    public function sukses(Request $request)
    {
        $kode    = $request->kode;
        $laporan = OpkLaporan::with(['kategori', 'kecamatan'])->where('kode_laporan', $kode)->firstOrFail();
        return view('publik.lapor-sukses', compact('laporan'));
    }

    // Cek status laporan oleh pelapor
    public function cekStatus(Request $request)
    {
        $kode    = $request->kode_laporan;
        $laporan = null;
        if ($kode) {
            $laporan = OpkLaporan::with(['kategori', 'kecamatan', 'desaDinas', 'riwayat.user'])
                ->where('kode_laporan', $kode)->first();
        }
        return view('publik.cek-status', compact('laporan', 'kode'));
    }
}
