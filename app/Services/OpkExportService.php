<?php

namespace App\Services;

use App\Models\OpkLaporan;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer as XlsxWriter;

class OpkExportService
{
    public function exportSingle(OpkLaporan $laporan, bool $includePrivate = false): string
    {
        $laporan->load([
            'kategori', 'kecamatan', 'desaDinas', 'kondisiOpk',
            'fotos', 'dokumens', 'videos',
            'riwayat.user', 'verifikator',
        ]);

        $safeName = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $laporan->nama_opk);
        $filename = "OPK_{$laporan->kode_laporan}_{$safeName}.xlsx";

        $filePath = $this->tempPath($filename);

        $writer = new XlsxWriter;
        $writer->openToFile($filePath);

        $this->writeDataUtama($writer, $laporan, $includePrivate);
        $this->writeFotos($writer, $laporan);
        $this->writeDokumens($writer, $laporan);
        $this->writeVideos($writer, $laporan);
        $this->writeRiwayat($writer, $laporan);
        $this->writeAiAnalysis($writer, $laporan);

        $writer->close();

        return $filePath;
    }

    public function exportBatch(array $filters = [], array $sheets = []): string
    {
        $query = OpkLaporan::with([
            'kategori', 'kecamatan', 'desaDinas', 'kondisiOpk',
            'fotos', 'dokumens', 'videos', 'verifikator',
            'riwayat.user', 'duplikatDari',
        ]);

        if (! empty($filters['status_verifikasi']) && is_array($filters['status_verifikasi'])) {
            $query->whereIn('status_verifikasi', $filters['status_verifikasi']);
        } else {
            $query->disetujui();
        }

        if (! empty($filters['search'])) {
            $query->where('nama_opk', 'like', '%'.$filters['search'].'%');
        }
        if (! empty($filters['kategori_id']) && is_array($filters['kategori_id'])) {
            $query->whereIn('kategori_id', $filters['kategori_id']);
        } elseif (! empty($filters['kategori_id'])) {
            $query->where('kategori_id', $filters['kategori_id']);
        }
        if (! empty($filters['kecamatan_id']) && is_array($filters['kecamatan_id'])) {
            $query->whereIn('kecamatan_id', $filters['kecamatan_id']);
        } elseif (! empty($filters['kecamatan_id'])) {
            $query->where('kecamatan_id', $filters['kecamatan_id']);
        }
        if (! empty($filters['kondisi']) && is_array($filters['kondisi'])) {
            $query->whereIn('kondisi', $filters['kondisi']);
        } elseif (! empty($filters['kondisi'])) {
            $query->where('kondisi', $filters['kondisi']);
        }
        if (! empty($filters['tanggal_dari'])) {
            $query->whereDate('created_at', '>=', $filters['tanggal_dari']);
        }
        if (! empty($filters['tanggal_sampai'])) {
            $query->whereDate('created_at', '<=', $filters['tanggal_sampai']);
        }

        $laporans = $query->latest()->get();

        $timestamp = now()->format('Y-m-d_Hi');
        $filename = "SIOPK_Badung_Export_{$timestamp}.xlsx";
        $filePath = $this->tempPath($filename);

        $writer = new XlsxWriter;
        $writer->openToFile($filePath);

        $this->writeBatchDataUtama($writer, $laporans);

        if ($this->includeSheet($sheets, 'foto')) {
            $this->writeBatchFotos($writer, $laporans);
        }
        if ($this->includeSheet($sheets, 'dokumen')) {
            $this->writeBatchDokumens($writer, $laporans);
        }
        if ($this->includeSheet($sheets, 'video')) {
            $this->writeBatchVideos($writer, $laporans);
        }
        if ($this->includeSheet($sheets, 'riwayat')) {
            $this->writeBatchRiwayat($writer, $laporans);
        }
        if ($this->includeSheet($sheets, 'ai')) {
            $this->writeBatchAi($writer, $laporans);
        }

        $writer->close();

        return $filePath;
    }

    // ===== Single OPK Sheets =====

    private function writeDataUtama(XlsxWriter $writer, OpkLaporan $l, bool $includePrivate): void
    {
        $sheet = $writer->getCurrentSheet();
        $sheet->setName('Data Utama');

        $rows = [
            ['Data Utama OPK', ''],
            ['Kode Laporan', $l->kode_laporan],
            ['Nama OPK', $l->nama_opk],
            ['Kategori', $l->kategori?->nomor.' - '.$l->kategori?->nama],
            ['Kondisi', $l->kondisiOpk?->nama ?? $l->kondisi],
            ['Status Pelindungan', $this->label($l->status_pelindungan)],
            ['Status Verifikasi', $this->label($l->status_verifikasi)],
            ['Tahun Diketahui', $l->tahun_diketahui],
            ['Tahun Keterangan', $l->tahun_keterangan],
            ['Kecamatan', $l->kecamatan?->nama],
            ['Desa Dinas', $l->desaDinas?->nama],
            ['Desa Adat', $l->nama_desa_adat],
            ['Banjar Adat', $l->banjar_adat],
            ['Lokasi Spesifik', $l->lokasi_spesifik],
            ['Latitude', $l->latitude],
            ['Longitude', $l->longitude],
            ['Bahasa Digunakan', $l->bahasa_digunakan],
            ['Aksara Digunakan', $l->aksara_digunakan],
            ['Frekuensi Pelaksanaan', $this->label($l->frekuensi_pelaksanaan)],
            ['Status Kepemilikan', $this->label($l->status_kepemilikan)],
            ['Link Video', $l->link_video],
            ['Link Dokumen Eksternal', $l->link_dokumen_eksternal],
            ['', ''],
            ['Deskripsi Umum', $l->deskripsi_umum],
            ['Sejarah & Asal-Usul', $l->sejarah_asal_usul],
            ['Nilai & Makna Budaya', $l->nilai_makna_budaya],
            ['', ''],
            ['Praktisi', ''],
            ['Nama', $l->praktisi_nama],
            ['Usia', $l->praktisi_usia ? $l->praktisi_usia.' tahun' : ''],
            ['Kontak', $l->praktisi_kontak],
            ['', ''],
            ['Pelapor', ''],
            ['Tipe Pelapor', $this->label($l->tipe_pelapor)],
            ['Nama Pelapor', $l->pelapor_nama],
            ['Verifikator', $l->verifikator?->name],
            ['Tanggal Verifikasi', $l->tanggal_verifikasi?->format('d M Y H:i')],
            ['Catatan Verifikasi', $l->catatan_verifikasi],
            ['Tanggal Lapor', $l->created_at->format('d M Y H:i')],
            ['Terakhir Diperbarui', $l->updated_at->format('d M Y H:i')],
        ];

        if ($includePrivate) {
            $rows[] = ['', ''];
            $rows[] = ['Data Pribadi (Rahasia)', ''];
            $rows[] = ['NIK Pelapor', $l->pelapor_nik ? "'".$l->pelapor_nik : ''];
            $rows[] = ['WhatsApp Pelapor', $l->pelapor_whatsapp];
            $rows[] = ['Email Pelapor', $l->pelapor_email];
        }

        foreach ($rows as $row) {
            $writer->addRow(Row::fromValues($row));
        }
    }

    private function writeFotos(XlsxWriter $writer, OpkLaporan $l): void
    {
        $sheet = $writer->addNewSheetAndMakeItCurrent();
        $sheet->setName('Foto');

        $writer->addRow(Row::fromValues(['Foto OPK: '.$l->nama_opk]));
        $writer->addRow(Row::fromValues([]));
        $writer->addRow(Row::fromValues(['No', 'Utama', 'Keterangan', 'Nama File', 'Ukuran', 'Tipe', 'Path']));

        $i = 1;
        foreach ($l->fotos as $foto) {
            $writer->addRow(Row::fromValues([
                $i++,
                $foto->is_utama ? 'YA' : '-',
                $foto->keterangan,
                $foto->nama_file,
                $this->formatBytes($foto->ukuran_bytes),
                $foto->mime_type,
                $foto->path,
            ]));
        }
    }

    private function writeDokumens(XlsxWriter $writer, OpkLaporan $l): void
    {
        $sheet = $writer->addNewSheetAndMakeItCurrent();
        $sheet->setName('Dokumen');

        $writer->addRow(Row::fromValues(['Dokumen OPK: '.$l->nama_opk]));
        $writer->addRow(Row::fromValues([]));
        $writer->addRow(Row::fromValues(['No', 'Judul', 'Jenis', 'Nama File', 'Ukuran', 'Path']));

        $i = 1;
        foreach ($l->dokumens as $dok) {
            $writer->addRow(Row::fromValues([
                $i++,
                $dok->judul,
                $dok->jenis,
                $dok->nama_file,
                $this->formatBytes($dok->ukuran_bytes),
                $dok->path,
            ]));
        }
    }

    private function writeVideos(XlsxWriter $writer, OpkLaporan $l): void
    {
        $sheet = $writer->addNewSheetAndMakeItCurrent();
        $sheet->setName('Video');

        $writer->addRow(Row::fromValues(['Video OPK: '.$l->nama_opk]));
        $writer->addRow(Row::fromValues([]));
        $writer->addRow(Row::fromValues(['No', 'Keterangan', 'Nama File', 'Link Eksternal', 'Path']));

        $i = 1;
        foreach ($l->videos as $vid) {
            $writer->addRow(Row::fromValues([
                $i++,
                $vid->keterangan,
                $vid->nama_file,
                $vid->link_eksternal,
                $vid->path,
            ]));
        }
    }

    private function writeRiwayat(XlsxWriter $writer, OpkLaporan $l): void
    {
        $sheet = $writer->addNewSheetAndMakeItCurrent();
        $sheet->setName('Riwayat Status');

        $writer->addRow(Row::fromValues(['Riwayat Status OPK: '.$l->nama_opk]));
        $writer->addRow(Row::fromValues([]));
        $writer->addRow(Row::fromValues(['No', 'Tanggal', 'Status Baru', 'Status Lama', 'Oleh', 'Catatan']));

        $i = 1;
        foreach ($l->riwayat as $rw) {
            $writer->addRow(Row::fromValues([
                $i++,
                $rw->created_at->format('d M Y H:i'),
                $this->label($rw->status_baru),
                $this->label($rw->status_lama),
                $rw->user?->name,
                $rw->catatan,
            ]));
        }
    }

    private function writeAiAnalysis(XlsxWriter $writer, OpkLaporan $l): void
    {
        $sheet = $writer->addNewSheetAndMakeItCurrent();
        $sheet->setName('AI Analysis');

        $writer->addRow(Row::fromValues(['AI Analysis OPK: '.$l->nama_opk]));
        $writer->addRow(Row::fromValues([]));

        $rows = [
            ['Urgency Score', $l->ai_urgency_score],
            ['Duplikat Score', $l->ai_duplikat_score],
            ['Duplikat Dari', $l->duplikatDari?->kode_laporan ? $l->duplikatDari->kode_laporan.' - '.$l->duplikatDari->nama_opk : '-'],
            ['Rekomendasi AI', $l->ai_rekomendasi],
        ];

        foreach ($rows as $row) {
            $writer->addRow(Row::fromValues($row));
        }
    }

    // ===== Batch OPK Sheets =====

    private function writeBatchDataUtama(XlsxWriter $writer, $laporans): void
    {
        $sheet = $writer->getCurrentSheet();
        $sheet->setName('Data Utama');

        $writer->addRow(Row::fromValues([
            'No', 'Kode Laporan', 'Nama OPK', 'Kategori', 'Kondisi',
            'Status Pelindungan', 'Status Verifikasi',
            'Tahun Diketahui', 'Tahun Keterangan',
            'Kecamatan', 'Desa Dinas', 'Desa Adat', 'Banjar Adat',
            'Lokasi Spesifik', 'Latitude', 'Longitude',
            'Bahasa', 'Aksara', 'Frekuensi', 'Kepemilikan',
            'Praktisi', 'Usia Praktisi',
            'Tipe Pelapor', 'Nama Pelapor',
            'Verifikator', 'Tgl Verifikasi',
            'AI Urgency', 'AI Duplikat', 'Tanggal Lapor',
        ]));

        $i = 1;
        foreach ($laporans as $l) {
            $writer->addRow(Row::fromValues([
                $i++,
                $l->kode_laporan,
                $l->nama_opk,
                $l->kategori?->nomor.' - '.$l->kategori?->nama,
                $l->kondisiOpk?->nama ?? $l->kondisi,
                $this->label($l->status_pelindungan),
                $this->label($l->status_verifikasi),
                $l->tahun_diketahui,
                $l->tahun_keterangan,
                $l->kecamatan?->nama,
                $l->desaDinas?->nama,
                $l->nama_desa_adat,
                $l->banjar_adat,
                $l->lokasi_spesifik,
                $l->latitude,
                $l->longitude,
                $l->bahasa_digunakan,
                $l->aksara_digunakan,
                $this->label($l->frekuensi_pelaksanaan),
                $this->label($l->status_kepemilikan),
                $l->praktisi_nama,
                $l->praktisi_usia,
                $this->label($l->tipe_pelapor),
                $l->pelapor_nama,
                $l->verifikator?->name,
                $l->tanggal_verifikasi?->format('d M Y'),
                $l->ai_urgency_score,
                $l->ai_duplikat_score,
                $l->created_at->format('d M Y'),
            ]));
        }
    }

    private function writeBatchFotos(XlsxWriter $writer, $laporans): void
    {
        $sheet = $writer->addNewSheetAndMakeItCurrent();
        $sheet->setName('Foto');

        $writer->addRow(Row::fromValues(['No', 'Kode Laporan', 'Nama OPK', 'Utama', 'Keterangan', 'Nama File', 'Ukuran', 'Tipe']));

        $no = 1;
        foreach ($laporans as $l) {
            foreach ($l->fotos as $foto) {
                $writer->addRow(Row::fromValues([
                    $no++,
                    $l->kode_laporan,
                    $l->nama_opk,
                    $foto->is_utama ? 'YA' : '-',
                    $foto->keterangan,
                    $foto->nama_file,
                    $this->formatBytes($foto->ukuran_bytes),
                    $foto->mime_type,
                ]));
            }
        }
    }

    private function writeBatchDokumens(XlsxWriter $writer, $laporans): void
    {
        $sheet = $writer->addNewSheetAndMakeItCurrent();
        $sheet->setName('Dokumen');

        $writer->addRow(Row::fromValues(['No', 'Kode Laporan', 'Nama OPK', 'Judul', 'Jenis', 'Nama File', 'Ukuran']));

        $no = 1;
        foreach ($laporans as $l) {
            foreach ($l->dokumens as $dok) {
                $writer->addRow(Row::fromValues([
                    $no++,
                    $l->kode_laporan,
                    $l->nama_opk,
                    $dok->judul,
                    $dok->jenis,
                    $dok->nama_file,
                    $this->formatBytes($dok->ukuran_bytes),
                ]));
            }
        }
    }

    private function writeBatchVideos(XlsxWriter $writer, $laporans): void
    {
        $sheet = $writer->addNewSheetAndMakeItCurrent();
        $sheet->setName('Video');

        $writer->addRow(Row::fromValues(['No', 'Kode Laporan', 'Nama OPK', 'Keterangan', 'Nama File', 'Link Eksternal']));

        $no = 1;
        foreach ($laporans as $l) {
            foreach ($l->videos as $vid) {
                $writer->addRow(Row::fromValues([
                    $no++,
                    $l->kode_laporan,
                    $l->nama_opk,
                    $vid->keterangan,
                    $vid->nama_file,
                    $vid->link_eksternal,
                ]));
            }
        }
    }

    // ===== Helpers =====

    private function includeSheet(array $sheets, string $key): bool
    {
        if (empty($sheets)) {
            return true;
        }

        return in_array($key, $sheets, true);
    }

    private function writeBatchRiwayat(XlsxWriter $writer, $laporans): void
    {
        $sheet = $writer->addNewSheetAndMakeItCurrent();
        $sheet->setName('Riwayat Status');

        $writer->addRow(Row::fromValues(['No', 'Kode Laporan', 'Nama OPK', 'Tanggal', 'Status Baru', 'Status Lama', 'Oleh', 'Catatan']));

        $no = 1;
        foreach ($laporans as $l) {
            foreach ($l->riwayat as $rw) {
                $writer->addRow(Row::fromValues([
                    $no++,
                    $l->kode_laporan,
                    $l->nama_opk,
                    $rw->created_at->format('d M Y H:i'),
                    $this->label($rw->status_baru),
                    $this->label($rw->status_lama),
                    $rw->user?->name,
                    $rw->catatan,
                ]));
            }
        }
    }

    private function writeBatchAi(XlsxWriter $writer, $laporans): void
    {
        $sheet = $writer->addNewSheetAndMakeItCurrent();
        $sheet->setName('AI Analysis');

        $writer->addRow(Row::fromValues(['No', 'Kode Laporan', 'Nama OPK', 'Urgency Score', 'Duplikat Score', 'Duplikat Dari', 'Rekomendasi AI']));

        $no = 1;
        foreach ($laporans as $l) {
            $writer->addRow(Row::fromValues([
                $no++,
                $l->kode_laporan,
                $l->nama_opk,
                $l->ai_urgency_score,
                $l->ai_duplikat_score,
                $l->duplikatDari?->kode_laporan ? $l->duplikatDari->kode_laporan.' - '.$l->duplikatDari->nama_opk : '',
                $l->ai_rekomendasi,
            ]));
        }
    }

    private function tempPath(string $filename): string
    {
        $dir = storage_path('app/temp');
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        return $dir.'/'.$filename;
    }

    private function label(?string $value): string
    {
        if (empty($value)) {
            return '';
        }

        return ucwords(str_replace('_', ' ', $value));
    }

    private function formatBytes(?int $bytes): string
    {
        if (! $bytes) {
            return '';
        }

        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 1).' MB';
        }
        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 1).' KB';
        }

        return $bytes.' B';
    }

    public function exportCsv(): array
    {
        $filename = 'siopk-badung-'.now()->format('Y-m-d-His').'.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () {
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($out, [
                'Kode Laporan', 'Nama OPK', 'Kategori', 'Kondisi',
                'Kecamatan', 'Desa Dinas', 'Desa Adat',
                'Latitude', 'Longitude',
                'Tahun Diketahui', 'Status Pelindungan',
                'Frekuensi Pelaksanaan', 'Status Kepemilikan',
                'Praktisi', 'Usia Praktisi',
                'Urgensi (AI)', 'Rekomendasi AI',
                'Tanggal Lapor', 'Status Verifikasi',
            ]);

            OpkLaporan::with(['kategori', 'kecamatan', 'desaDinas', 'verifikator'])
                ->disetujui()
                ->cursor()
                ->each(function ($o) use ($out) {
                    fputcsv($out, [
                        $o->kode_laporan,
                        $o->nama_opk,
                        $o->kategori?->nama ?? '',
                        $o->kondisi,
                        $o->kecamatan?->nama ?? '',
                        $o->desaDinas?->nama ?? '',
                        $o->nama_desa_adat ?? '',
                        $o->latitude,
                        $o->longitude,
                        $o->tahun_diketahui ?? '',
                        $o->status_pelindungan ?? '',
                        $o->frekuensi_pelaksanaan ?? '',
                        $o->status_kepemilikan ?? '',
                        $o->praktisi_nama ?? '',
                        $o->praktisi_usia ?? '',
                        $o->ai_urgency_score ?? '',
                        $o->ai_rekomendasi ?? '',
                        $o->created_at->format('Y-m-d H:i'),
                        $o->status_verifikasi,
                    ]);
                });

            fclose($out);
        };

        return [$headers, $callback];
    }
}
