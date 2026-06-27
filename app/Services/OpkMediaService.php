<?php

namespace App\Services;

use App\Models\{OpkLaporan, OpkFoto};
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class OpkMediaService
{
    public function deleteFotos(int $laporanId, array $ids): void
    {
        if (empty($ids)) {
            return;
        }

        $fotos = OpkFoto::where('laporan_id', $laporanId)
            ->whereIn('id', $ids)
            ->get();

        foreach ($fotos as $foto) {
            Storage::disk('public')->delete($foto->path);
            $foto->delete();
        }
    }

    public function uploadFotos(OpkLaporan $laporan, array $files, int $deletedCount = 0): void
    {
        $existingCount = OpkFoto::where('laporan_id', $laporan->id)->count();
        $newCount      = count($files);

        if (($existingCount - $deletedCount + $newCount) > 10) {
            throw new \RuntimeException(
                'Maksimal 10 foto per OPK. Anda memiliki ' . ($existingCount - $deletedCount) . ' foto, mencoba upload ' . $newCount . ' foto baru.'
            );
        }

        $urutanAwal = OpkFoto::where('laporan_id', $laporan->id)->max('urutan') ?? -1;

        foreach ($files as $index => $foto) {
            $namaFile = Str::uuid() . '.' . $foto->getClientOriginalExtension();
            $path     = $foto->storeAs('foto_opk/' . $laporan->id, $namaFile, 'public');

            OpkFoto::create([
                'laporan_id'   => $laporan->id,
                'nama_file'    => $foto->getClientOriginalName(),
                'path'         => $path,
                'is_utama'     => false,
                'urutan'       => $urutanAwal + $index + 1,
                'ukuran_bytes' => $foto->getSize(),
                'mime_type'    => $foto->getMimeType(),
            ]);
        }
    }

    public function setFotoUtama(int $laporanId, int $fotoId): void
    {
        OpkFoto::where('laporan_id', $laporanId)->update(['is_utama' => false]);
        OpkFoto::where('laporan_id', $laporanId)
            ->where('id', $fotoId)
            ->update(['is_utama' => true]);
    }
}
