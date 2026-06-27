<?php

namespace App\Jobs;

use App\Services\WhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\{InteractsWithQueue, SerializesModels};
use Illuminate\Support\Facades\Log;

class SendWhatsAppNotifJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public array $backoff = [10, 30, 60];

    public function __construct(
        private readonly string $action,
        private readonly string $nomorWa,
        private readonly string $kodeLaporan,
        private readonly string $namaOpk,
        private readonly string $catatan = '',
    ) {}

    public function handle(WhatsAppService $wa): void
    {
        Log::info("SendWhatsAppNotifJob: {$this->action} ke {$this->nomorWa} untuk {$this->kodeLaporan}");

        match ($this->action) {
            'laporan_diterima'   => $wa->notifikasiLaporanDiterima($this->nomorWa, $this->kodeLaporan, $this->namaOpk),
            'laporan_disetujui'  => $wa->notifikasiLaporanDisetujui($this->nomorWa, $this->kodeLaporan, $this->namaOpk),
            'laporan_ditolak'    => $wa->notifikasiLaporanDitolak($this->nomorWa, $this->kodeLaporan, $this->namaOpk, $this->catatan),
            default => Log::warning("SendWhatsAppNotifJob: action tidak dikenal '{$this->action}'"),
        };
    }

    public function failed(\Throwable $e): void
    {
        Log::error("SendWhatsAppNotifJob FAILED action {$this->action}: " . $e->getMessage());
    }
}
