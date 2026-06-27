<?php

namespace App\Services;

use App\Events\LaporanVerified;
use App\Models\{OpkLaporan, OpkRiwayatStatus, User};
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VerifikasiService
{
    public function setujuiLaporan(OpkLaporan $laporan, User $verifikator, ?string $catatan = null): void
    {
        $this->updateStatus($laporan, $verifikator, 'disetujui', $catatan);
    }

    public function tolakLaporan(OpkLaporan $laporan, User $verifikator, string $alasan, string $catatan): void
    {
        $statusBaru = $alasan === 'duplikat' ? 'duplikat' : 'ditolak';
        $catatan    = $alasan === 'duplikat'
            ? "[duplikat] " . $catatan
            : "[{$alasan}] " . $catatan;

        $this->updateStatus($laporan, $verifikator, $statusBaru, $catatan);
    }

    private function updateStatus(OpkLaporan $laporan, User $verifikator, string $statusBaru, ?string $catatan): void
    {
        DB::beginTransaction();
        try {
            $statusLama = $laporan->status_verifikasi;

            $laporan->update([
                'status_verifikasi'  => $statusBaru,
                'diverifikasi_oleh'  => $verifikator->id,
                'tanggal_verifikasi' => now(),
                'catatan_verifikasi' => $catatan,
            ]);

            OpkRiwayatStatus::create([
                'laporan_id'  => $laporan->id,
                'status_lama' => $statusLama,
                'status_baru' => $statusBaru,
                'user_id'     => $verifikator->id,
                'catatan'     => $catatan ?? ($statusBaru === 'disetujui' ? 'Disetujui oleh verifikator.' : 'Ditolak oleh verifikator.'),
            ]);

            DB::commit();

            LaporanVerified::dispatch($laporan, $statusBaru);
            PetaDataService::invalidateCache();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal memverifikasi laporan', ['laporan_id' => $laporan->id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }
}
