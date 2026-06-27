<?php

namespace App\Services;

use App\Models\OpkLaporan;
use App\Services\Ai\{AiProviderInterface, AiProviderFactory};
use Illuminate\Support\Facades\Log;

/**
 * AiOpkAnalyzer
 * 
 * Multi-provider AI service (Claude, OpenAI, DeepSeek, Groq, Custom).
 * 
 * Konfigurasi via .env:
 *   AI_PROVIDER=claude|openai|deepseek|groq|custom
 *   <PROVIDER>_API_KEY=...
 *   <PROVIDER>_API_URL=...  (opsional, ada default)
 *   <PROVIDER>_MODEL=...    (opsional, ada default)
 * 
 * Custom provider bisa pakai format Claude atau OpenAI (CUSTOM_AI_TYPE).
 */
class AiOpkAnalyzer
{
    private AiProviderInterface $provider;

    public function __construct()
    {
        $this->provider = AiProviderFactory::make(
            config('services.ai.provider', 'claude')
        );
    }

    // ─────────────────────────────────────────────
    //  1. ANALISIS UTAMA — dipanggil setelah laporan masuk
    // ─────────────────────────────────────────────
    public function analisisLaporan(OpkLaporan $laporan): array
    {
        // Kumpulkan konteks laporan lain yang sudah disetujui (maks 5 terbaru)
        $laporanLain = OpkLaporan::where('status_verifikasi', 'disetujui')
            ->where('id', '!=', $laporan->id)
            ->latest()
            ->limit(5)
            ->get(['id', 'nama_opk', 'kondisi', 'nama_desa_adat', 'kategori_id'])
            ->map(fn($l) => "- [{$l->id}] " . $this->sanitize($l->nama_opk) . " (" . $this->sanitize($l->nama_desa_adat) . ", kondisi: {$l->kondisi})")
            ->implode("\n");

        $prompt = <<<PROMPT
Kamu adalah sistem AI untuk Dinas Kebudayaan Kabupaten Badung, Bali.
Tugasmu adalah menganalisis laporan Objek Pemajuan Kebudayaan (OPK) dari masyarakat.

## DATA LAPORAN BARU:
- Nama OPK     : {$this->sanitize($laporan->nama_opk)}
- Jenis OPK    : {$this->sanitize($laporan->kategori?->nama ?? '')}
- Kondisi      : {$laporan->kondisi}
- Kecamatan    : {$this->sanitize($laporan->kecamatan?->nama ?? '')}
- Desa Adat    : {$this->sanitize($laporan->nama_desa_adat)}
- Frekuensi    : {$this->sanitize($laporan->frekuensi_pelaksanaan ?? '')}
- Kepemilikan  : {$this->sanitize($laporan->status_kepemilikan ?? '')}
- Deskripsi    : {$this->sanitize($laporan->deskripsi_umum)}
- Praktisi     : {$this->sanitize($laporan->praktisi_nama ?? '')} (usia: {$laporan->praktisi_usia})

## OPK YANG SUDAH TERDAFTAR (pembanding duplikat):
{$laporanLain}

## INSTRUKSI:
Analisis laporan ini dan berikan output JSON berikut (tanpa penjelasan, tanpa markdown, hanya JSON murni):

{
  "urgency_score": <angka 0.0–10.0, makin tinggi makin mendesak>,
  "duplikat_score": <angka 0.0–100.0 persen kemiripan dengan laporan lain, 0 jika tidak ada>,
  "duplikat_id": <id laporan yang mirip atau null>,
  "rekomendasi": "<kalimat rekomendasi tindakan dalam Bahasa Indonesia, maks 150 kata>",
  "alasan_urgensi": "<penjelasan singkat mengapa skor urgensi tersebut, maks 80 kata>",
  "saran_verifikasi": "<saran untuk verifikator dinas, maks 80 kata>"
}

## PANDUAN SCORING URGENSI:
- 9–10 : Kritis + praktisi tunggal usia lanjut / hampir punah
- 7–9  : Kritis / rusak fisik / tidak ada regenerasi
- 5–7  : Waspada / praktisi berkurang signifikan
- 3–5  : Waspada ringan / perlu pemantauan
- 1–3  : Baik / terpelihara / komunitas aktif
- 0–1  : Sangat baik / sudah ada SK perlindungan
PROMPT;

        $result = $this->callApi($prompt);

        if (!$result['success']) {
            return $this->defaultAnalysis($laporan);
        }

        $data = $this->parseJson($result['content']);

        return [
            'urgency_score'    => min(10, max(0, (float)($data['urgency_score'] ?? 5.0))),
            'duplikat_score'   => min(100, max(0, (float)($data['duplikat_score'] ?? 0))),
            'duplikat_id'      => $data['duplikat_id'] ?? null,
            'rekomendasi'      => $data['rekomendasi'] ?? 'Perlu verifikasi lapangan.',
            'alasan_urgensi'   => $data['alasan_urgensi'] ?? '',
            'saran_verifikasi' => $data['saran_verifikasi'] ?? '',
        ];
    }

