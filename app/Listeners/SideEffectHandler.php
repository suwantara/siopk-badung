<?php

namespace App\Listeners;

use App\Events\AiAnalysisCompleted;
use App\Events\LaporanCreated;
use App\Events\LaporanVerified;
use App\Helpers\CacheKeys;
use App\Jobs\SendWhatsAppNotifJob;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SideEffectHandler
{
    private function clearSidebarCache(): void
    {
        Cache::forget(CacheKeys::SIDEBAR_COUNTS);
        Cache::forget(CacheKeys::RINGKASAN_EKSEKUTIF);
        Cache::forget(CacheKeys::LAPORAN_ADMIN);
        Cache::forget(CacheKeys::PUBLIK_DASHBOARD);
        Cache::forget(CacheKeys::DAFTAR_OPK_FILTERS . '_kategori');
        Cache::forget(CacheKeys::DAFTAR_OPK_FILTERS . '_kecamatan');
    }

    public function handleLaporanCreated(LaporanCreated $event): void
    {
        $this->clearSidebarCache();
        Log::info("[SIOPK] Laporan dibuat: {$event->laporan->kode_laporan}, WA: ".$this->maskWa($event->laporan->pelapor_whatsapp));

        SendWhatsAppNotifJob::dispatch(
            'laporan_diterima',
            $event->laporan->pelapor_whatsapp,
            $event->laporan->kode_laporan,
            $event->laporan->nama_opk
        );
    }

    public function handleLaporanVerified(LaporanVerified $event): void
    {
        $this->clearSidebarCache();
        Log::info("Laporan {$event->laporan->kode_laporan} telah {$event->status}");

        if ($event->status === 'disetujui') {
            SendWhatsAppNotifJob::dispatch(
                'laporan_disetujui',
                $event->laporan->pelapor_whatsapp,
                $event->laporan->kode_laporan,
                $event->laporan->nama_opk
            );
        } else {
            SendWhatsAppNotifJob::dispatch(
                'laporan_ditolak',
                $event->laporan->pelapor_whatsapp,
                $event->laporan->kode_laporan,
                $event->laporan->nama_opk,
                $event->laporan->catatan_verifikasi ?? ''
            );
        }
    }

    public function handleAiAnalysisCompleted(AiAnalysisCompleted $event): void
    {
        $this->clearSidebarCache();
        Log::info("AI selesai analisis: {$event->laporan->kode_laporan}");
    }

    private function maskWa(string $number): string
    {
        if (strlen($number) <= 4) {
            return '***';
        }

        return substr($number, 0, 3) . str_repeat('*', max(0, strlen($number) - 5)) . substr($number, -2);
    }
}
