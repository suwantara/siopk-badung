<?php

namespace App\Services;

use App\Helpers\CacheKeys;
use App\Models\DesaDinas;
use App\Models\Kecamatan;
use App\Models\KondisiOpk;
use App\Models\OpkCategory;
use App\Models\OpkLaporan;
use App\Models\OpkRiwayatStatus;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Reader\CSV\Options as CsvOptions;
use OpenSpout\Reader\CSV\Reader as CsvReader;
use OpenSpout\Reader\XLSX\Reader as XlsxReader;
use OpenSpout\Writer\XLSX\Writer as XlsxWriter;

/**
 * Impor massal data OPK dari CSV/XLSX.
 *
 * Prinsip desain agar aman untuk ~1000+ baris:
 * - File dibaca streaming (OpenSpout), tidak memuat seluruh file ke memori.
 * - Baris divalidasi dulu semua, baru di-insert per chunk 100 baris dalam satu transaksi.
 * - Bulk insert (bukan Eloquent create per baris) sehingga tidak memicu event/observer,
 *   tidak mengirim WhatsApp, dan tidak menjalankan analisis AI.
 * - Dibatasi MAX_ROWS baris per file; proses dilindungi cache lock di controller.
 */
class OpkImportService
{
    public const MAX_ROWS = 2000;

    public const CHUNK_SIZE = 100;

    private const ERROR_DIR = 'app/imports';

    private const TEMP_DIR = 'app/temp';

    private const STATUS_PELINDUNGAN = [
        'belum_terdaftar', 'sudah_didata_dinas', 'sk_kabupaten', 'sk_provinsi', 'wbtb_nasional',
    ];

    private const FREKUENSI = [
        'rutin_harian', 'rutin_mingguan', 'rutin_bulanan', 'rutin_6bulanan',
        'rutin_tahunan', 'langka', 'sangat_langka', 'tidak_ada',
    ];

    private const KEPEMILIKAN = [
        'desa_adat', 'pura_keagamaan', 'pribadi_keluarga', 'negara_pemerintah', 'tidak_jelas',
    ];

    private const TIPE_PELAPOR = ['masyarakat', 'tokoh_adat', 'petugas_dinas'];

    private const STATUS_VERIFIKASI_IMPORT = ['disetujui', 'menunggu'];

    /** Alias nilai agar input manusiawi tetap diterima. */
    private const VALUE_ALIASES = [
        'rutin_6_bulanan' => 'rutin_6bulanan',
        '6_bulanan' => 'rutin_6bulanan',
        'enam_bulanan' => 'rutin_6bulanan',
        'tiap_hari' => 'rutin_harian',
        'harian' => 'rutin_harian',
        'mingguan' => 'rutin_mingguan',
        'bulanan' => 'rutin_bulanan',
        'tahunan' => 'rutin_tahunan',
    ];

