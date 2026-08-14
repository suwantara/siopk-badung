# Bugfix Produksi — Verifikasi, AI Ringkasan & DeepSeek v4

Dokumentasi perbaikan bug yang ditemukan di production (Hostinger shared hosting),
tanggal 13 Agustus 2026. Mencakup analisis akar masalah, perubahan kode, dan
langkah operasional.

---

## 1. Bug: "Gagal memverifikasi laporan. Silakan coba lagi."

### Gejala
Verifikator menekan **Setujui** / **Tolak** → muncul pesan error generik
"Gagal memverifikasi laporan. Silakan coba lagi." Retry juga gagal.

### Akar Masalah (2 kemungkinan)

**A. Laporan macet di status `menunggu`/`ai_review`**
`StatusVerifikasi::canTransitionTo()` hanya mengizinkan verifikasi dari
`review_dinas`. Di Hostinger, queue worker tidak berjalan (cron belum diset),
sehingga `AnalisisOpkJob` tidak pernah memproses laporan → status tidak pernah
mencapai `review_dinas` → verifikator selalu ditolak transisinya.

**B. Side effect setelah commit ikut masuk try/catch**
`LaporanVerified::dispatch()` (WhatsApp job) dan `PetaDataService::invalidateCache()`
dipanggil di dalam `try` yang sama dengan transaksi DB — padahal DB sudah
`commit()`. Jika salah satu gagal (tabel `jobs` belum ada, cache file bermasalah),
data sebenarnya sudah terverifikasi, tetapi user tetap melihat error, dan retry
gagal karena status sudah terminal.

### Perubahan Kode

| File | Perubahan |
|------|-----------|
| `app/Enums/StatusVerifikasi.php` | Verifikator boleh memverifikasi manual dari status `menunggu` dan `ai_review` (fallback jika AI tidak sempat jalan) |
| `app/Services/VerifikasiService.php` | Event & invalidasi cache dipindah keluar try/catch transaksi; kegagalan side effect hanya di-log (`Log::warning`), tidak menggagalkan verifikasi |
| `app/Http/Controllers/Admin/VerifikasiController.php` | Exception di-log dengan detail (`laporan_id`, message) dan pesan error asli ditampilkan ke user |

### Cara Konfirmasi di Production
```bash
grep -n "Gagal memverifikasi laporan" storage/logs/laravel-$(date +%Y-%m-%d).log | tail -5
```

---

## 2. Bug: AI Ringkasan Eksekutif Tidak Tampil

### Gejala
Klik "Generate Ringkasan" di `/admin/ai/ringkasan` → hasil tidak muncul
(tidak ada teks, tidak ada error).

### Akar Masalah
Model `deepseek-v4-flash` adalah **reasoning model**. Jawabannya dikembalikan
di field `reasoning_content`, sementara `content` kosong. Kode hanya membaca
`message.content`:

```json
{"choices":[{"message":{"content":"","reasoning_content":"We need answer. User"}}]}
```

Akibatnya:
- `AiOpkAnalyzer::ringkasanEksekutif()` menerima `success=true` dengan teks kosong
- Controller menganggap sukses → cache teks kosong → UI merender kosong
- Tidak ada error yang ter-log sama sekali (log sepi)

Efek sama terjadi di **AI pre-screening verifikasi**: `analisisLaporan()` gagal
parse JSON kosong → semua laporan mendapat skor default 5.0 flat / duplikat 0%.

### Catatan Model DeepSeek (per docs resmi, update 24 Juli 2026)
- `deepseek-chat` dan `deepseek-reasoner` sudah **discontinued**
- Sekarang hanya: `deepseek-v4-flash` dan `deepseek-v4-pro`
- Mapping legacy: `deepseek-chat` = v4-flash **non-thinking**, `deepseek-reasoner` = v4-flash **thinking**
- Mode thinking dikontrol via body request: `"thinking": {"type": "enabled"|"disabled"}`

