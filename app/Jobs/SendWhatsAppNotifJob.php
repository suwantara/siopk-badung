<?php

namespace App\Jobs;

use App\Services\WhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendWhatsAppNotifJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;

    public array $backoff = [5, 30];

    public function __construct(
        private readonly string $action,
        private readonly string $nomorWa,
        private readonly string $kodeLaporan,
        private readonly string $namaOpk,
        private readonly string $catatan = '',
    ) {}

    public function handle(WhatsAppService $wa): void
    {
        if (trim($this->nomorWa) === '') {
            Log::info("SendWhatsAppNotifJob: nomor WA kosong, notifikasi {$this->action} dilewati untuk {$this->kodeLaporan}");

            return;
        }

        Log::info("SendWhatsAppNotifJob: {$this->action} ke ".$this->maskWa()." untuk {$this->kodeLaporan}");

        match ($this->action) {
            'laporan_diterima' => $wa->notifikasiLaporanDiterima($this->nomorWa, $this->kodeLaporan, $this->namaOpk),
            'laporan_disetujui' => $wa->notifikasiLaporanDisetujui($this->nomorWa, $this->kodeLaporan, $this->namaOpk),
            'laporan_ditolak' => $wa->notifikasiLaporanDitolak($this->nomorWa, $this->kodeLaporan, $this->namaOpk, $this->catatan),
            default => Log::warning("SendWhatsAppNotifJob: action tidak dikenal '{$this->action}'"),
        };
    }

    public function failed(\Throwable $e): void
    {
        Log::warning("SendWhatsAppNotifJob FAILED action {$this->action}: ".$e->getMessage());
    }

    private function maskWa(): string
    {
        if (strlen($this->nomorWa) <= 4) {
            return '***';
        }

        return substr($this->nomorWa, 0, 3).str_repeat('*', max(0, strlen($this->nomorWa) - 5)).substr($this->nomorWa, -2);
    }
}
