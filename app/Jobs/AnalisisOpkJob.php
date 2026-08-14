<?php

namespace App\Jobs;

use App\Events\AiAnalysisCompleted;
use App\Models\OpkLaporan;
use App\Models\OpkRiwayatStatus;
use App\Services\AiOpkAnalyzer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * AnalisisOpkJob
 *
 * Dijalankan secara background (queue) setelah laporan baru masuk.
 * Memanggil AiOpkAnalyzer lalu menyimpan hasilnya ke database.
 *
 * Untuk development XAMPP (QUEUE_CONNECTION=sync), job ini
 * langsung berjalan synchronous tanpa perlu worker terpisah.
 */
class AnalisisOpkJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 60;

    public function __construct(
        private readonly int $laporanId
    ) {}

    public function handle(AiOpkAnalyzer $ai): void
    {
        $laporan = OpkLaporan::with(['kategori', 'kecamatan'])->find($this->laporanId);

        if (! $laporan) {
            Log::warning("AnalisisOpkJob: laporan ID {$this->laporanId} tidak ditemukan.");

            return;
        }

        // Update status → sedang diproses AI
        $laporan->update(['status_verifikasi' => 'ai_review']);

        // Skip AI jika API key tidak tersedia — pakai skor default heuristik
        if (! $ai->isAvailable()) {
            Log::info("AnalisisOpkJob: AI dilewati (API key tidak tersedia) untuk {$laporan->kode_laporan}.");

            $hasil = $ai->defaultAnalysis($laporan);

            $laporan->update([
                'ai_urgency_score' => $hasil['urgency_score'],
                'ai_duplikat_score' => $hasil['duplikat_score'],
                'ai_duplikat_of' => $hasil['duplikat_id'],
                'ai_rekomendasi' => '[Tanpa AI] '.$hasil['rekomendasi'],
                'status_verifikasi' => 'review_dinas',
            ]);

            OpkRiwayatStatus::create([
                'laporan_id' => $laporan->id,
                'status_lama' => 'ai_review',
                'status_baru' => 'review_dinas',
                'catatan' => 'AI dilewati: API key tidak tersedia. Skor default dari kondisi OPK.',
            ]);

            AiAnalysisCompleted::dispatch($laporan);

            return;
        }

        try {
            Log::info("AI mulai analisis laporan: {$laporan->kode_laporan}");

            // Panggil AI
            $hasil = $ai->analisisLaporan($laporan);

            // Simpan hasil ke database
            $laporan->update([
                'ai_urgency_score' => $hasil['urgency_score'],
                'ai_duplikat_score' => $hasil['duplikat_score'],
                'ai_duplikat_of' => $hasil['duplikat_id'],
                'ai_rekomendasi' => $this->formatRekomendasi($hasil),
                'status_verifikasi' => 'review_dinas',
            ]);

            // Catat riwayat
            OpkRiwayatStatus::create([
                'laporan_id' => $laporan->id,
                'status_lama' => 'ai_review',
                'status_baru' => 'review_dinas',
                'catatan' => sprintf(
                    'AI Score: %.1f/10 | Duplikat: %.0f%% | %s',
                    $hasil['urgency_score'],
                    $hasil['duplikat_score'],
                    $hasil['rekomendasi']
                ),
            ]);

            Log::info("AI selesai: {$laporan->kode_laporan} | Score: {$hasil['urgency_score']}");

            AiAnalysisCompleted::dispatch($laporan);

        } catch (\Exception $e) {
            Log::error("AnalisisOpkJob gagal untuk {$laporan->kode_laporan}: ".$e->getMessage(), [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            OpkRiwayatStatus::create([
                'laporan_id' => $laporan->id,
                'status_lama' => 'ai_review',
                'status_baru' => 'ai_review',
                'catatan' => 'AI gagal: '.$e->getMessage().' | Laporan tetap bisa diverifikasi manual.',
            ]);

            $laporan->update(['status_verifikasi' => 'review_dinas']);
        }
    }

    public function failed(\Throwable $e): void
    {
        Log::error("AnalisisOpkJob FAILED laporan ID {$this->laporanId}: ".$e->getMessage());

        // Pastikan laporan tetap bisa diverifikasi manual
        OpkLaporan::where('id', $this->laporanId)
            ->where('status_verifikasi', 'ai_review')
            ->update(['status_verifikasi' => 'review_dinas']);
    }

    private function formatRekomendasi(array $hasil): string
    {
        $parts = [];

        if (! empty($hasil['rekomendasi'])) {
            $parts[] = $hasil['rekomendasi'];
        }

        if (! empty($hasil['saran_verifikasi'])) {
            $parts[] = '[Saran Verifikator: '.$hasil['saran_verifikasi'].']';
        }

        return implode(' ', $parts);
    }
}
