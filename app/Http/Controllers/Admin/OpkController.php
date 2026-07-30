<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\CacheKeys;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateOpkRequest;
use App\Models\Kecamatan;
use App\Models\KondisiOpk;
use App\Models\OpkCategory;
use App\Models\OpkLaporan;
use App\Services\OpkExportService;
use App\Services\OpkMediaService;
use App\Services\PetaDataService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class OpkController extends Controller
{
    public function __construct(
        private readonly PetaDataService $petaService,
        private readonly OpkMediaService $mediaService,
        private readonly OpkExportService $exportService,
    ) {}

    public function index(Request $request)
    {
        $query = OpkLaporan::with(['kategori', 'kecamatan', 'fotoUtama'])
            ->disetujui();

        if ($request->filled('search')) {
            $query->where('nama_opk', 'like', '%'.$request->search.'%');
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

        $laporans = $query->latest()->paginate(20)->withQueryString();
        $kategori = Cache::remember(CacheKeys::KATEGORI_LIST, 86400, fn () => OpkCategory::orderBy('nomor')->get());
        $kecamatans = Cache::remember(CacheKeys::KECAMATAN_LIST, 86400, fn () => Kecamatan::orderBy('nama')->get());

        return view('admin.opk.index', compact('laporans', 'kategori', 'kecamatans'));
    }

    public function show(OpkLaporan $laporan)
    {
        $laporan->load(['kategori', 'kecamatan', 'desaDinas', 'kondisiOpk', 'fotos', 'fotoUtama', 'dokumens', 'videos', 'riwayat.user', 'verifikator']);

        return view('admin.opk.show', compact('laporan'));
    }

    public function edit(OpkLaporan $laporan)
    {
        $this->authorize('update', $laporan);
        $laporan->load(['kategori', 'kecamatan', 'fotos']);
        $kategori = Cache::remember(CacheKeys::KATEGORI_LIST, 86400, fn () => OpkCategory::orderBy('nomor')->get());
        $kecamatans = Cache::remember(CacheKeys::KECAMATAN_WITH_DESA, 86400, fn () => Kecamatan::with('desaDinas')->orderBy('nama')->get());

        return view('admin.opk.edit', compact('laporan', 'kategori', 'kecamatans'));
    }

    public function update(UpdateOpkRequest $request, OpkLaporan $laporan)
    {
        $validated = $request->validated();

        $laporan->nama_opk = $validated['nama_opk'];
        $laporan->kondisi = $validated['kondisi'];
        $laporan->status_pelindungan = $validated['status_pelindungan'];
        $laporan->deskripsi_umum = $validated['deskripsi_umum'];
        $laporan->sejarah_asal_usul = $validated['sejarah_asal_usul'] ?? $laporan->sejarah_asal_usul;
        $laporan->nilai_makna_budaya = $validated['nilai_makna_budaya'] ?? $laporan->nilai_makna_budaya;
        $laporan->latitude = $validated['latitude'] ?? $laporan->latitude;
        $laporan->longitude = $validated['longitude'] ?? $laporan->longitude;
        $laporan->save();

        PetaDataService::invalidateCache();
        Cache::forget(CacheKeys::SIDEBAR_COUNTS);
        Cache::forget(CacheKeys::LAPORAN_ADMIN);
        Cache::forget(CacheKeys::RINGKASAN_EKSEKUTIF);
        Cache::forget(CacheKeys::DAFTAR_OPK_FILTERS . '_kategori');
        Cache::forget(CacheKeys::DAFTAR_OPK_FILTERS . '_kecamatan');

        $hapusIds = $this->parseHapusIds($validated['hapus_foto_ids'] ?? []);
        $this->mediaService->deleteFotos($laporan->id, $hapusIds);

        if ($request->hasFile('fotos')) {
            try {
                $this->mediaService->uploadFotos($laporan, $request->file('fotos'), count($hapusIds));
            } catch (\RuntimeException $e) {
                return back()->withInput()->with('error', $e->getMessage());
            }
        }

        if (! empty($validated['foto_utama_id'])) {
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
        $this->authorize('delete', $laporan);
        $laporan->delete();

        PetaDataService::invalidateCache();
        Cache::forget(CacheKeys::SIDEBAR_COUNTS);
        Cache::forget(CacheKeys::LAPORAN_ADMIN);
        Cache::forget(CacheKeys::RINGKASAN_EKSEKUTIF);
        Cache::forget(CacheKeys::DAFTAR_OPK_FILTERS . '_kategori');
        Cache::forget(CacheKeys::DAFTAR_OPK_FILTERS . '_kecamatan');

        return redirect()->route('admin.opk.index')
            ->with('success', 'OPK berhasil diarsipkan. Data masih tersimpan di database.');
    }

    // Restore dari arsip
    public function restore($id)
    {
        $laporan = OpkLaporan::withTrashed()->findOrFail($id);
        $this->authorize('restore', $laporan);
        $laporan->restore();

        PetaDataService::invalidateCache();
        Cache::forget(CacheKeys::SIDEBAR_COUNTS);
        Cache::forget(CacheKeys::LAPORAN_ADMIN);
        Cache::forget(CacheKeys::RINGKASAN_EKSEKUTIF);
        Cache::forget(CacheKeys::DAFTAR_OPK_FILTERS . '_kategori');
        Cache::forget(CacheKeys::DAFTAR_OPK_FILTERS . '_kecamatan');

        return redirect()->route('admin.opk.index')
            ->with('success', 'OPK berhasil dipulihkan dari arsip.');
    }

    // Hapus permanen dari arsip (hanya yang sudah di-soft-delete)
    public function forceDelete($id)
    {
        $laporan = OpkLaporan::onlyTrashed()->findOrFail($id);
        $this->authorize('forceDelete', $laporan);
        $laporan->forceDelete();

        PetaDataService::invalidateCache();
        Cache::forget(CacheKeys::SIDEBAR_COUNTS);
        Cache::forget(CacheKeys::LAPORAN_ADMIN);
        Cache::forget(CacheKeys::RINGKASAN_EKSEKUTIF);
        Cache::forget(CacheKeys::DAFTAR_OPK_FILTERS . '_kategori');
        Cache::forget(CacheKeys::DAFTAR_OPK_FILTERS . '_kecamatan');

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

    public function exportOne(OpkLaporan $laporan)
    {
        $includePrivate = auth()->user()->isAdmin() || auth()->user()->isSuperAdmin();

        $filePath = $this->exportService->exportSingle($laporan, $includePrivate);

        return response()->download($filePath)->deleteFileAfterSend();
    }

    public function exportForm()
    {
        $kategori = Cache::remember(CacheKeys::KATEGORI_LIST, 86400, fn () => OpkCategory::orderBy('nomor')->get());
        $kecamatans = Cache::remember(CacheKeys::KECAMATAN_LIST, 86400, fn () => Kecamatan::orderBy('nama')->get());
        $kondisiList = Cache::remember(CacheKeys::KONDISI_LIST, 86400, fn () => KondisiOpk::orderBy('urutan')->get());

        $statusList = [
            'disetujui' => 'Disetujui',
            'ditolak' => 'Ditolak',
            'duplikat' => 'Duplikat',
            'menunggu' => 'Menunggu',
            'ai_review' => 'AI Review',
            'review_dinas' => 'Review Dinas',
        ];

        return view('admin.opk.export', compact('kategori', 'kecamatans', 'kondisiList', 'statusList'));
    }

    public function exportAll(Request $request)
    {
        $filters = $request->only([
            'search', 'status_verifikasi', 'kategori_id', 'kecamatan_id',
            'kondisi', 'tanggal_dari', 'tanggal_sampai',
        ]);

        $sheets = $request->input('sheets', []);

        $filePath = $this->exportService->exportBatch($filters, $sheets);

        return response()->download($filePath)->deleteFileAfterSend();
    }
}
