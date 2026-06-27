<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateOpkRequest;
use App\Helpers\CacheKeys;
use App\Models\{OpkLaporan, OpkCategory, Kecamatan};
use App\Services\{PetaDataService, OpkMediaService};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class OpkController extends Controller
{
    public function __construct(
        private readonly PetaDataService $petaService,
        private readonly OpkMediaService $mediaService
    ) {}

    public function index(Request $request)
    {
        $query = OpkLaporan::with(['kategori', 'kecamatan', 'fotoUtama'])
            ->disetujui();

        if ($request->filled('search')) {
            $query->where('nama_opk', 'like', '%' . $request->search . '%');
        }
        if ($request->filled('kategori_id')) {
            $query->where('kategori_id', $request->kategori_id);
        }
        if ($request->filled('kecamatan_id')) {
            $query->where('kecamatan_id', $request->kecamatan_id);
        }
        if ($request->filled('kondisi')) {
            $query->where('kondisi', $request->kondisi);
        }

        $laporans   = $query->latest()->paginate(20)->withQueryString();
        $kategori   = Cache::remember(CacheKeys::KATEGORI_LIST, 86400, fn() => OpkCategory::orderBy('nomor')->get());
        $kecamatans = Cache::remember(CacheKeys::KECAMATAN_LIST, 86400, fn() => Kecamatan::orderBy('nama')->get());

        return view('admin.opk.index', compact('laporans', 'kategori', 'kecamatans'));
    }

    public function show(OpkLaporan $laporan)
    {
        $laporan->load(['kategori', 'kecamatan', 'desaDinas', 'fotos', 'dokumens', 'videos', 'riwayat.user', 'verifikator']);
        return view('admin.opk.show', compact('laporan'));
    }

    public function edit(OpkLaporan $laporan)
    {
        $this->authorize('update', $laporan);
        $laporan->load(['kategori', 'kecamatan', 'fotos']);
        $kategori   = Cache::remember(CacheKeys::KATEGORI_LIST, 86400, fn() => OpkCategory::orderBy('nomor')->get());
        $kecamatans = Cache::remember(CacheKeys::KECAMATAN_WITH_DESA, 86400, fn() => Kecamatan::with('desaDinas')->orderBy('nama')->get());
        return view('admin.opk.edit', compact('laporan', 'kategori', 'kecamatans'));
    }

    public function update(UpdateOpkRequest $request, OpkLaporan $laporan)
    {
        $validated = $request->validated();

        $laporan->nama_opk           = $validated['nama_opk'];
        $laporan->kondisi            = $validated['kondisi'];
        $laporan->status_pelindungan = $validated['status_pelindungan'];
        $laporan->deskripsi_umum     = $validated['deskripsi_umum'];
        $laporan->sejarah_asal_usul  = $validated['sejarah_asal_usul'] ?? $laporan->sejarah_asal_usul;
        $laporan->nilai_makna_budaya = $validated['nilai_makna_budaya'] ?? $laporan->nilai_makna_budaya;
        $laporan->latitude           = $validated['latitude'] ?? $laporan->latitude;
        $laporan->longitude          = $validated['longitude'] ?? $laporan->longitude;
        $laporan->save();

        $hapusIds = $this->parseHapusIds($validated['hapus_foto_ids'] ?? []);
        $this->mediaService->deleteFotos($laporan->id, $hapusIds);

        if ($request->hasFile('fotos')) {
            try {
                $this->mediaService->uploadFotos($laporan, $request->file('fotos'), count($hapusIds));
            } catch (\RuntimeException $e) {
                return back()->withInput()->with('error', $e->getMessage());
            }
        }

        if (!empty($validated['foto_utama_id'])) {
            $this->mediaService->setFotoUtama($laporan->id, $validated['foto_utama_id']);
        }

        return redirect()->route('admin.opk.show', $laporan)
                         ->with('success', 'Data OPK berhasil diperbarui.');
    }

    private function parseHapusIds(array|string $hapusIds): array
    {
        if (is_string($hapusIds)) {
            return array_filter(explode(',', $hapusIds));
        }

        return $hapusIds;
    }

    // Arsipkan (soft delete)
    public function destroy(OpkLaporan $laporan)
    {
        $laporan->delete(); // soft delete — data tetap di DB, tidak muncul di tampilan
        return redirect()->route('admin.opk.index')
                         ->with('success', 'OPK berhasil diarsipkan. Data masih tersimpan di database.');
    }

    // Restore dari arsip
    public function restore($id)
    {
        $laporan = OpkLaporan::withTrashed()->findOrFail($id);
        $laporan->restore();
        return redirect()->route('admin.opk.index')
                         ->with('success', 'OPK berhasil dipulihkan dari arsip.');
    }

    // Hapus permanen dari arsip (hanya yang sudah di-soft-delete)
    public function forceDelete($id)
    {
        $laporan = OpkLaporan::onlyTrashed()->findOrFail($id);
        $laporan->forceDelete();

        return redirect()->route('admin.opk.arsip')
                         ->with('success', 'OPK berhasil dihapus permanen dari database.');
    }

    public function peta()
    {
        return view('admin.opk.peta');
    }

    public function petaJson(Request $request)
    {
        return response()->json(
            $this->petaService->getPetaData($request, true)
        );
    }

    // Daftar OPK yang diarsipkan
    public function arsip(Request $request)
    {
        $laporans = OpkLaporan::onlyTrashed()
            ->with(['kategori', 'kecamatan'])
            ->latest('deleted_at')
            ->paginate(20);
        return view('admin.opk.arsip', compact('laporans'));
    }
}
