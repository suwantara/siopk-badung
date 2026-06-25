# Changelog

Semua perubahan penting pada project SIOPK Badung dicatat di sini.
Format mengikuti [Keep a Changelog](https://keepachangelog.com/id-ID/1.1.0/).

---

## [Unreleased]

### Added
- **Docker Compose production setup** — `docker-compose.yml` + nginx/PHP/MySQL/Redis config (`docker/`)
- **Backup script** — `scripts/backup.sh` (mysqldump + storage tar + auto-cleanup 30 hari)
- **WhatsApp notification service** — F-16: notifikasi via WhatsApp Cloud API (laporan diterima/disetujui/ditolak)
- **Chart.js dashboard** — 4 chart interaktif di `/admin/laporan` (tren, kategori, kecamatan, kondisi)
- **Leaflet marker clustering** — `leaflet.markercluster` di admin + publik dashboard
- **Migration `010_add_composite_indexes`** — Composite: `(status_verifikasi, kondisi)`, `(status_verifikasi, ai_urgency_score)`, `(latitude, longitude)`
- **`PetaDataService`** — Service class untuk query peta JSON (DRY dari 2 controller duplikat)
- **`OpkStatsService`** — Service class untuk semua query statistik (dashboard, laporan, ringkasan eksekutif)
- **`CacheKeys` helper** — Konstanta cache key terpusat (`app/Helpers/CacheKeys.php`)
- **`OpkLaporanObserver`** — Observer untuk cleanup file storage saat `forceDeleted`
- **Migration `009_add_missing_indexes`** — Unique index `opk_categories.nomor`, index `opk_fotos.is_utama`
- **`Model::shouldBeStrict()`** — Deteksi N+1 query, lazy loading, missing fillable di dev environment
- **Foto management di admin edit OPK** — Upload, hapus, ganti foto utama dari panel admin

### Changed
- **`.env.example`** — `SESSION_DRIVER=database`, `CACHE_STORE=database`, `QUEUE_CONNECTION=database` + production guide section
- **`SideEffectHandler`** — Integrasi WhatsApp notification (F-16); invalidasi `SIDEBAR_COUNTS` + `RINGKASAN_EKSEKUTIF` di semua event
- **Admin dashboard + Publik dashboard** — Marker clustering dengan `leaflet.markercluster`
- **`/admin/laporan` view** — 4 Chart.js chart menggantikan tabel statis + KPI cards
- **Caching Kategori/Kecamatan** — `Cache::remember('kategori_list', 86400)` + `kecamatan_list` + `kecamatan_with_desa` di semua controller
- **`VerifikasiController`** — `setujui()` + `tolak()` → private `updateVerificationStatus()` (DRY, 70 baris → 0 duplikasi)
- **`OpkLaporan::generateKode()`** — `max('id')` + uniqid fallback → `count()` + `DB::transaction()` (KISS, atomic)
- **`DashboardController`** — Pakai `OpkStatsService::dashboardAdmin()`, hapus import unused (`User`, `Request`, `DB`)
- **`DashboardPublikController`** — Pakai `OpkStatsService::dashboardPublik()` + `PetaDataService`
- **`AiController`** — `ringkasanEksekutif()` pakai `OpkStatsService::ringkasanEksekutif()` + `CacheKeys`
- **`LaporanAdminController`** — `index()` pakai `OpkStatsService::laporanAdmin()`; `exportCsv()` hapus param `$request` unused
- **`LaporController`** — `index()` pakai cache kategori/kecamatan; AJAX endpoints validasi `required|exists:kecamatans,id`
- **`OpkController`** — `index()` + `edit()` pakai cache; `petaJson()` → delegasi ke `PetaDataService`; hapus storage cleanup inline
- **`SidebarComposer`** — Gunakan `CacheKeys::SIDEBAR_COUNTS`
- **`SideEffectHandler`** — Gunakan `CacheKeys::RINGKASAN_EKSEKUTIF`; sederhanakan ternary `$event->status`
- **Event dispatch** — `\App\Events\LaporanCreated` FQN → import `use App\Events\LaporanCreated` di LaporController + VerifikasiController
- **`User` model** — Cast `password => hashed` (Laravel 11); hapus explicit `Hash::make()` di `PenggunaController`; hapus unused `SoftDeletes` import
- **`AiOpkAnalyzer`** — `Http::connectTimeout(10)` + `retry(3, fn($attempt) => ...)`
- **`config/session.php`** — Default `SESSION_ENCRYPT=true`
- **`bootstrap/app.php`** — `E_DEPRECATED` suppression hanya untuk `MYSQL_ATTR_SSL_CA`, bukan global

### Fixed
- **Bug: `TestAiCommand`** — Config path `services.claude` → `services.ai.claude`
- **Race condition `generateKode()`** — Konversi ke `count()`-based + `DB::transaction()` untuk atomicity
- **Missing input validation** — AJAX `getDesaDinas`/`getDesaAdat` tanpa validasi `kecamatan_id`
- **Implicit nullable deprecation** — `AiOpkAnalyzer::callApi($maxTokens = null)` → `?int $maxTokens = null`

### Removed
- **`routes/web1.php`** — File route mati 112 baris (duplikat tanpa throttle middleware)
- **Import unused** — `DashboardController` (User, Request, DB); `User` model (SoftDeletes); `PenggunaController` (Hash)
- **Storage cleanup inline** — Dipindah ke `OpkLaporanObserver::forceDeleted()`

---

## [1.0.0] — 2025-01-01

### Added
- **Fase 1** — Setup project Laravel 11, database schema (10 tabel), seeders (kategori, wilayah, user)
- **Fase 2** — Autentikasi (login/logout), 5 role RBAC (`superadmin`, `admin`, `verifikator`, `petugas`, `publik`), `RoleMiddleware`
- **Fase 3** — Form laporan publik 5 langkah (identitas OPK, lokasi GPS, deskripsi budaya, media upload, data pelapor)
- **Fase 4** — Panel verifikasi Dinas (antrian, approve, reject, catatan, riwayat status)
- **Fase 5** — Dashboard eksekutif (KPI cards, chart kategori/kecamatan, prioritas pemeliharaan, WebGIS Leaflet)
- **Fase 6** — Integrasi Claude AI (scoring urgensi, deteksi duplikat, rekomendasi, chat asisten, ringkasan eksekutif, auto-klasifikasi)
- **`AnalisisOpkJob`** — Background job untuk analisis AI otomatis setiap laporan baru
- **`siopk:test-ai`** — Artisan command test koneksi AI
- **`siopk:analisis-semua`** — Artisan command batch analisis
- **Scheduled tasks** — Analisis otomatis tiap 30 menit, refresh cache ringkasan mingguan
