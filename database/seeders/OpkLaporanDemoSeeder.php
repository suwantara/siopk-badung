<?php

namespace Database\Seeders;

use App\Models\{OpkLaporan, Kecamatan, DesaDinas, User};
use Illuminate\Database\Seeder;

class OpkLaporanDemoSeeder extends Seeder
{
    public function run(): void
    {
        $kecamatans = Kecamatan::all();
        $desasByKec = DesaDinas::all()->groupBy('kecamatan_id');
        $verifikatorId = User::where('role', 'admin')->value('id') ?? 1;
        $now = now();

        $namaBalinese = [
            'I Wayan', 'I Made', 'I Nyoman', 'I Ketut', 'I Gusti', 'Anak Agung',
            'Ni Wayan', 'Ni Made', 'Ni Nyoman', 'Ni Ketut', 'Ni Putu', 'Ni Kadek',
            'Ni Luh', 'Ni Komang', 'Ida Bagus', 'Ida Ayu',
        ];
        $namaBelakang = [
            'Sudarsana', 'Putra', 'Santika', 'Wardana', 'Wirawan', 'Astawa',
            'Ardika', 'Pradnyana', 'Sukawati', 'Darmawan', 'Artawan', 'Widiarta',
            'Subrata', 'Sumerta', 'Yasa', 'Adnyana', 'Mahendra', 'Pasek',
        ];

        $opkNamaPrefixes = [
            1  => ['Cerita Rakyat', 'Legenda', 'Folklor', 'Pantun', 'Tutur', 'Mitos'],
            2  => ['Lontar', 'Prasasti', 'Manuskrip', 'Naskah Kuno', 'Babad', 'Primbon'],
            3  => ['Adat Istiadat', 'Hukum Adat', 'Tata Kelola', 'Sistem Nilai', 'Norma Adat'],
            4  => ['Upacara', 'Ritual', 'Ritus', 'Sesaji', 'Persembahan', 'Prosesi'],
            5  => ['Pengobatan', 'Jamu', 'Herbal', 'Ramuan', 'Pengetahuan', 'Kearifan Lokal'],
            6  => ['Subak', 'Tenun', 'Gamelan', 'Arsitektur', 'Anyaman', 'Pengolahan'],
            7  => ['Tari', 'Seni Lukis', 'Seni Ukir', 'Musik Tradisi', 'Wayang', 'Topeng'],
            8  => ['Bahasa', 'Aksara', 'Dialek', 'Sastra Lisan', 'Kosakata'],
            9  => ['Permainan', 'Dolanan', 'Lomba Adat', 'Adu Ketangkasan'],
            10 => ['Olahraga', 'Adu Fisik', 'Bela Diri', 'Gulat Tradisi', 'Pencak'],
        ];

        $opkNamaObjek = [
            1  => ['Danau Beratan', 'Gunung Agung', 'Pura Besakih', 'Sungai Ayung', 'Pantai Seseh', 'Bukit Campuhan', 'Tanah Lot', 'Goa Gajah'],
            2  => ['Kuno', 'Bertuah', 'Sakral', 'Pusaka', 'Leluhur', 'Keramat'],
            3  => ['Desa', 'Banjar', 'Sekaa', 'Krama', 'Prajuru'],
            4  => ['Ngaben', 'Melasti', 'Odalan', 'Ngusaba', 'Mepandes', 'Piodalan', 'Nyekah', 'Ngenteg Linggih'],
            5  => ['Usada', 'Tradisional', 'Boreh', 'Loloh', 'Sembar', 'Otonan'],
            6  => ['Tradisional', 'Khas', 'Pertanian', 'Irigasi', 'Peralatan', 'Kerajinan'],
            7  => ['Klasik', 'Kontemporer', 'Sakral', 'Wali', 'Bebali', 'Balih-balihan'],
            8  => ['Bali', 'Daerah', 'Kawi', 'Sansekerta', 'Pesisir', 'Pegunungan'],
            9  => ['Tradisional', 'Rakyat', 'Anak-anak', 'Dewasa', 'Beregu'],
            10 => ['Tradisional', 'Pesisir', 'Pegunungan', 'Sawah'],
        ];

        $deskripsiUmum = [
            'kritis'  => ['Kondisi sangat memprihatinkan, praktisi tinggal sedikit, belum ada dokumentasi.', 'Hampir punah, hanya tersisa sedikit pelaku yang sudah berusia lanjut.', 'Terancam oleh modernisasi, generasi muda tidak berminat.', 'Butuh penanganan segera, dokumentasi dan regenerasi mendesak.'],
            'waspada' => ['Mulai jarang dilakukan, perlu perhatian khusus untuk pelestarian.', 'Masih bertahan namun regenerasi mulai menurun.', 'Terpengaruh perubahan sosial dan ekonomi masyarakat.', 'Perlu program revitalisasi untuk mempertahankan keberlangsungan.'],
            'baik'    => ['Masih lestari dan dipraktikkan secara rutin oleh masyarakat.', 'Regenerasi berjalan baik, didukung komunitas yang kuat.', 'Sudah terdokumentasi dengan baik, menjadi ikon budaya setempat.', 'Aktif dipergelarkan dalam berbagai event budaya dan keagamaan.'],
        ];

        $sejarahUmum = [
            'Diwariskan secara turun-temurun sejak zaman kerajaan.',
            'Berasal dari tradisi leluhur yang sudah berlangsung ratusan tahun.',
            'Diciptakan pada masa kolonial sebagai bentuk perlawanan budaya.',
            'Muncul dari kearifan lokal petani dalam beradaptasi dengan alam.',
            'Berkembang dari ritual keagamaan Hindu Bali yang sudah ada sejak abad ke-15.',
            'Merupakan percampuran budaya Jawa kuno dan Bali asli.',
        ];

        $kondisiDist   = ['kritis', 'kritis', 'waspada', 'waspada', 'waspada', 'baik', 'baik', 'baik', 'baik', 'baik'];
        $statusDist    = ['disetujui', 'disetujui', 'disetujui', 'disetujui', 'disetujui', 'disetujui', 'disetujui', 'menunggu', 'review_dinas', 'ditolak'];
        $statusPelDist = ['belum_terdaftar', 'belum_terdaftar', 'belum_terdaftar', 'sudah_didata_dinas', 'sudah_didata_dinas', 'sk_kabupaten', 'sk_kabupaten', 'sk_provinsi', 'wbtb_nasional'];
        $frekuensi     = ['rutin_harian', 'rutin_mingguan', 'rutin_bulanan', 'rutin_6bulanan', 'rutin_tahunan', 'tidak_ada'];
        $kepemilikan   = ['desa_adat', 'desa_adat', 'desa_adat', 'pribadi_keluarga', 'pribadi_keluarga', 'negara_pemerintah', 'tidak_jelas'];
        $tipePelapor   = ['masyarakat', 'masyarakat', 'masyarakat', 'tokoh_adat', 'tokoh_adat', 'petugas_dinas'];

        $records = [];

        for ($i = 0; $i < 200; $i++) {
            $kategoriId = rand(1, 10);
            $kecamatan  = $kecamatans->random();
            $desa       = $desasByKec->get($kecamatan->id)?->random();
            $kondisi    = $kondisiDist[array_rand($kondisiDist)];
            $status     = $statusDist[array_rand($statusDist)];
            $statusPel  = $statusPelDist[array_rand($statusPelDist)];

            $prefix   = $opkNamaPrefixes[$kategoriId][array_rand($opkNamaPrefixes[$kategoriId])];
            $objek    = $opkNamaObjek[$kategoriId][array_rand($opkNamaObjek[$kategoriId])];
            $namaOpk  = $prefix . ' ' . $objek . ' ' . ($kecamatan->nama);

            $tahun  = 2000 + rand(0, 24);
            $lat    = -8.30 - (rand(0, 600) / 1000);
            $lng    = 115.08 + (rand(0, 150) / 1000);

            $praktisiNama    = $namaBalinese[array_rand($namaBalinese)] . ' ' . $namaBelakang[array_rand($namaBelakang)];
            $praktisiUsia    = rand(25, 85);
            $pelaporNama     = $namaBalinese[array_rand($namaBalinese)] . ' ' . $namaBelakang[array_rand($namaBelakang)];
            $pelaporNik      = '5103' . str_pad(rand(1, 6), 2, '0', STR_PAD_LEFT) . str_pad(rand(1, 31), 2, '0', STR_PAD_LEFT) . str_pad(rand(1, 12), 2, '0', STR_PAD_LEFT) . str_pad(rand(40, 99), 2, '0', STR_PAD_LEFT) . str_pad($i + 1, 4, '0', STR_PAD_LEFT);

            $record = [
                'kode_laporan'       => 'SIOPK-2025-' . str_pad($i + 1, 5, '0', STR_PAD_LEFT),
                'nama_opk'           => $namaOpk,
                'kategori_id'        => $kategoriId,
                'tahun_diketahui'    => $tahun,
                'status_pelindungan' => $statusPel,
                'kondisi'            => $kondisi,
                'kecamatan_id'       => $kecamatan->id,
                'desa_dinas_id'      => $desa?->id,
                'nama_desa_adat'     => 'Desa Adat ' . ($desa?->nama ?? $kecamatan->nama),
                'latitude'           => round($lat, 6),
                'longitude'          => round($lng, 6),
                'deskripsi_umum'     => $deskripsiUmum[$kondisi][array_rand($deskripsiUmum[$kondisi])],
                'sejarah_asal_usul'  => $sejarahUmum[array_rand($sejarahUmum)],
                'nilai_makna_budaya' => 'Mengandung nilai-nilai luhur yang mencerminkan kearifan lokal masyarakat Bali.',
                'frekuensi_pelaksanaan' => $frekuensi[array_rand($frekuensi)],
                'status_kepemilikan' => $kepemilikan[array_rand($kepemilikan)],
                'praktisi_nama'      => $praktisiNama,
                'praktisi_usia'      => $praktisiUsia,
                'praktisi_kontak'    => '08' . rand(1000000000, 9999999999),
                'tipe_pelapor'       => $tipePelapor[array_rand($tipePelapor)],
                'pelapor_nama'       => $pelaporNama,
                'pelapor_nik'        => $pelaporNik,
                'pelapor_whatsapp'   => '08' . rand(1000000000, 9999999999),
                'pelapor_email'      => strtolower(str_replace(' ', '', $pelaporNama)) . rand(1, 99) . '@gmail.com',
                'status_verifikasi'  => $status,
                'created_at'         => $now->copy()->subDays(rand(1, 365))->subHours(rand(0, 23)),
            ];

            $record['ai_urgency_score']   = null;
            $record['ai_rekomendasi']      = null;
            $record['diverifikasi_oleh']   = null;
            $record['tanggal_verifikasi']  = null;
            $record['catatan_verifikasi']  = null;
            $record['ai_duplikat_score']   = null;
            $record['ai_duplikat_of']      = null;
            $record['link_video']          = null;
            $record['link_dokumen_eksternal'] = null;

            if ($status === 'disetujui') {
                $record['diverifikasi_oleh']   = $verifikatorId;
                $record['tanggal_verifikasi']  = $now->copy()->subDays(rand(1, 90));
                $record['catatan_verifikasi']  = 'Diverifikasi oleh admin Dinas Kebudayaan.';
                $record['ai_urgency_score']    = round($kondisi === 'kritis' ? rand(70, 99) / 10 : ($kondisi === 'waspada' ? rand(40, 69) / 10 : rand(5, 39) / 10), 1);
                $record['ai_rekomendasi']      = $kondisi === 'kritis' ? 'Prioritas tinggi. Segera lakukan dokumentasi dan regenerasi.' : ($kondisi === 'waspada' ? 'Pantau berkala, siapkan program revitalisasi.' : 'Pertahankan dokumentasi dan promosi budaya.');
            }

            $record['updated_at'] = $record['created_at'];
            $records[] = $record;
        }

        OpkLaporan::insert($records);

        $disetujui = collect($records)->where('status_verifikasi', 'disetujui')->count();
        $this->command->info("Seeded 200 OPK laporan: {$disetujui} disetujui, " . (200 - $disetujui) . ' lainnya — di ' . $kecamatans->count() . ' kecamatan.');
    }
}