    // ─────────────────────────────────────────────
    //  2. CEK DUPLIKAT
    // ─────────────────────────────────────────────
    public function cekDuplikat(OpkLaporan $baru, OpkLaporan $lama): float
    {
        $prompt = <<<PROMPT
Bandingkan dua laporan OPK berikut dan tentukan persentase kemiripannya (0–100).
Pertimbangkan nama, lokasi, jenis, dan deskripsi.

## Laporan A (Baru):
Nama: {$this->sanitize($baru->nama_opk)}
Jenis: {$this->sanitize($baru->kategori?->nama ?? '')}
Lokasi: {$this->sanitize($baru->nama_desa_adat)}, {$this->sanitize($baru->kecamatan?->nama ?? '')}
Deskripsi: {$this->sanitize($baru->deskripsi_umum)}

## Laporan B (Sudah ada):
Nama: {$this->sanitize($lama->nama_opk)}
Jenis: {$this->sanitize($lama->kategori?->nama ?? '')}
Lokasi: {$this->sanitize($lama->nama_desa_adat)}, {$this->sanitize($lama->kecamatan?->nama ?? '')}
Deskripsi: {$this->sanitize($lama->deskripsi_umum)}

Jawab HANYA dengan angka desimal 0–100 (contoh: 75.5), tanpa penjelasan.
PROMPT;

        $result = $this->callApi($prompt, 50);
        if (!$result['success']) return 0.0;

        return min(100, max(0, (float) trim($result['content'])));
    }

    // ─────────────────────────────────────────────
    //  3. RINGKASAN EKSEKUTIF MINGGUAN
    // ─────────────────────────────────────────────
    public function ringkasanEksekutif(array $stats): string
    {
        $prompt = <<<PROMPT
Kamu adalah asisten AI untuk Kepala Dinas Kebudayaan Kabupaten Badung.
Buat ringkasan eksekutif mingguan dalam Bahasa Indonesia yang profesional dan ringkas.

## DATA MINGGUAN:
- Total OPK terdaftar : {$stats['total_opk']}
- Laporan baru masuk  : {$stats['laporan_baru']}
- OPK status kritis   : {$stats['kritis']}
- OPK status waspada  : {$stats['waspada']}
- Laporan disetujui   : {$stats['disetujui']}
- Laporan ditolak     : {$stats['ditolak']}
- Menunggu verifikasi : {$stats['menunggu']}
- OPK prioritas tinggi (AI score ≥7): {$stats['prioritas_tinggi']}

## OPK KRITIS TERATAS:
{$this->sanitize($stats['opk_kritis_list'])}

Tulis ringkasan eksekutif dengan format berikut (tanpa markdown, tanpa bold/italic, plain text saja):
1. Kondisi Umum (2 kalimat)
2. Temuan Penting (2-3 poin, gunakan tanda strip "-" di awal)
3. Rekomendasi Tindakan (2-3 poin, gunakan tanda strip "-" di awal)
4. Prioritas Minggu Ini (1 poin)

Maksimal 200 kata, nada formal dan faktual. JANGAN gunakan formatting markdown (**bold**, *italic*, dll).
PROMPT;

        $result = $this->callApi($prompt, 600);
        return $result['success'] ? $result['content'] : 'Ringkasan tidak tersedia.';
    }

