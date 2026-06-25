<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    private string $token;
    private string $phoneId;
    private bool $enabled;

    public function __construct()
    {
        $this->token   = config('services.whatsapp.token', '');
        $this->phoneId = config('services.whatsapp.phone_id', '');
        $this->enabled = !empty($this->token) && !empty($this->phoneId);
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * Kirim notifikasi saat laporan baru diterima.
     */
    public function notifikasiLaporanDiterima(string $nomorWa, string $kodeLaporan, string $namaOpk): void
    {
        if (!$this->enabled || empty($nomorWa)) {
            return;
        }

        $pesan = "*SIOPK Badung — Laporan Diterima*\n\n"
            . "Terima kasih! Laporan OPK Anda telah kami terima.\n\n"
            . "📋 Kode Laporan: *{$kodeLaporan}*\n"
            . "🏛️ Nama OPK: *{$namaOpk}*\n\n"
            . "Status: ⏳ Menunggu Verifikasi\n\n"
            . "Pantau status laporan Anda di:\n"
            . config('app.url') . "/lapor/status?kode_laporan={$kodeLaporan}\n\n"
            . "_Dinas Kebudayaan Kabupaten Badung_";

        $this->sendMessage($nomorWa, $pesan);
    }

    /**
     * Kirim notifikasi saat laporan disetujui.
     */
    public function notifikasiLaporanDisetujui(string $nomorWa, string $kodeLaporan, string $namaOpk): void
    {
        if (!$this->enabled || empty($nomorWa)) {
            return;
        }

        $pesan = "*SIOPK Badung — Laporan Disetujui ✅*\n\n"
            . "Selamat! Laporan OPK Anda telah *DISETUJUI* oleh verifikator.\n\n"
            . "📋 Kode Laporan: *{$kodeLaporan}*\n"
            . "🏛️ Nama OPK: *{$namaOpk}*\n\n"
            . "OPK Anda kini sudah masuk di Peta Resmi Kabupaten Badung!\n\n"
            . "Lihat di peta:\n"
            . config('app.url') . "\n\n"
            . "_Dinas Kebudayaan Kabupaten Badung_";

        $this->sendMessage($nomorWa, $pesan);
    }

    /**
     * Kirim notifikasi saat laporan ditolak.
     */
    public function notifikasiLaporanDitolak(string $nomorWa, string $kodeLaporan, string $namaOpk, string $catatan = ''): void
    {
        if (!$this->enabled || empty($nomorWa)) {
            return;
        }

        $alasan = !empty($catatan) ? "\n\n📝 Catatan: {$catatan}" : '';

        $pesan = "*SIOPK Badung — Laporan Ditolak ❌*\n\n"
            . "Laporan OPK Anda tidak dapat disetujui.\n\n"
            . "📋 Kode Laporan: *{$kodeLaporan}*\n"
            . "🏛️ Nama OPK: *{$namaOpk}*{$alasan}\n\n"
            . "Anda dapat mengirim laporan baru dengan data yang lebih lengkap.\n\n"
            . "_Dinas Kebudayaan Kabupaten Badung_";

        $this->sendMessage($nomorWa, $pesan);
    }

    private function sendMessage(string $to, string $message): void
    {
        $url = "https://graph.facebook.com/v21.0/{$this->phoneId}/messages";

        try {
            $response = Http::timeout(10)
                ->connectTimeout(5)
                ->retry(2, 1000)
                ->withToken($this->token)
                ->post($url, [
                    'messaging_product' => 'whatsapp',
                    'to'                => $this->normalizeNumber($to),
                    'type'              => 'text',
                    'text'              => [
                        'preview_url' => false,
                        'body'        => $message,
                    ],
                ]);

            if (!$response->successful()) {
                Log::warning('WhatsApp notification failed', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                    'to'     => $to,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('WhatsApp notification exception', [
                'message' => $e->getMessage(),
                'to'      => $to,
            ]);
        }
    }

    private function normalizeNumber(string $number): string
    {
        $number = preg_replace('/[^0-9]/', '', $number);

        if (str_starts_with($number, '0')) {
            $number = '62' . substr($number, 1);
        }

        if (!str_starts_with($number, '62')) {
            $number = '62' . $number;
        }

        return $number;
    }
}