    /**
     * Definisi kolom template. Label dipilih agar slug-nya == key
     * (kecuali yang punya alias eksplisit).
     *
     * @var array<string, array{label: string, required: bool, aliases: list<string>}>
     */
    private const COLUMNS = [
        'nama_opk' => ['label' => 'Nama OPK', 'required' => true, 'aliases' => []],
        'kategori' => ['label' => 'Kategori', 'required' => true, 'aliases' => ['jenis', 'jenis_opk', 'kategori_id']],
        'kecamatan' => ['label' => 'Kecamatan', 'required' => true, 'aliases' => []],
        'desa_dinas' => ['label' => 'Desa Dinas', 'required' => true, 'aliases' => ['desa']],
        'nama_desa_adat' => ['label' => 'Nama Desa Adat', 'required' => true, 'aliases' => ['desa_adat']],
        'banjar_adat' => ['label' => 'Banjar Adat', 'required' => false, 'aliases' => []],
        'lokasi_spesifik' => ['label' => 'Lokasi Spesifik', 'required' => false, 'aliases' => []],
        'latitude' => ['label' => 'Latitude', 'required' => false, 'aliases' => ['lat']],
        'longitude' => ['label' => 'Longitude', 'required' => false, 'aliases' => ['lng', 'long']],
        'tahun_diketahui' => ['label' => 'Tahun Diketahui', 'required' => false, 'aliases' => ['tahun']],
        'tahun_keterangan' => ['label' => 'Tahun Keterangan', 'required' => false, 'aliases' => []],
        'status_pelindungan' => ['label' => 'Status Pelindungan', 'required' => false, 'aliases' => []],
        'kondisi' => ['label' => 'Kondisi', 'required' => false, 'aliases' => []],
        'deskripsi_umum' => ['label' => 'Deskripsi Umum', 'required' => true, 'aliases' => ['deskripsi']],
        'sejarah_asal_usul' => ['label' => 'Sejarah Asal Usul', 'required' => false, 'aliases' => ['sejarah']],
        'nilai_makna_budaya' => ['label' => 'Nilai Makna Budaya', 'required' => false, 'aliases' => []],
        'bahasa_digunakan' => ['label' => 'Bahasa Digunakan', 'required' => false, 'aliases' => ['bahasa']],
        'aksara_digunakan' => ['label' => 'Aksara Digunakan', 'required' => false, 'aliases' => ['aksara']],
        'frekuensi_pelaksanaan' => ['label' => 'Frekuensi Pelaksanaan', 'required' => false, 'aliases' => ['frekuensi']],
        'status_kepemilikan' => ['label' => 'Status Kepemilikan', 'required' => false, 'aliases' => ['kepemilikan']],
        'praktisi_nama' => ['label' => 'Nama Praktisi', 'required' => false, 'aliases' => ['praktisi_nama', 'praktisi']],
        'praktisi_usia' => ['label' => 'Usia Praktisi', 'required' => false, 'aliases' => ['usia_praktisi', 'usia']],
        'praktisi_kontak' => ['label' => 'Kontak Praktisi', 'required' => false, 'aliases' => ['kontak_praktisi', 'kontak']],
        'tipe_pelapor' => ['label' => 'Tipe Pelapor', 'required' => false, 'aliases' => []],
        'pelapor_nama' => ['label' => 'Nama Pelapor', 'required' => true, 'aliases' => ['nama_pelapor']],
        'pelapor_nik' => ['label' => 'NIK Pelapor', 'required' => false, 'aliases' => ['nik', 'nik_pelapor']],
        'pelapor_whatsapp' => ['label' => 'WhatsApp Pelapor', 'required' => false, 'aliases' => ['whatsapp', 'wa_pelapor', 'no_wa']],
        'pelapor_email' => ['label' => 'Email Pelapor', 'required' => false, 'aliases' => ['email']],
        'status_verifikasi' => ['label' => 'Status Verifikasi', 'required' => false, 'aliases' => []],
        'link_video' => ['label' => 'Link Video', 'required' => false, 'aliases' => []],
        'link_dokumen_eksternal' => ['label' => 'Link Dokumen Eksternal', 'required' => false, 'aliases' => ['link_dokumen']],
    ];

    /**
     * Daftar kolom template (label + wajib) untuk ditampilkan di UI.
     *
     * @return array<string, array{label: string, required: bool}>
     */
    public static function columns(): array
    {
        return array_map(
            fn ($col) => ['label' => $col['label'], 'required' => $col['required']],
            self::COLUMNS
        );
    }

    /**
     * Proses impor. Mengembalikan ringkasan hasil (tidak melempar exception untuk
     * kesalahan file yang bisa diprediksi — semua dilaporkan lewat array hasil).
     */
    public function import(UploadedFile $file, User $user, bool $dryRun = false, bool $skipInvalid = false): array
    {
        $result = [
            'dry_run' => $dryRun,
            'skip_invalid' => $skipInvalid,
            'total' => 0,
            'total_read' => 0,
            'blank' => 0,
            'valid' => 0,
            'invalid' => 0,
            'imported' => 0,
            'schedule_hint' => null,
            'errors' => [],
            'error_file' => null,
            'fatal' => null,
        ];

        $path = $file->getRealPath();
        $ext = Str::lower($file->getClientOriginalExtension());

        try {
            [$headerMap, $rows] = $this->parseFile($path, $ext);
        } catch (\Throwable $e) {
            $result['fatal'] = 'File tidak bisa dibaca: '.$e->getMessage();

            return $result;
        }

        if ($headerMap === null) {
            $result['fatal'] = 'Format file tidak dikenali. Gunakan template yang disediakan.';

            return $result;
        }

        $missing = [];
        foreach (self::COLUMNS as $key => $col) {
            if ($col['required'] && ! in_array($key, $headerMap, true)) {
                $missing[] = $col['label'];
            }
        }
        if ($missing) {
            $result['fatal'] = 'Kolom wajib tidak ditemukan: '.implode(', ', $missing).'. Unduh template terbaru.';

            return $result;
        }

        $lookup = $this->buildLookups();

        $validRows = [];
        $errors = [];
        $seen = [];

        foreach ($rows as $rowNumber => $cells) {
            $result['total_read']++;
            $mapped = $this->mapRow($headerMap, $cells);

            // Lewati baris yang seluruh kolomnya kosong (mis. baris kosong di template)
            if (! array_filter($mapped, fn ($value) => $value !== '')) {
                $result['blank']++;

                continue;
            }

            $result['total']++;

            if ($result['total'] > self::MAX_ROWS) {
                $result['fatal'] = 'Jumlah baris melebihi batas '.number_format(self::MAX_ROWS, 0, ',', '.').' per file. Silakan pecah file menjadi beberapa bagian.';
                $result['invalid'] = count($errors);
                $result['errors'] = array_slice($errors, 0, 100);
                if ($errors) {
                    $result['error_file'] = $this->writeErrorFile($errors);
                }

                return $result;
            }

            [$data, $messages] = $this->validateRow($mapped, $rowNumber, $lookup, $seen);

            if ($messages) {
                $errors[] = ['row' => $rowNumber, 'messages' => $messages];

                continue;
            }

            $validRows[] = $data;
        }

        $result['valid'] = count($validRows);
        $result['invalid'] = count($errors);
        $result['errors'] = array_slice($errors, 0, 100);

        if ($errors) {
            $result['error_file'] = $this->writeErrorFile($errors);
        }

        if ($errors && ! $skipInvalid) {
            $result['fatal'] = 'Ditemukan '.count($errors).' baris tidak valid. Perbaiki file lalu unggah ulang, atau centang "Lewati baris tidak valid".';

            return $result;
        }

        if ($dryRun || ! $validRows) {
            return $result;
        }

        $this->insertRows($validRows, $user);

        $result['imported'] = count($validRows);
        $result['schedule_hint'] = collect($validRows)->where('status_verifikasi', 'menunggu')->count() ?: null;

        $this->clearCaches();

        return $result;
    }

