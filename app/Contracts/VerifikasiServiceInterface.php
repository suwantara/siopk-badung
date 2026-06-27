<?php

namespace App\Contracts;

use App\Models\{OpkLaporan, User};

interface VerifikasiServiceInterface
{
    public function setujuiLaporan(OpkLaporan $laporan, User $verifikator, ?string $catatan = null): void;
    public function tolakLaporan(OpkLaporan $laporan, User $verifikator, string $alasan, string $catatan): void;
}
