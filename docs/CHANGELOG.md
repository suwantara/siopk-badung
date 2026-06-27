# Changelog

Semua perubahan penting pada project SIOPK Badung dicatat di sini.
Format mengikuti [Keep a Changelog](https://keepachangelog.com/id-ID/1.1.0/).

---

## [Unreleased]

### Added
- **Docker Compose production setup** — `docker-compose.yml` + nginx/PHP/MySQL/Redis config (`docker/`)
- **Backup script** — `scripts/backup.sh` (mysqldump + storage tar + auto-cleanup 30 hari)
- **Fonnte WhatsApp notification** — `WhatsAppService` via Fonnte API (laporan diterima/disetujui/ditolak) + `SendWhatsAppNotifJob` queue
- **Fonnte monitoring** — `FonnteHealthCheck` scheduler + `FonnteWebhookController` + WA alert admin saat disconnect
- **Chart.js dashboard** — 4 chart interaktif di `/admin/laporan` (tren, kategori, kecamatan, kondisi)
- **Leaflet marker clustering** — `leaflet.markercluster` di admin + publik dashboard
- **Migration `010_add_composite_indexes`** — Composite: `(status_verifikasi, kondisi)`, `(status_verifikasi, ai_urgency_score)`, `(latitude, longitude)`
- **`PetaDataService`** — Service class untuk query peta JSON + viewport bounds filtering + limit 500 marker
- **`OpkStatsService`** — Service class untuk statistik + query consolidation (8 queries → 1 selectRaw)
- **`LaporanService`** — Extract `createLaporan()`, `uploadFotos()`, `uploadDokumen()`, `saveVideoLink()` dari controller
- **`OpkMediaService`** — Extract `deleteFotos()`, `uploadFotos()`, `setFotoUtama()` dari OpkController
- **`VerifikasiService`** — Extract `setujuiLaporan()`, `tolakLaporan()` dari VerifikasiController
- **AiProviderInterface strategy pattern** — `AiProviderFactory` + 5 providers (Claude, OpenAI, DeepSeek, Groq, Custom) di `app/Services/Ai/`
- **Service interfaces** — `OpkStatsServiceInterface`, `PetaDataServiceInterface`, `LaporanServiceInterface`, `VerifikasiServiceInterface` + container binding
- **PHP 8.1 Enums** — `StatusVerifikasi`, `KondisiOpk`, `UserRole`, `AiProvider` di `app/Enums/`
- **Blade Components** — `<x-info-row>`, `<x-foto-modal>` (DRY dari 4 view)
- **Custom error pages** — `403`, `404`, `500` branded dengan layout publik
- **`CacheKeys` helper** — Konstanta cache key terpusat (`app/Helpers/CacheKeys.php`)
- **`OpkLaporanObserver`** — Observer untuk cleanup file storage saat `forceDeleted`
- **Migration `009_add_missing_indexes`** — Unique index `opk_categories.nomor`, index `opk_fotos.is_utama`
- **`Model::shouldBeStrict()`** — Deteksi N+1 query, lazy loading, missing fillable di dev environment
- **Foto management di admin edit OPK** — Upload, hapus, ganti foto utama dari panel admin

### Changed
- **`.env.example`** — `SESSION_DRIVER=database`, `CACHE_STORE=database`, `QUEUE_CONNECTION=database`, `LOG_LEVEL=warning`, `SESSION_SECURE_COOKIE=true`, `FONNTE_TOKEN=`
- **`SideEffectHandler`** — Integrasi Fonnte notification via `SendWhatsAppNotifJob`; invalidasi `SIDEBAR_COUNTS` + `RINGKASAN_EKSEKUTIF` + `LAPORAN_ADMIN`
- **`AnalisisSemuaOpkCommand`** — `$query->get()` → `$query->chunk(100)` untuk mencegah OOM
- **`SecurityHeaders` middleware** — Tambah `Content-Security-Policy-Report-Only` header
- **`OpkLaporan::generateKode()`** — UUID-based: `SIOPK-YYYY-XXXXXXXX` → eliminate race condition
- **`config/logging.php`** — Default `LOG_LEVEL` dari `debug` → `warning` (production-safe)
- **All controllers** — `app(Service::class)` → constructor DI (`private readonly Service`)
- **Controller scope queries** — Raw `->where('status_verifikasi', 'disetujui')` → model scopes (`->disetujui()`)
- **Cache invalidation** — `KategoriController` + `WilayahController` forget cache on CRUD
- **`LaporanAdminController::index()`** — Wrap dengan `Cache::remember(laporan_admin, 120)`
- **`DashboardPublikController::daftarOpk()`** — Cache `withCount` filter queries 300s
- **`AiOpkAnalyzer`** — Delegate API calls ke `AiProviderInterface`; `callApi()` simplified
- **`VerifikasiController`** — Gunakan `VerifikasiService`; `setujui()` + `tolak()` jadi thin
- **`OpkController::update()`** — Gunakan `OpkMediaService` (68 baris → 28 baris)
- **`LaporController::store()`** — Gunakan `LaporanService` (99 baris → 25 baris)
- **`WilayahController`** — Split ke `KecamatanController` + `DesaDinasController` + `DesaAdatController`
- **`PenggunaController`** — `$this->authorize()` gantikan inline role checks
- **`routes/web.php`** — Tambah Fonnte webhook routes; update Wilayah controller references
- **`routes/console.php`** — Tambah `siopk:fonnte-check` setiap 15 menit
- **Admin dashboard + Publik dashboard** — Marker clustering dengan `leaflet.markercluster`
- **`/admin/laporan` view** — 4 Chart.js chart menggantikan tabel statis + KPI cards
- **Caching Kategori/Kecamatan** — `Cache::remember()` di semua controller
- **`config/services.php`** — Multi-provider AI config; WhatsApp → Fonnte token
- **`User` model** — Cast `password => hashed` (Laravel 11); hapus explicit `Hash::make()`

### Fixed
- **Bug: Fonnte invalid token** — `withToken()` → `withHeaders(['Authorization' => ...])` (Fonnte tidak pakai Bearer prefix)
- **Bug: Duplicate event listener** — Hapus manual `Event::listen()` di `AppServiceProvider` (auto-discovery already handles)
- **Bug: Missing `OpkLaporan` import** — `LaporController` kehilangan import saat refactor
- **Bug: `var(--hijau)` dalam `@php` block** — 8 file Blade fixed (CSS vars harus di-quote)
- **Bug: `TestAiCommand`** — Config path `services.claude` → `services.ai.claude`
- **Race condition `generateKode()`** — UUID-based (`Str::upper(Str::random(8))`)
- **Missing input validation** — AJAX `getDesaDinas`/`getDesaAdat` tanpa validasi `kecamatan_id`
- **Implicit nullable deprecation** — `AiOpkAnalyzer::callApi($maxTokens = null)` → `?int $maxTokens = null`

### Removed
- **`routes/web1.php`** — File route mati 112 baris
- **`DisetujuiScope.php`** — Global scope tidak pernah diregister
- **Stale `hapus_foto_ids.*` rule** — Never executed (field is string, not array)
- **Import unused** — `DashboardController`, `User` model, `PenggunaController`
- **Storage cleanup inline** — Dipindah ke `OpkLaporanObserver::forceDeleted()`
- **Manual `Event::listen()` entries** — Duplikat karena auto-discovery

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
