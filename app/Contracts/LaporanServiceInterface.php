<?php

namespace App\Contracts;

use App\Models\OpkLaporan;

interface LaporanServiceInterface
{
    public function createLaporan(array $validated): OpkLaporan;
    public function uploadFotos(OpkLaporan $laporan, array $files, ?string $keteranganUtama = null): void;
    public function uploadDokumen(OpkLaporan $laporan, \Illuminate\Http\UploadedFile $dokumen): void;
    public function saveVideoLink(OpkLaporan $laporan, ?string $url): void;
}