    /**
     * Baca header + seluruh baris data (streaming).
     *
     * @return array{0: ?array<int, string>, 1: array<int, array<int, mixed>>}
     */
    private function parseFile(string $path, string $ext): array
    {
        $reader = match ($ext) {
            'csv', 'txt' => new CsvReader(new CsvOptions(FIELD_DELIMITER: $this->detectDelimiter($path))),
            'xlsx' => new XlsxReader,
            default => throw new \RuntimeException('Ekstensi file tidak didukung ('.$ext.'). Gunakan .csv atau .xlsx.'),
        };

        $reader->open($path);

        $headerMap = null;
        $rows = [];
        $rowNumber = 0;

        try {
            foreach ($reader->getSheetIterator() as $sheet) {
                foreach ($sheet->getRowIterator() as $row) {
                    $rowNumber++;

                    if ($headerMap === null) {
                        $headerMap = $this->resolveHeaderMap($row->toArray());

                        continue;
                    }

                    $rows[$rowNumber] = $row->toArray();
                }

                break; // hanya sheet pertama
            }
        } finally {
            $reader->close();
        }

        if ($rowNumber === 0) {
            throw new \RuntimeException('File kosong.');
        }

        return [$headerMap, $rows];
    }

    /**
     * Deteksi delimiter CSV dari baris pertama (',' vs ';').
     */
    private function detectDelimiter(string $path): string
    {
        $handle = fopen($path, 'r');
        if ($handle === false) {
            return ',';
        }

        $firstLine = fgets($handle, 8192) ?: '';
        fclose($handle);

        return substr_count($firstLine, ';') > substr_count($firstLine, ',') ? ';' : ',';
    }

    /**
     * Cocokkan header file dengan key kolom template.
     *
     * @param  array<int, mixed>  $headerCells
     * @return ?array<int, string> index kolom => key
     */
    private function resolveHeaderMap(array $headerCells): ?array
    {
        $lookup = [];
        foreach (self::COLUMNS as $key => $col) {
            $lookup[$this->normalizeHeader($col['label'])] = $key;
            $lookup[$this->normalizeHeader($key)] = $key;
            foreach ($col['aliases'] as $alias) {
                $lookup[$this->normalizeHeader($alias)] = $key;
            }
        }

        $map = [];
        $matched = 0;
        foreach ($headerCells as $index => $cell) {
            $normalized = $this->normalizeHeader($this->stringify($cell));
            if ($normalized !== '' && isset($lookup[$normalized])) {
                $map[$index] = $lookup[$normalized];
                $matched++;
            }
        }

        return $matched > 0 ? $map : null;
    }

    private function normalizeHeader(string $value): string
    {
        return Str::slug(trim($value), '_');
    }

    /**
     * @param  array<int, string>  $headerMap
     * @param  array<int, mixed>  $cells
     * @return array<string, string>
     */
    private function mapRow(array $headerMap, array $cells): array
    {
        $mapped = [];
        foreach ($headerMap as $index => $key) {
            $mapped[$key] = $this->stringify($cells[$index] ?? null);
        }

        return $mapped;
    }

    private function stringify(mixed $value): string
    {
        if ($value === null) {
            return '';
        }
        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d');
        }
        if (is_bool($value)) {
            return $value ? '1' : '0';
        }
        if (is_float($value) && floor($value) === $value && abs($value) < 1e15) {
            return (string) (int) $value;
        }