### Perubahan Kode

| File | Perubahan |
|------|-----------|
| `app/Services/Ai/DeepSeekProvider.php` | Kirim `"thinking": {"type": "disabled"}` default (setara deepseek-chat lama); fallback baca `reasoning_content` jika `content` kosong |
| `config/services.php` | Default model `deepseek-v4-flash`; tambah `DEEPSEEK_THINKING=disabled` (env) |
| `app/Http/Controllers/Admin/AiController.php` | try/catch + logging di `ringkasanEksekutif()`; hasil kosong dianggap gagal; `Cache::put` dibungkus try/catch |
| `resources/views/admin/ai/ringkasan.blade.php` | Fetch kirim `Accept: application/json` (sebelumnya 429/500 Laravel berupa HTML → JSON parse gagal diam-diam); pesan error server ditampilkan |
| `app/Services/Ai/BaseProvider.php` | Retry configurable (`AI_RETRIES` di .env); log mencatat durasi panggilan & memotong body |
| `app/Services/Ai/AiProviderFactory.php` | Meneruskan konfigurasi `retries` ke provider |

### Konfigurasi .env Production
```env
AI_PROVIDER=deepseek
DEEPSEEK_API_KEY=sk-...
DEEPSEEK_MODEL=deepseek-v4-flash        # jangan deepseek-chat lagi
DEEPSEEK_THINKING=disabled              # opsional, default sudah disabled
DEEPSEEK_MAX_TOKENS=2000                # disarankan, default 1024
AI_RETRIES=1                            # kurangi retry agar tidak melebihi max_execution_time
DEEPSEEK_TIMEOUT=20                     # sesuaikan dengan limit PHP Hostinger
```

> **Catatan Hostinger**: `max_execution_time` shared hosting terbatas (umumnya 30 dtk).
> Timeout AI 30 dtk × 4 percobaan (retry 3) bisa melebihi limit → request mati
> sebelum respons terkirim. Naikkan `max_execution_time` di hPanel → PHP Configuration
> (120 dtk disarankan) atau turunkan `AI_RETRIES`/`*_TIMEOUT`.

---

## 3. Fitur: Skip AI Saat API Key Kosong

### Perilaku Baru
Saat API key provider tidak tersedia:
1. `AnalisisOpkJob` **langsung skip** panggilan HTTP (tidak ada 401/retry/delay)
2. Skor diisi dari **heuristik kondisi OPK** (kritis 7.5, non-baik 4.5, baik 2.0, +1.5 jika praktisi >60 tahun) — antrian verifikasi tetap terurut
3. `ai_rekomendasi` diberi prefix **`[Tanpa AI]`** sebagai penanda
4. Status tetap lanjut ke `review_dinas` → verifikasi manual berjalan normal
5. Scheduler 30-menit tetap hanya memproses laporan tanpa skor (NULL) → tidak ada loop job

### Saat API Key Ditambahkan
```bash
# Re-analisis hanya laporan yang sempat dilewati AI (disarankan)
php artisan siopk:analisis-semua --fallback

# Atau ulang semua laporan pending (menunggu/review_dinas)
php artisan siopk:analisis-semua --force
```

### Perubahan Kode

| File | Perubahan |
|------|-----------|
| `app/Services/AiOpkAnalyzer.php` | Tambah `isAvailable()`; `defaultAnalysis()` jadi public |
| `app/Jobs/AnalisisOpkJob.php` | Jalur skip AI sebelum try/catch utama |
| `app/Console/Commands/AnalisisSemuaOpkCommand.php` | Opsi baru `--fallback` (filter `ai_rekomendasi LIKE '[Tanpa AI]%'`) |

---

## 4. Setup Cron Job di Hostinger (hPanel → Cron Jobs)

Penting: jangan duplikasi `* * * * *` — jika kolom **Waktu** sudah diisi `* * * * *`,
kolom **Command** hanya berisi command tanpa prefix waktu.

