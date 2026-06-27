<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    private string $token;
    private string $countryCode;
    private bool $enabled;

    public function __construct()
    {
        $this->token       = config('services.whatsapp.token', '');
        $this->countryCode = config('services.whatsapp.country_code', '62');
        $this->enabled     = !empty($this->token);
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function notifikasiLaporanDiterima(string $nomorWa, string $kodeLaporan, string $namaOpk): void
    {
        if (!$this->enabled || empty($nomorWa)) {
            return;
        }

        $pesan = "*SIOPK Badung — Laporan Diterima*\n\n"
            . "Terima kasih! Laporan OPK Anda telah kami terima.\n\n"
            . "\xF0\x9F\x93\x8B Kode Laporan: *{$kodeLaporan}*\n"
            . "\xF0\x9F\x8F\x9B\xEF\xB8\x8F Nama OPK: *{$namaOpk}*\n\n"
            . "Status: \xE2\x8F\xB3 Menunggu Verifikasi\n\n"
            . "Pantau status laporan Anda di:\n"
            . config('app.url') . "/lapor/status?kode_laporan={$kodeLaporan}\n\n"
            . "_Dinas Kebudayaan Kabupaten Badung_";

        $this->send($nomorWa, $pesan);
    }

    public function notifikasiLaporanDisetujui(string $nomorWa, string $kodeLaporan, string $namaOpk): void
    {
        if (!$this->enabled || empty($nomorWa)) {
            return;
        }

        $pesan = "*SIOPK Badung — Laporan Disetujui* \xE2\x9C\x85\n\n"
            . "Selamat! Laporan OPK Anda telah *DISETUJUI* oleh verifikator.\n\n"
            . "\xF0\x9F\x93\x8B Kode Laporan: *{$kodeLaporan}*\n"
            . "\xF0\x9F\x8F\x9B\xEF\xB8\x8F Nama OPK: *{$namaOpk}*\n\n"
            . "OPK Anda kini sudah masuk di Peta Resmi Kabupaten Badung!\n\n"
            . "Lihat di peta:\n"
            . config('app.url') . "\n\n"
            . "_Dinas Kebudayaan Kabupaten Badung_";

        $this->send($nomorWa, $pesan);
    }

    public function notifikasiLaporanDitolak(string $nomorWa, string $kodeLaporan, string $namaOpk, string $catatan = ''): void
    {
        if (!$this->enabled || empty($nomorWa)) {
            return;
        }

        $alasan = !empty($catatan) ? "\n\n\xF0\x9F\x93\x9D Catatan: {$catatan}" : '';

        $pesan = "*SIOPK Badung — Laporan Ditolak* \xE2\x9D\x8C\n\n"
            . "Laporan OPK Anda tidak dapat disetujui.\n\n"
            . "\xF0\x9F\x93\x8B Kode Laporan: *{$kodeLaporan}*\n"
            . "\xF0\x9F\x8F\x9B\xEF\xB8\x8F Nama OPK: *{$namaOpk}*{$alasan}\n\n"
            . "Anda dapat mengirim laporan baru dengan data yang lebih lengkap.\n\n"
            . "_Dinas Kebudayaan Kabupaten Badung_";

        $this->send($nomorWa, $pesan);
    }

    private function send(string $to, string $message): void
    {
        try {
            $response = Http::timeout(15)
                ->connectTimeout(5)
                ->withHeaders(['Authorization' => $this->token])
                ->asForm()
                ->post('https://api.fonnte.com/send', [
                    'target'      => $this->normalizeNumber($to),
                    'message'     => $message,
                    'countryCode' => $this->countryCode,
                ]);

            if (!$response->successful()) {
                Log::warning('Fonnte notification failed', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                    'to'     => $to,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Fonnte notification exception', [
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
