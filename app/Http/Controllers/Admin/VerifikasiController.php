<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OpkLaporan;
use App\Models\OpkRiwayatStatus;
use App\Services\VerifikasiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class VerifikasiController extends Controller
{
    public function __construct(
        private readonly VerifikasiService $verifikasiService
    ) {}

    public function index(Request $request)
    {
        $query = OpkLaporan::with(['kategori', 'kecamatan', 'fotoUtama'])
            ->menunggu();

        if ($request->filled('kondisi')) {
            $query->where('kondisi', $request->kondisi);
        }
        if ($request->filled('kategori_id')) {
            $query->where('kategori_id', $request->kategori_id);
        }

        $laporans = $query->orderByDesc('ai_urgency_score')
            ->orderBy('created_at')
            ->paginate(15);

        return view('admin.verifikasi.index', compact('laporans'));
    }

    public function show(OpkLaporan $laporan)
    {
        $laporan->load([
            'kategori', 'kecamatan', 'desaDinas',
            'fotos', 'fotoUtama', 'dokumens', 'videos', 'riwayat.user', 'duplikatDari',
        ]);

        return view('admin.verifikasi.show', compact('laporan'));
    }

    public function setujui(Request $request, OpkLaporan $laporan)
    {
        $this->authorize('verify', $laporan);

        $request->validate(['catatan' => 'nullable|string|max:500']);

        try {
            $this->verifikasiService->setujuiLaporan($laporan, auth()->user(), $request->catatan);

            return redirect()->route('admin.verifikasi.index')
                ->with('success', "Laporan {$laporan->kode_laporan} berhasil disetujui.");
        } catch (\Exception $e) {
            Log::error('Gagal menyetujui laporan', ['laporan_id' => $laporan->id, 'error' => $e->getMessage()]);

            return back()->with('error', 'Gagal memverifikasi laporan: '.$e->getMessage());
        }
    }

    public function tolak(Request $request, OpkLaporan $laporan)
    {
        $this->authorize('verify', $laporan);

        $request->validate([
            'catatan' => 'required|string|max:500',
            'alasan' => 'required|in:tidak_valid,duplikat,kurang_data,diluar_wilayah,lainnya',
        ]);

        try {
            $this->verifikasiService->tolakLaporan($laporan, auth()->user(), $request->alasan, $request->catatan);

            return redirect()->route('admin.verifikasi.index')
                ->with('success', "Laporan {$laporan->kode_laporan} berhasil ditolak.");
        } catch (\Exception $e) {
            Log::error('Gagal menolak laporan', ['laporan_id' => $laporan->id, 'error' => $e->getMessage()]);

            return back()->with('error', 'Gagal memverifikasi laporan: '.$e->getMessage());
        }
    }

    public function updateAiScore(Request $request, OpkLaporan $laporan)
    {
        $request->validate([
            'ai_urgency_score' => 'required|numeric|min:0|max:10',
            'ai_rekomendasi' => 'nullable|string',
        ]);

        $laporan->update([
            'ai_urgency_score' => $request->ai_urgency_score,
            'ai_rekomendasi' => $request->ai_rekomendasi,
        ]);

        OpkRiwayatStatus::create([
            'laporan_id' => $laporan->id,
            'status_lama' => $laporan->status_verifikasi,
            'status_baru' => $laporan->status_verifikasi,
            'user_id' => auth()->id(),
            'catatan' => sprintf(
                'AI score manual: %.1f | %s',
                $request->ai_urgency_score,
                $request->ai_rekomendasi ?? '-'
            ),
        ]);

        return back()->with('success', 'AI score diperbarui.');
    }
}