### Cron 1 — Scheduler Laravel (wajib, tiap menit)
```
/usr/bin/php /home/u472709165/domains/siopk.unhi.ac.id/artisan schedule:run >> /dev/null 2>&1
```

### Cron 2 — Queue Worker (wajib karena `QUEUE_CONNECTION=database`)
```
/usr/bin/php /home/u472709165/domains/siopk.unhi.ac.id/artisan queue:work --stop-when-empty --max-time=55 --memory=128 >> /dev/null 2>&1
```

Jika hPanel memaksa interval minimal 5 menit:
```
/usr/bin/php /home/u472709165/domains/siopk.unhi.ac.id/artisan queue:work --stop-when-empty --max-time=280 --memory=128 >> /dev/null 2>&1
```

### Verifikasi
```bash
php artisan schedule:list
php artisan migrate:status | grep -i job   # pastikan tabel jobs, failed_jobs sudah dibuat
```

> Log channel production adalah `daily` → file `storage/logs/laravel-YYYY-MM-DD.log`.
> Jangan mencari `laravel.log` di production.

---

## 5. Troubleshooting Cepat di SSH Production

```bash
# Error AI / ringkasan terbaru
grep -n "AI Provider\|Ringkasan eksekutif" storage/logs/laravel-$(date +%Y-%m-%d).log | tail -20

# Tes koneksi AI (cek provider aktif + key)
php artisan siopk:test-ai

# Generate ringkasan dari CLI (isolasi dari browser)
php artisan tinker --execute="
app(App\Services\AiOpkAnalyzer::class)->ringkasanEksekutif(app(App\Services\OpkStatsService::class)->ringkasanEksekutif())
"

# Cek konfigurasi AI
php artisan config:show services.ai

# Cek antrian job
php artisan queue:monitor default

# Cek limit eksekusi PHP
php -r "echo ini_get('max_execution_time');"
```

---

## 6. Daftar Lengkap File yang Berubah (Bugfix 13 Agustus 2026)

| File | Terkait Bug |
|------|-------------|
| `app/Enums/StatusVerifikasi.php` | 1 — verifikasi manual dari menunggu/ai_review |
| `app/Services/VerifikasiService.php` | 1 — side effect keluar try/catch transaksi |
| `app/Http/Controllers/Admin/VerifikasiController.php` | 1 — log + pesan error asli |
| `app/Services/Ai/DeepSeekProvider.php` | 2 — thinking disabled + fallback reasoning_content |
| `config/services.php` | 2 — model v4-flash, DEEPSEEK_THINKING, AI_RETRIES |
| `app/Http/Controllers/Admin/AiController.php` | 2 — try/catch, log, guard hasil kosong |
| `resources/views/admin/ai/ringkasan.blade.php` | 2 — Accept JSON, tampilkan error server |
| `app/Services/Ai/BaseProvider.php` | 2 — retry configurable, log durasi |
| `app/Services/Ai/AiProviderFactory.php` | 2 — teruskan config retries |
| `app/Services/AiOpkAnalyzer.php` | 3 — isAvailable(), defaultAnalysis() public |
| `app/Jobs/AnalisisOpkJob.php` | 3 — skip AI saat key kosong |
| `app/Console/Commands/AnalisisSemuaOpkCommand.php` | 3 — opsi --fallback |
| `app/Console/Commands/TestAiCommand.php` | 2 — cek provider aktif, bukan hardcode claude |

### Langkah Deploy ke Production
1. Upload semua file di atas (overwrite)
2. Update `.env`: `DEEPSEEK_MODEL=deepseek-v4-flash` (jika masih deepseek-chat)
3. `php artisan config:clear && php artisan cache:clear`
4. `php artisan siopk:analisis-semua --fallback` (re-analisis laporan yang dilewati AI)
5. Verifikasi: cek log AI + antrian verifikasi
