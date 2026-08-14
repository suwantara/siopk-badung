<?php

namespace App\Services;

use App\Enums\StatusVerifikasi;
use App\Events\LaporanVerified;
use App\Models\OpkLaporan;
use App\Models\OpkRiwayatStatus;
use App\Models\User;
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
        $catatan = $alasan === 'duplikat'
            ? '[duplikat] '.$catatan
            : "[{$alasan}] ".$catatan;

        $this->updateStatus($laporan, $verifikator, $statusBaru, $catatan);
    }

    private function updateStatus(OpkLaporan $laporan, User $verifikator, string $statusBaru, ?string $catatan): void
    {
        $statusLama = StatusVerifikasi::tryFrom($laporan->status_verifikasi);
        $target = StatusVerifikasi::tryFrom($statusBaru);

        if (! $statusLama || ! $target) {
            throw new \InvalidArgumentException("Status tidak valid: dari '{$laporan->status_verifikasi}' ke '{$statusBaru}'.");
        }

        if (! $statusLama->canTransitionTo($target)) {
            throw new \RuntimeException(
                "Transisi status tidak diizinkan: dari '{$statusLama->value}' ke '{$target->value}' pada laporan {$laporan->kode_laporan}."
            );
        }

        DB::beginTransaction();
        try {
            $statusLamaStr = $laporan->status_verifikasi;

            $laporan->update([
                'status_verifikasi' => $target->value,
                'diverifikasi_oleh' => $verifikator->id,
                'tanggal_verifikasi' => now(),
                'catatan_verifikasi' => $catatan,
            ]);

            OpkRiwayatStatus::create([
                'laporan_id' => $laporan->id,
                'status_lama' => $statusLamaStr,
                'status_baru' => $target->value,
                'user_id' => $verifikator->id,
                'catatan' => $catatan ?? ($target === StatusVerifikasi::Disetujui ? 'Disetujui oleh verifikator.' : 'Ditolak oleh verifikator.'),
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal memverifikasi laporan', ['laporan_id' => $laporan->id, 'error' => $e->getMessage()]);
            throw $e;
        }

        try {
            LaporanVerified::dispatch($laporan, $target->value);
        } catch (\Throwable $e) {
            Log::warning('Verifikasi sukses, tetapi event LaporanVerified gagal', ['laporan_id' => $laporan->id, 'error' => $e->getMessage()]);
        }

        try {
            PetaDataService::invalidateCache();
        } catch (\Throwable $e) {
            Log::warning('Verifikasi sukses, tetapi invalidasi cache peta gagal', ['laporan_id' => $laporan->id, 'error' => $e->getMessage()]);
        }
    }
}
