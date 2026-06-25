<?php

namespace App\Listeners;

use App\Events\AiAnalysisCompleted;
use App\Events\LaporanCreated;
use App\Events\LaporanVerified;
use App\Helpers\CacheKeys;
use App\Services\WhatsAppService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SideEffectHandler
{
    private function clearSidebarCache(): void
    {
        Cache::forget(CacheKeys::SIDEBAR_COUNTS);
        Cache::forget(CacheKeys::RINGKASAN_EKSEKUTIF);
    }

    public function handleLaporanCreated(LaporanCreated $event): void
    {
        $this->clearSidebarCache();
        Log::info("Laporan dibuat: {$event->laporan->kode_laporan}");

        $wa = app(WhatsAppService::class);
        $wa->notifikasiLaporanDiterima(
            $event->laporan->pelapor_whatsapp,
            $event->laporan->kode_laporan,
            $event->laporan->nama_opk
        );
    }

    public function handleLaporanVerified(LaporanVerified $event): void
    {
        $this->clearSidebarCache();
        Log::info("Laporan {$event->laporan->kode_laporan} telah {$event->status}");

        $wa = app(WhatsAppService::class);

        if ($event->status === 'disetujui') {
            $wa->notifikasiLaporanDisetujui(
                $event->laporan->pelapor_whatsapp,
                $event->laporan->kode_laporan,
                $event->laporan->nama_opk
            );
        } else {
            $wa->notifikasiLaporanDitolak(
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
}