        return trim((string) $value);
    }

    /**
     * Validasi satu baris. Mengembalikan [data siap insert, daftar error].
     *
     * @param  array<string, string>  $row
     * @param  array<string, mixed>  $lookup
     * @param  array<string, int>  $seen
     * @return array{0: array<string, mixed>, 1: list<string>}
     */
    private function validateRow(array $row, int $rowNumber, array $lookup, array &$seen): array
    {
        $errors = [];
        $data = ['status_verifikasi' => 'disetujui'];

        $required = [
            'nama_opk' => 'Nama OPK',
            'kategori' => 'Kategori',
            'kecamatan' => 'Kecamatan',
            'desa_dinas' => 'Desa Dinas',
            'nama_desa_adat' => 'Nama Desa Adat',
            'deskripsi_umum' => 'Deskripsi Umum',
            'pelapor_nama' => 'Nama Pelapor',
        ];
        foreach ($required as $key => $label) {
            if (($row[$key] ?? '') === '') {
                $errors[] = "{$label} wajib diisi.";
            }
        }

        // === Identitas ===
        $kategoriId = $this->resolveKategori($row['kategori'] ?? '', $lookup);
        if (($row['kategori'] ?? '') !== '' && $kategoriId === null) {
            $errors[] = "Kategori '{$row['kategori']}' tidak dikenal. Lihat sheet Referensi.";
        }
        $data['kategori_id'] = $kategoriId;

        $namaOpk = trim($row['nama_opk'] ?? '');
        if (mb_strlen($namaOpk) > 200) {
            $errors[] = 'Nama OPK maksimal 200 karakter.';
        }
        $data['nama_opk'] = $namaOpk;

        if (($row['tahun_diketahui'] ?? '') !== '') {
            $tahun = $row['tahun_diketahui'];
            if (! ctype_digit($tahun) || (int) $tahun < 1 || (int) $tahun > (int) date('Y')) {
                $errors[] = 'Tahun Diketahui harus angka tahun yang valid (contoh: 1985).';
            } else {
                $data['tahun_diketahui'] = (int) $tahun;
            }
        }
        $data['tahun_keterangan'] = $this->limit($row['tahun_keterangan'] ?? '', 100, 'Tahun Keterangan', $errors);

        $data['status_pelindungan'] = $this->resolveEnum(
            $row['status_pelindungan'] ?? '', self::STATUS_PELINDUNGAN, 'belum_terdaftar', 'Status Pelindungan', $errors
        );

        $kondisi = $this->resolveKondisi($row['kondisi'] ?? '', $lookup);
        if (($row['kondisi'] ?? '') !== '' && $kondisi === null) {
            $errors[] = "Kondisi '{$row['kondisi']}' tidak dikenal. Lihat sheet Referensi.";
        }
        $data['kondisi'] = $kondisi ?? 'baik';

        // === Wilayah ===
        $kecamatanId = $lookup['kecamatan'][$this->key($row['kecamatan'] ?? '')] ?? null;
        if (($row['kecamatan'] ?? '') !== '' && $kecamatanId === null) {
            $errors[] = "Kecamatan '{$row['kecamatan']}' tidak ditemukan.";
        }
        $data['kecamatan_id'] = $kecamatanId;

        $desaDinasId = null;
        if ($kecamatanId !== null && ($row['desa_dinas'] ?? '') !== '') {
            $desaDinasId = $lookup['desa_dinas'][$kecamatanId.'|'.$this->key($row['desa_dinas'])] ?? null;
            if ($desaDinasId === null) {
                $errors[] = "Desa Dinas '{$row['desa_dinas']}' tidak ditemukan di Kecamatan '{$row['kecamatan']}'.";
            }
        }
        $data['desa_dinas_id'] = $desaDinasId;

        $data['nama_desa_adat'] = $this->limit($row['nama_desa_adat'] ?? '', 150, 'Nama Desa Adat', $errors);
        $data['banjar_adat'] = $this->limit($row['banjar_adat'] ?? '', 150, 'Banjar Adat', $errors);
        $data['lokasi_spesifik'] = $this->limit($row['lokasi_spesifik'] ?? '', 255, 'Lokasi Spesifik', $errors);

        [$lat, $lng] = $this->resolveCoordinates($row, $errors);
        $data['latitude'] = $lat;
        $data['longitude'] = $lng;

        // === Deskripsi ===
        $deskripsi = trim($row['deskripsi_umum'] ?? '');
        if ($deskripsi !== '' && mb_strlen($deskripsi) < 50) {
            $errors[] = 'Deskripsi Umum minimal 50 karakter.';
        }
        $data['deskripsi_umum'] = $deskripsi;
        $data['sejarah_asal_usul'] = ($row['sejarah_asal_usul'] ?? '') !== '' ? $row['sejarah_asal_usul'] : null;
        $data['nilai_makna_budaya'] = ($row['nilai_makna_budaya'] ?? '') !== '' ? $row['nilai_makna_budaya'] : null;
        $data['bahasa_digunakan'] = $this->limit($row['bahasa_digunakan'] ?? '', 100, 'Bahasa Digunakan', $errors);
        $data['aksara_digunakan'] = $this->limit($row['aksara_digunakan'] ?? '', 100, 'Aksara Digunakan', $errors);

        $data['frekuensi_pelaksanaan'] = $this->resolveEnum(
            $row['frekuensi_pelaksanaan'] ?? '', self::FREKUENSI, null, 'Frekuensi Pelaksanaan', $errors
        );
        $data['status_kepemilikan'] = $this->resolveEnum(
            $row['status_kepemilikan'] ?? '', self::KEPEMILIKAN, null, 'Status Kepemilikan', $errors
        );

        // === Praktisi ===
        $data['praktisi_nama'] = $this->limit($row['praktisi_nama'] ?? '', 150, 'Nama Praktisi', $errors);
        if (($row['praktisi_usia'] ?? '') !== '') {
            $usia = $row['praktisi_usia'];
            if (! ctype_digit($usia) || (int) $usia < 1 || (int) $usia > 120) {
                $errors[] = 'Usia Praktisi harus angka 1-120.';
            } else {
                $data['praktisi_usia'] = (int) $usia;
            }
        }
        $data['praktisi_kontak'] = $this->limit($row['praktisi_kontak'] ?? '', 50, 'Kontak Praktisi', $errors);

        // === Pelapor ===
        $data['tipe_pelapor'] = $this->resolveEnum(
            $row['tipe_pelapor'] ?? '', self::TIPE_PELAPOR, 'petugas_dinas', 'Tipe Pelapor', $errors
        );

        $data['pelapor_nama'] = $this->limit($row['pelapor_nama'] ?? '', 150, 'Nama Pelapor', $errors);

        $nik = $row['pelapor_nik'] ?? '';
        if ($nik !== '' && ! preg_match('/^\d{16}$/', $nik)) {
            $errors[] = 'NIK Pelapor harus 16 digit angka (format kolom sebagai Teks di Excel).';
        }
        $data['pelapor_nik'] = $nik;

        $wa = $row['pelapor_whatsapp'] ?? '';
        if (mb_strlen($wa) > 20) {
            $errors[] = 'WhatsApp Pelapor maksimal 20 karakter.';
        }
        $data['pelapor_whatsapp'] = $wa;
        $email = $row['pelapor_email'] ?? '';
        if ($email !== '' && ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Email Pelapor tidak valid.';
        }
        $data['pelapor_email'] = $email ?: null;

        // === Link & status ===
        $video = $row['link_video'] ?? '';
        if ($video !== '' && ! filter_var($video, FILTER_VALIDATE_URL)) {
            $errors[] = 'Link Video harus berupa URL yang valid.';
        }
        $data['link_video'] = $video ?: null;
        $data['link_dokumen_eksternal'] = $this->limit($row['link_dokumen_eksternal'] ?? '', 500, 'Link Dokumen Eksternal', $errors) ?: null;

        $data['status_verifikasi'] = $this->resolveEnum(
            $row['status_verifikasi'] ?? '', self::STATUS_VERIFIKASI_IMPORT, 'disetujui', 'Status Verifikasi', $errors
        );

        // === Duplikat (dalam file & di database) ===
        if ($kecamatanId !== null && $namaOpk !== '') {
            $dupKey = $kecamatanId.'|'.$this->key($namaOpk).'|'.$this->key($row['nama_desa_adat'] ?? '');
            if (isset($seen[$dupKey])) {
                $errors[] = "Duplikat dengan baris {$seen[$dupKey]} (nama, kecamatan & desa adat sama).";
            } elseif (isset($lookup['existing'][$dupKey])) {
                $errors[] = 'OPK dengan nama, kecamatan & desa adat sama sudah ada di database.';
            } elseif (! $errors) {
                $seen[$dupKey] = $rowNumber;
            }
        }

        return [$data, $errors];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildLookups(): array
    {
        $kategoriByName = [];
        $kategoriByNomor = [];
        foreach (OpkCategory::get(['id', 'nomor', 'nama']) as $kat) {
            $kategoriByName[$this->key($kat->nama)] = $kat->id;
            $kategoriByNomor[(string) $kat->nomor] = $kat->id;
        }

        $kecamatan = [];
        foreach (Kecamatan::get(['id', 'nama']) as $kec) {
            $kecamatan[$this->key($kec->nama)] = $kec->id;
        }

        $desaDinas = [];
        foreach (DesaDinas::get(['id', 'kecamatan_id', 'nama']) as $desa) {
            $desaDinas[$desa->kecamatan_id.'|'.$this->key($desa->nama)] = $desa->id;
        }

        $kondisi = [];
        foreach (KondisiOpk::get(['kode', 'nama']) as $k) {
            $kondisi[$this->key($k->kode)] = $k->kode;
            $kondisi[$this->key($k->nama)] = $k->kode;
        }

        $existing = [];
        foreach (OpkLaporan::select('nama_opk', 'kecamatan_id', 'nama_desa_adat')->get() as $opk) {
            $existing[$opk->kecamatan_id.'|'.$this->key($opk->nama_opk).'|'.$this->key($opk->nama_desa_adat ?? '')] = true;
        }

        return [
            'kategori' => $kategoriByName,
            'kategori_nomor' => $kategoriByNomor,
            'kecamatan' => $kecamatan,
            'desa_dinas' => $desaDinas,
            'kondisi' => $kondisi,
            'existing' => $existing,
        ];
    }

    /**
     * @param  array<string, mixed>  $lookup
     */
    private function resolveKategori(string $value, array $lookup): ?int
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }

        if (isset($lookup['kategori'][$this->key($value)])) {
            return $lookup['kategori'][$this->key($value)];
        }

        if (is_numeric($value) && isset($lookup['kategori_nomor'][(string) (int) $value])) {
            return $lookup['kategori_nomor'][(string) (int) $value];
        }

        if (str_contains($value, '-')) {
            [$nomor, $nama] = array_map('trim', explode('-', $value, 2));
            if (is_numeric($nomor) && isset($lookup['kategori_nomor'][(string) (int) $nomor])) {
                return $lookup['kategori_nomor'][(string) (int) $nomor];
            }
            if (isset($lookup['kategori'][$this->key($nama)])) {
                return $lookup['kategori'][$this->key($nama)];
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $lookup
     */
    private function resolveKondisi(string $value, array $lookup): ?string
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }

        return $lookup['kondisi'][$this->key($value)] ?? null;
    }

    /**
     * @param  list<string>  $allowed
     * @param  list<string>  $errors
     */
    private function resolveEnum(string $value, array $allowed, ?string $default, string $label, array &$errors): ?string
    {
        $value = trim($value);
        if ($value === '') {
            return $default;
        }

        $normalized = $this->key($value);
        $normalized = self::VALUE_ALIASES[$normalized] ?? $normalized;

        if (in_array($normalized, $allowed, true)) {
            return $normalized;
        }

        $errors[] = "{$label} '{$value}' tidak valid. Pilihan: ".implode(', ', $allowed).'.';

        return $default;
    }

    /**
     * @param  array<string, string>  $row
     * @param  list<string>  $errors
     * @return array{0: ?float, 1: ?float}
     */
    private function resolveCoordinates(array $row, array &$errors): array
    {
        $lat = trim($row['latitude'] ?? '');
        $lng = trim($row['longitude'] ?? '');

        if ($lat === '' && $lng === '') {
            return [null, null];
        }

        if ($lat === '' || $lng === '') {
            $errors[] = 'Latitude dan Longitude harus diisi keduanya atau dikosongkan keduanya.';

            return [null, null];
        }

        if (! is_numeric($lat) || (float) $lat < -90 || (float) $lat > 90) {
            $errors[] = 'Latitude harus angka antara -90 sampai 90.';

            return [null, null];
        }
        if (! is_numeric($lng) || (float) $lng < -180 || (float) $lng > 180) {
            $errors[] = 'Longitude harus angka antara -180 sampai 180.';

            return [null, null];
        }

        return [(float) $lat, (float) $lng];
    }

    /**
     * @param  list<string>  $errors
     */
    private function limit(string $value, int $max, string $label, array &$errors): ?string
    {
        $value = trim($value);
        if ($value !== '' && mb_strlen($value) > $max) {
            $errors[] = "{$label} maksimal {$max} karakter.";
        }

        return $value === '' ? null : $value;
    }

    private function key(string $value): string
    {
        return Str::lower(trim($value));
    }

    /**
     * Insert massal per chunk dalam satu transaksi (atomik).
     *
     * @param  list<array<string, mixed>>  $rows
     */
    private function insertRows(array $rows, User $user): void
    {
        $now = now();

        DB::transaction(function () use ($rows, $user, $now) {
            foreach (array_chunk($rows, self::CHUNK_SIZE) as $chunk) {
                $insert = [];
                foreach ($chunk as $row) {
                    $row['kode_laporan'] = OpkLaporan::generateKode();
                    $row['diverifikasi_oleh'] = $row['status_verifikasi'] === 'disetujui' ? $user->id : null;
                    $row['tanggal_verifikasi'] = $row['status_verifikasi'] === 'disetujui' ? $now : null;
                    $row['created_at'] = $now;
                    $row['updated_at'] = $now;
                    $insert[] = $row;
                }

                OpkLaporan::insert($insert);

                $kodes = array_column($insert, 'kode_laporan');
                $ids = OpkLaporan::whereIn('kode_laporan', $kodes)->pluck('id', 'kode_laporan');

                $riwayat = [];
                foreach ($insert as $row) {
                    $riwayat[] = [
                        'laporan_id' => $ids[$row['kode_laporan']] ?? null,
                        'status_lama' => '-',
                        'status_baru' => $row['status_verifikasi'],
                        'user_id' => $user->id,
                        'catatan' => 'Diimpor massal dari file Excel/CSV.',
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
                $riwayat = array_filter($riwayat, fn ($r) => $r['laporan_id'] !== null);
                if ($riwayat) {
                    OpkRiwayatStatus::insert(array_values($riwayat));
                }
            }
        });
    }

    /**
     * @param  list<array{row: int, messages: list<string>}>  $errors
     * @return string token file error
     */
    private function writeErrorFile(array $errors): string
    {
        $dir = storage_path(self::ERROR_DIR);
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $token = (string) Str::uuid();
        $handle = fopen($dir.'/'.$token.'.csv', 'w');
        fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
        fputcsv($handle, ['Baris', 'Masalah'], ',', '"', '');
        foreach ($errors as $error) {
            foreach ($error['messages'] as $message) {
                fputcsv($handle, [$error['row'], $message], ',', '"', '');
            }
        }
        fclose($handle);

        return $token;
    }

    public function errorFilePath(string $token): string
    {
        return storage_path(self::ERROR_DIR.'/'.$token.'.csv');
    }

    /**
     * Bersihkan file error lama (>7 hari).
     */
    public function pruneErrorFiles(): void
    {
        $dir = storage_path(self::ERROR_DIR);
        if (! is_dir($dir)) {
            return;
        }

        foreach (glob($dir.'/*.csv') ?: [] as $file) {
            if (filemtime($file) < now()->subDays(7)->getTimestamp()) {
                @unlink($file);
            }
        }
    }

    /**
     * Template XLSX (3 sheet) atau CSV (header + contoh).
     */
    public function generateTemplate(string $format = 'xlsx'): string
    {
        $dir = storage_path(self::TEMP_DIR);
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        return $format === 'csv'
            ? $this->writeCsvTemplate($dir)
            : $this->writeXlsxTemplate($dir);
    }

    private function writeCsvTemplate(string $dir): string
    {
        $path = $dir.'/template-impor-opk-'.Str::random(6).'.csv';
        $handle = fopen($path, 'w');
        fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
        fputcsv($handle, array_map(fn ($c) => $c['label'], self::COLUMNS), ',', '"', '');
        foreach ($this->exampleRows() as $row) {
            fputcsv($handle, $row, ',', '"', '');
        }
        fclose($handle);

        return $path;
    }

    private function writeXlsxTemplate(string $dir): string
    {
        $path = $dir.'/template-impor-opk-'.Str::random(6).'.xlsx';

        $writer = new XlsxWriter;
        $writer->openToFile($path);

        // Sheet 1 — Data OPK
        $sheet = $writer->getCurrentSheet();
        $sheet->setName('Data OPK');
        $writer->addRow(Row::fromValues(array_map(fn ($c) => $c['label'], self::COLUMNS)));
        foreach ($this->exampleRows() as $row) {
            $writer->addRow(Row::fromValues($row));
        }

        // Sheet 2 — Referensi
        $sheet = $writer->addNewSheetAndMakeItCurrent();
        $sheet->setName('Referensi');
        $writer->addRow(Row::fromValues(['Jenis', 'Nilai yang diterima', 'Keterangan']));
        foreach (OpkCategory::orderBy('nomor')->get() as $kat) {
            $writer->addRow(Row::fromValues(['Kategori', $kat->nomor.' - '.$kat->nama, 'Boleh isi nomor, nama, atau keduanya']));
        }
        foreach (KondisiOpk::orderBy('urutan')->get() as $k) {
            $writer->addRow(Row::fromValues(['Kondisi', $k->kode.' - '.$k->nama, 'Default: baik']));
        }
        foreach (self::STATUS_PELINDUNGAN as $v) {
            $writer->addRow(Row::fromValues(['Status Pelindungan', $v, 'Default: belum_terdaftar']));
        }
        foreach (self::FREKUENSI as $v) {
            $writer->addRow(Row::fromValues(['Frekuensi Pelaksanaan', $v, 'Opsional']));
        }
        foreach (self::KEPEMILIKAN as $v) {
            $writer->addRow(Row::fromValues(['Status Kepemilikan', $v, 'Opsional']));
        }
        foreach (self::TIPE_PELAPOR as $v) {
            $writer->addRow(Row::fromValues(['Tipe Pelapor', $v, 'Default: petugas_dinas']));
        }
        foreach (self::STATUS_VERIFIKASI_IMPORT as $v) {
            $writer->addRow(Row::fromValues(['Status Verifikasi', $v, 'Default: disetujui']));
        }
        foreach (Kecamatan::with(['desaDinas:id,kecamatan_id,nama'])->orderBy('nama')->get() as $kec) {
            $desa = $kec->desaDinas->pluck('nama')->implode(', ');
            $writer->addRow(Row::fromValues(['Kecamatan & Desa Dinas', $kec->nama, 'Desa dinas: '.$desa]));
        }

        // Sheet 3 — Petunjuk
        $sheet = $writer->addNewSheetAndMakeItCurrent();
        $sheet->setName('Petunjuk');
        $lines = [
            ['PETUNJUK IMPOR DATA OPK'],
            ['1. Isi sheet "Data OPK". Jangan mengubah/menghapus nama kolom di baris pertama.'],
            ['2. Kolom wajib: '.implode(', ', array_map(fn ($k) => self::COLUMNS[$k]['label'], array_keys(array_filter(self::COLUMNS, fn ($c) => $c['required']))))],
            ['3. Deskripsi Umum minimal 50 karakter.'],
            ['4. NIK & WhatsApp Pelapor boleh dikosongkan untuk data administratif. Jika diisi, format kolomnya sebagai Teks.'],
            ['5. Format kolom Latitude dan Longitude sebagai Teks agar angka tidak berubah.'],
            ['6. Nilai yang diterima untuk tiap kolom ada di sheet "Referensi".'],
            ['7. Maksimal '.number_format(self::MAX_ROWS, 0, ',', '.').' baris per file; pecah file jika lebih.'],
            ['8. Baris yang tidak valid akan ditolak; unduh laporan kesalahan setelah impor.'],
            ['9. Simpan sebagai .xlsx atau .csv (UTF-8) lalu unggah lewat menu Impor OPK.'],
        ];
        foreach ($lines as $line) {
            $writer->addRow(Row::fromValues($line));
        }

        $writer->close();

        return $path;
    }

    /**
     * @return list<list<string>>
     */
    private function exampleRows(): array
    {
        $kategori = OpkCategory::orderBy('nomor')->first();
        $kecamatan = Kecamatan::with(['desaDinas:id,kecamatan_id,nama'])->orderBy('nama')->first();
        $desa = $kecamatan?->desaDinas->first();
        $kondisi = KondisiOpk::orderBy('urutan')->value('kode') ?? 'baik';

        $contoh = [
            'nama_opk' => 'Contoh: Tari Baris Tunggal',
            'kategori' => $kategori ? $kategori->nomor.' - '.$kategori->nama : 'Seni',
            'kecamatan' => $kecamatan?->nama ?? 'Mengwi',
            'desa_dinas' => $desa?->nama ?? 'Mengwi',
            'nama_desa_adat' => 'Desa Adat Contoh',
            'banjar_adat' => 'Banjar Contoh',
            'lokasi_spesifik' => 'Balai Banjar Contoh',
            'latitude' => '-8.61515880',
            'longitude' => '115.14472445',
            'tahun_diketahui' => '1985',
            'tahun_keterangan' => '',
            'status_pelindungan' => 'belum_terdaftar',
            'kondisi' => $kondisi,
            'deskripsi_umum' => 'Tari Baris Tunggal adalah tari sakral yang dipentaskan saat upacara ngaben di desa adat, diwariskan secara turun-temurun.',
            'sejarah_asal_usul' => '',
            'nilai_makna_budaya' => '',
            'bahasa_digunakan' => 'Bali',
            'aksara_digunakan' => '',
            'frekuensi_pelaksanaan' => 'rutin_tahunan',
            'status_kepemilikan' => 'desa_adat',
            'praktisi_nama' => 'I Wayan Contoh',
            'praktisi_usia' => '55',
            'praktisi_kontak' => '081234567890',
            'tipe_pelapor' => 'petugas_dinas',
            'pelapor_nama' => 'Petugas Dinas Kebudayaan',
            'pelapor_nik' => '5103000000000001',
            'pelapor_whatsapp' => '081234567890',
            'pelapor_email' => 'petugas@example.com',
            'status_verifikasi' => 'disetujui',
            'link_video' => '',
            'link_dokumen_eksternal' => '',
        ];

        $headerKeys = array_keys(self::COLUMNS);

        return [
            array_map(fn ($k) => (string) ($contoh[$k] ?? ''), $headerKeys),
            array_fill(0, count($headerKeys), ''),
        ];
    }

    private function clearCaches(): void
    {
        PetaDataService::invalidateCache();
        Cache::forget(CacheKeys::SIDEBAR_COUNTS);
        Cache::forget(CacheKeys::LAPORAN_ADMIN);
        Cache::forget(CacheKeys::RINGKASAN_EKSEKUTIF);
        Cache::forget(CacheKeys::PUBLIK_DASHBOARD);
        Cache::forget(CacheKeys::DAFTAR_OPK_FILTERS.'_kategori');
        Cache::forget(CacheKeys::DAFTAR_OPK_FILTERS.'_kecamatan');
    }
}
