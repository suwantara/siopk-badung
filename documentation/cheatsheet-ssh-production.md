# Cheatsheet SSH — SIOPK Badung (Production Hostinger)

Kumpulan command SSH untuk troubleshoot masalah dan rutinitas setelah setiap
perubahan (deploy). Jalankan dari direktori aplikasi:

```bash
cd /home/u472709165/domains/siopk.unhi.ac.id
```

---

## A. Setelah Setiap Perubahan / Deploy (Wajib)

```bash
# 1. Clear cache & config
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# 2. (Opsional) Optimize cache untuk production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 3. Cek tidak ada error sintaks di perubahan (jika edit file langsung)
php -l <path/file.php>

# 4. Cek status migrasi
php artisan migrate:status

# 5. Cek log terbaru (log channel production = daily)
tail -50 storage/logs/laravel-$(date +%Y-%m-%d).log
```

---

## B. Troubleshoot — Verifikasi Gagal

```bash
# Lihat error verifikasi terbaru
grep -n "Gagal memverifikasi\|Gagal menyetujui\|Gagal menolak" storage/logs/laravel-$(date +%Y-%m-%d).log | tail -10

# Cek laporan yang macet (belum review_dinas padahal lama)
php artisan tinker --execute="
App\Models\OpkLaporan::whereIn('status_verifikasi', ['menunggu','ai_review'])
  ->orderBy('created_at')->get(['id','kode_laporan','status_verifikasi','created_at'])->each(fn(\$l) => dump(\$l->toArray()));
"

# Cek queue worker hidup (cron)
php artisan queue:monitor default

# Lihat job gagal
php artisan queue:failed

# Proses ulang job yang gagal
php artisan queue:retry all
```

---

## C. Troubleshoot — AI (Ringkasan / Skor / Pre-Screening)

```bash
# Tes koneksi AI (cek provider aktif + key)
php artisan siopk:test-ai

# Lihat error provider AI
grep -n "AI Provider\|Ringkasan eksekutif" storage/logs/laravel-$(date +%Y-%m-%d).log | tail -20

# Cek konfigurasi AI yang aktif
php artisan config:show services.ai

# Tes DeepSeek API langsung (ganti key)
curl -s https://api.deepseek.com/v1/chat/completions \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $(grep '^DEEPSEEK_API_KEY' .env | cut -d= -f2)" \
  -d '{"model":"deepseek-v4-flash","thinking":{"type":"disabled"},"messages":[{"role":"user","content":"halo"}],"max_tokens":10}'

# Generate ringkasan dari CLI (isolasi masalah dari browser)
php artisan tinker --execute="
app(App\Services\AiOpkAnalyzer::class)->ringkasanEksekutif(app(App\Services\OpkStatsService::class)->ringkasanEksekutif())
"

# Analisis ulang laporan (setelah API key ditambahkan / model diganti)
php artisan siopk:analisis-semua --fallback   # hanya laporan [Tanpa AI]
php artisan siopk:analisis-semua --force      # semua laporan pending

# Cek hasil analisis
grep "AI selesai\|AI mulai" storage/logs/laravel-$(date +%Y-%m-%d).log | tail -20
```

---

## D. Troubleshoot — Cron / Queue

```bash
# Cek task scheduler terdaftar
php artisan schedule:list

# Tes scheduler jalan manual
php artisan schedule:run

# Cek isi tabel queue
php artisan tinker --execute="dump(DB::table('jobs')->count(), DB::table('failed_jobs')->count());"

# Bersihkan job yang macet/rusak
php artisan queue:clear

# Restart tidak perlu (cron worker tiap menit) — cukup cek log
tail -30 storage/logs/laravel-$(date +%Y-%m-%d).log
```

---

## E. Troubleshoot — Umum

```bash
# Log error hari ini (error saja)
grep -E "ERROR|CRITICAL|ALERT|EMERGENCY" storage/logs/laravel-$(date +%Y-%m-%d).log | tail -30

# Ikuti log real-time (biarkan terbuka sambil test di browser)
tail -f storage/logs/laravel-$(date +%Y-%m-%d).log

# Cek versi & environment
php artisan about

# Cek limit PHP Hostinger
php -r "echo ini_get('max_execution_time');"

# Cek .env tanpa menampilkan full key
grep -E "^(APP_|AI_|DEEPSEEK|QUEUE|CACHE|LOG_CHANNEL)" .env | sed 's/\(KEY=\).\{8\}.*/\1***masked***/'

# Cek permission storage
ls -la storage/logs bootstrap/cache

# Cek error 500 terakhir (format daily)
ls -lt storage/logs/ | head -5
```

---

## F. Setelah Masalah Selesai — Verifikasi Sistem Sehat

```bash
# 1. AI jalan
php artisan siopk:test-ai

# 2. Queue tidak menumpuk
php artisan tinker --execute="dump(DB::table('jobs')->count());"

# 3. Tidak ada error baru di log
grep -c "ERROR" storage/logs/laravel-$(date +%Y-%m-%d).log

# 4. Semua cache bersih & config ter-cache
php artisan config:cache && php artisan route:cache && php artisan view:cache
```