    // ─────────────────────────────────────────────
    //  4. AUTO-KLASIFIKASI OPK
    // ─────────────────────────────────────────────
    public function klasifikasiOtomatis(string $namaOpk, string $deskripsi): ?int
    {
        $prompt = <<<PROMPT
Berdasarkan nama dan deskripsi OPK berikut, tentukan jenis OPK yang paling tepat.

Nama OPK  : {$this->sanitize($namaOpk)}
Deskripsi : {$this->sanitize($deskripsi)}

Pilih SALAH SATU nomor dari daftar berikut:
1 = Tradisi Lisan (tutur, cerita rakyat, pantun)
2 = Manuskrip (lontar, babad, naskah kuno)
3 = Adat Istiadat (hukum adat, tata kelola komunitas)
4 = Ritus (upacara keagamaan, ritual siklus hidup)
5 = Pengetahuan Tradisional (pengobatan, etnobotani)
6 = Teknologi Tradisional (subak, tenun, arsitektur)
7 = Seni (tari, musik, ukir, lukis, pertunjukan)
8 = Bahasa (bahasa daerah, dialek, aksara)
9 = Permainan Rakyat (permainan tradisional)
10 = Olahraga Tradisional (seni bela diri, lomba tradisional)

Jawab HANYA dengan angka 1–10, tanpa penjelasan.
PROMPT;

        $result = $this->callApi($prompt, 10);
        if (!$result['success']) return null;

        $num = (int) trim($result['content']);
        return ($num >= 1 && $num <= 10) ? $num : null;
    }

    // ─────────────────────────────────────────────
    //  5. CHAT ASISTEN
    // ─────────────────────────────────────────────
    public function chatAsisten(string $pertanyaan, OpkLaporan $laporan): string
    {
        $prompt = <<<PROMPT
Kamu adalah asisten AI untuk verifikator Dinas Kebudayaan Kabupaten Badung.
Jawab pertanyaan verifikator tentang laporan OPK berikut.

## KONTEKS LAPORAN:
Nama     : {$this->sanitize($laporan->nama_opk)}
Jenis    : {$this->sanitize($laporan->kategori?->nama ?? '')}
Kondisi  : {$laporan->kondisi}
Lokasi   : {$this->sanitize($laporan->nama_desa_adat)}, {$this->sanitize($laporan->kecamatan?->nama ?? '')}
Deskripsi: {$this->sanitize($laporan->deskripsi_umum)}
AI Score : {$laporan->ai_urgency_score}/10

## PERTANYAAN VERIFIKATOR:
{$this->sanitize($pertanyaan)}

Jawab dalam Bahasa Indonesia, profesional, maksimal 150 kata.
PROMPT;

        $result = $this->callApi($prompt, 400);
        return $result['success'] ? $result['content'] : 'Maaf, asisten AI tidak tersedia saat ini.';
    }

    // ─────────────────────────────────────────────
    //  PRIVATE HELPERS
    // ─────────────────────────────────────────────
    private function sanitize(?string $input): string
    {
        if ($input === null || $input === '') {
            return '';
        }
        $cleaned = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $input);
        return mb_substr(trim($cleaned), 0, 2000);
    }

    /**
     * Panggil API AI via provider.
     */
    private function callApi(string $prompt, ?int $maxTokens = null): array
    {
        return $this->provider->analyze($prompt, $maxTokens ?? 1024);
    }

    private function parseJson(string $content): array
    {
        $clean = preg_replace('/```json|```/i', '', $content);
        $clean = trim($clean);

        $data = json_decode($clean, true);
        return is_array($data) ? $data : [];
    }

    private function defaultAnalysis(OpkLaporan $laporan): array
    {
        $score = match($laporan->kondisi) {
            'kritis'  => 7.5,
            'waspada' => 4.5,
            default   => 2.0,
        };

        if ($laporan->praktisi_usia && $laporan->praktisi_usia > 60) {
            $score = min(10, $score + 1.5);
        }

        return [
            'urgency_score'    => $score,
            'duplikat_score'   => 0.0,
            'duplikat_id'      => null,
            'rekomendasi'      => "OPK dengan kondisi {$laporan->kondisi} di {$this->sanitize($laporan->nama_desa_adat)}. Perlu verifikasi lapangan oleh tim Dinas Kebudayaan.",
            'alasan_urgensi'   => "Dihitung berdasarkan kondisi OPK ({$laporan->kondisi}) secara otomatis.",
            'saran_verifikasi' => 'Lakukan kunjungan lapangan untuk memvalidasi data.',
        ];
    }
}
