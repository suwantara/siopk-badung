<?php

namespace App\Services;

use App\Models\{OpkLaporan, OpkFoto, OpkDokumen, OpkVideo};
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class LaporanService
{
    public function createLaporan(array $validated): OpkLaporan
    {
        return OpkLaporan::create([
            'kode_laporan'        => OpkLaporan::generateKode(),
            'nama_opk'            => $validated['nama_opk'],
            'kategori_id'         => $validated['kategori_id'],
            'tahun_diketahui'     => $validated['tahun_diketahui'] ?? null,
            'tahun_keterangan'    => $validated['tahun_keterangan'] ?? null,
            'status_pelindungan'  => $validated['status_pelindungan'],
            'kondisi'             => $validated['kondisi'],
            'kecamatan_id'        => $validated['kecamatan_id'],
            'desa_dinas_id'       => $validated['desa_dinas_id'],
            'nama_desa_adat'      => $validated['nama_desa_adat'],
            'banjar_adat'         => $validated['banjar_adat'] ?? null,
            'lokasi_spesifik'     => $validated['lokasi_spesifik'] ?? null,
            'latitude'            => $validated['latitude'] ?? null,
            'longitude'           => $validated['longitude'] ?? null,
            'deskripsi_umum'      => $validated['deskripsi_umum'],
            'sejarah_asal_usul'   => $validated['sejarah_asal_usul'] ?? null,
            'nilai_makna_budaya'  => $validated['nilai_makna_budaya'] ?? null,
            'bahasa_digunakan'    => $validated['bahasa_digunakan'] ?? null,
            'aksara_digunakan'    => $validated['aksara_digunakan'] ?? null,
            'frekuensi_pelaksanaan' => $validated['frekuensi_pelaksanaan'] ?? null,
            'status_kepemilikan'  => $validated['status_kepemilikan'] ?? null,
            'praktisi_nama'       => $validated['praktisi_nama'] ?? null,
            'praktisi_usia'       => $validated['praktisi_usia'] ?? null,
            'praktisi_kontak'     => $validated['praktisi_kontak'] ?? null,
            'tipe_pelapor'        => $validated['tipe_pelapor'],
            'pelapor_nama'        => $validated['pelapor_nama'],
            'pelapor_nik'         => $validated['pelapor_nik'],
            'pelapor_whatsapp'    => $validated['pelapor_whatsapp'],
            'pelapor_email'       => $validated['pelapor_email'] ?? null,
            'link_video'          => $validated['link_video'] ?? null,
            'status_verifikasi'   => 'menunggu',
        ]);
    }

    public function uploadFotos(OpkLaporan $laporan, array $files, ?string $keteranganUtama = null): void
    {
        foreach ($files as $index => $foto) {
            $namaFile = Str::uuid() . '.' . $foto->getClientOriginalExtension();
            $path     = $foto->storeAs('foto_opk/' . $laporan->id, $namaFile, 'public');

            OpkFoto::create([
                'laporan_id'   => $laporan->id,
                'nama_file'    => $foto->getClientOriginalName(),
                'path'         => $path,
                'keterangan'   => $index === 0 ? $keteranganUtama : null,
                'is_utama'     => $index === 0,
                'urutan'       => $index,
                'ukuran_bytes' => $foto->getSize(),
                'mime_type'    => $foto->getMimeType(),
            ]);
        }
    }

    public function uploadDokumen(OpkLaporan $laporan, UploadedFile $dokumen): void
    {
        $namaDok = Str::uuid() . '.' . $dokumen->getClientOriginalExtension();
        $pathDok = $dokumen->storeAs('dokumen_opk/' . $laporan->id, $namaDok, 'public');

        OpkDokumen::create([
            'laporan_id'   => $laporan->id,
            'nama_file'    => $dokumen->getClientOriginalName(),
            'path'         => $pathDok,
            'jenis'        => 'dokumen_pendukung',
            'ukuran_bytes' => $dokumen->getSize(),
        ]);
    }

    public function saveVideoLink(OpkLaporan $laporan, ?string $url): void
    {
        if (!empty($url)) {
            OpkVideo::create([
                'laporan_id'     => $laporan->id,
                'link_eksternal' => $url,
            ]);
        }
    }
}
