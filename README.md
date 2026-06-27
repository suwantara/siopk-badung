# SIOPK Badung

[![Laravel](https://img.shields.io/badge/Laravel-11--12-FF2D20?logo=laravel)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2%2B-777BB4?logo=php)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?logo=mysql)](https://mysql.com)

**Sistem Informasi Objek Pemajuan Kebudayaan Kabupaten Badung**
Pemetaan Partisipatif 10 OPK · UU No. 5 Tahun 2017

---

## Stack Teknologi

| Layer | Teknologi |
|---|---|
| Backend | Laravel 11/12, PHP 8.2+ |
| Database | MySQL 8 |
| Cache / Queue | Redis |
| Frontend | Bootstrap 5 + Blade |
| Peta | Leaflet.js + OpenStreetMap |
| AI | Multi-provider: Claude, OpenAI, DeepSeek, Groq, Custom |
| WhatsApp | Fonnte API |
| Build | Vite 7 |

---

## Instalasi

```bash
cp .env.example .env
# Edit .env — isi DB_PASSWORD, AI_PROVIDER, dan API key

docker compose up -d
docker compose exec app composer install
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed
docker compose exec app php artisan storage:link
```

Buka `http://localhost`.

---

## Konfigurasi .env

```env
APP_URL=http://localhost
DB_DATABASE=siopk_badung
DB_USERNAME=root
DB_PASSWORD=

AI_PROVIDER=claude             # claude|openai|deepseek|groq|custom
CLAUDE_API_KEY=                # isi hanya provider yang dipilih
FONNTE_TOKEN=                  # opsional — notifikasi WhatsApp

SESSION_DRIVER=redis           # redis|file|database
CACHE_STORE=redis
QUEUE_CONNECTION=redis
REDIS_HOST=127.0.0.1
```

---

## URL Akses

| URL | Keterangan |
|---|---|
| `/` | Dashboard Publik + Peta |
| `/daftar-opk` | Daftar OPK Terverifikasi |
| `/lapor` | Form Laporan Masyarakat |
| `/lapor/status` | Cek Status Laporan |
| `/login` | Login Admin/Dinas |
| `/admin/dashboard` | Dashboard Eksekutif |
| `/admin/verifikasi` | Antrian Verifikasi + AI Review |
| `/admin/opk` | Data OPK Resmi |
| `/admin/laporan` | Statistik & Ekspor CSV |
| `/admin/ai/ringkasan-halaman` | Ringkasan Eksekutif AI |

---

## Akun Default

| Role | Email | Password |
|---|---|---|
| Superadmin | superadmin@siopk-badung.id | SiOPK@2025! |
| Admin | admin@siopk-badung.id | Admin@2025 |
| Verifikator | verifikator@siopk-badung.id | Verif@2025 |
| Petugas | petugas@siopk-badung.id | Petugas@2025 |

---

## Fitur AI

| Fitur | Provider |
|---|---|
| Auto-analisis laporan (urgency score 0–10) | Semua provider |
| Deteksi duplikat | Semua provider |
| Rekomendasi + saran verifikator | Semua provider |
| Chat asisten per laporan | Semua provider |
| Ringkasan eksekutif mingguan | Semua provider |
| Auto-klasifikasi OPK | Semua provider |

Ganti provider via `.env`: `AI_PROVIDER=deepseek`.

```bash
php artisan siopk:test-ai              # Test koneksi
php artisan siopk:analisis-semua        # Batch analisis
php artisan siopk:analisis-semua --force # Re-analisis semua
```

---

## Alur Verifikasi

```
Masyarakat Lapor → menunggu
    ↓
AnalisisOpkJob → AI (scoring + duplikat)
    ↓
ai_review → review_dinas
    ↓
Verifikator Review
  ├── Setujui  → disetujui  → muncul di Peta Publik
  └── Tolak    → ditolak    → notif WhatsApp pelapor
```

---

## Fase Pengembangan

| Fase | Status |
|---|---|
| 1 — Setup, database, seeder | ✅ Selesai |
| 2 — Auth & role-based access | ✅ Selesai |
| 3 — Form laporan publik | ✅ Selesai |
| 4 — Panel verifikasi | ✅ Selesai |
| 5 — Dashboard + WebGIS | ✅ Selesai |
| 6 — Integrasi AI multi-provider | ✅ Selesai |
| 7 — WhatsApp notification | ✅ Selesai |
| Phase 1–4 — Production readiness, performance, cleanup | ✅ Selesai |
| Phase 5 — Redis, CI/CD, load test, future-proofing | ✅ Selesai |

---

## Perintah Berguna

```bash
php artisan migrate:fresh --seed        # Reset + seed ulang
php artisan optimize:clear              # Clear semua cache
php artisan schedule:run                # Jalankan scheduler
php artisan queue:work                  # Worker queue (production)
```

### Docker

```bash
docker compose up -d                    # Start semua service
docker compose down                     # Stop semua service
docker compose exec app php artisan migrate --force
docker compose exec app php artisan config:cache
```

---

## Keamanan

- `.env` **tidak boleh di-commit** — sudah di `.gitignore`
- Rotasi API key jika pernah terekspos di Git history
- CSP headers enforced via `SecurityHeaders` middleware
- Rate limiting: login 5/menit, lapor 3/menit, API 30/menit

