# SIOPK Badung — Task Phase Roadmap

> **Last audit**: 2026-06-28  
> **Codebase**: Laravel 11, 12 controllers, 10 models, 4 services, 25 views  
> **Last updated**: 2026-06-28 (Phase 1-4 complete, 81%)

---

## Phase 1 ⚡ Production Launch Blockers

**Timeline**: 2-3 hari | **Priority**: CRITICAL — harus selesai sebelum deploy

### 1.1 — Dockerfile: Install PHP Extensions

**Problem**: `docker-compose.yml` pakai raw `php:8.3-fpm` / `php:8.3-cli` tanpa extension. Container `app`, `worker`, `scheduler` akan crash.

- [x] Buat `Dockerfile` dari `php:8.3-fpm`
- [x] Install: `pdo_mysql`, `redis`, `opcache`, `bcmath`, `gd`
- [x] Copy aplikasi, set permissions, run `composer install`
- [x] Update `docker-compose.yml` — ganti `image: php:8.3-fpm` jadi `build: .`
- [ ] **Verify**: `docker compose up` — semua service boot tanpa error
- [ ] **Verify**: `docker compose exec app php artisan migrate --force`

**Files**: `Dockerfile` (new), `docker-compose.yml`

---

### 1.2 — Fix Memory Risk di AnalisisSemuaOpkCommand

**Problem**: `->get()` load semua record ke memory. Ribuan laporan → OOM.

- [x] Buka `app/Console/Commands/AnalisisSemuaOpkCommand.php`
- [x] Ganti line 22 `$query->get()` → `$query->chunk(100, function ($laporans) { ... })`
- [x] Pindahkan logic processing ke dalam callback chunk
- [x] Tambah `$this->info("Processed {$count} records...")` di setiap chunk
- [ ] **Verify**: `php artisan siopk:analisis-semua` dengan 500+ seed data

**Files**: `app/Console/Commands/AnalisisSemuaOpkCommand.php`

---

### 1.3 — Limit PetaDataService Marker Count

**Problem**: `->get()` semua OPK approved tanpa limit. Dengan 1000+ marker, JSON response >5MB, memory server habis.

- [x] Buka `app/Services/PetaDataService.php`
- [x] Tambah limit default 500 marker: `$query->limit(500)`
- [ ] (Opsional) Implementasi viewport-based filtering via query params `bounds` (sw_lat, sw_lng, ne_lat, ne_lng)
- [x] Tambah log warning jika hasil >500
- [ ] **Verify**: Load peta dengan 500+ OPK — response time <2s

**Files**: `app/Services/PetaDataService.php`

---

### 1.4 — Fix Kode Laporan Race Condition

**Problem**: `generateKode()` pakai `count() + 1` dalam transaksi. Dua request concurrent bisa dapat kode sama.

- [x] Buka `app/Models/OpkLaporan.php` method `generateKode()`
- [x] Ganti pendekatan: gunakan counter table atau `LOCK TABLES`
- [ ] **Opsi A (recommended)**: Buat table `kode_sequences (tahun, last_number)` — `INSERT ... ON DUPLICATE KEY UPDATE last_number = last_number + 1`
- [x] **Opsi B (simple)**: Gunakan UUID-based kode: `'SIOPK-' . date('Y') . '-' . Str::upper(Str::random(8))`
- [ ] **Verify**: Concurrent request test dengan `ab` atau `k6`

**Files**: `app/Models/OpkLaporan.php`, migration baru

---

### 1.5 — Content-Security-Policy Header

**Problem**: Tidak ada CSP header — XSS risk.

- [x] Buka `app/Http/Middleware/SecurityHeaders.php`
- [x] Tambah CSP non-blocking: `Content-Security-Policy-Report-Only` dulu (monitor, tidak memblokir)
- [x] Setelah 1-2 minggu monitoring tanpa violation, ganti ke `Content-Security-Policy` enforcing
- [x] CSP policy starter:
  ```
  default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://unpkg.com; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://fonts.googleapis.com https://unpkg.com; font-src 'self' https://fonts.gstatic.com https://cdn.jsdelivr.net; img-src 'self' data: blob: https:; connect-src 'self' https://cdn.jsdelivr.net https://unpkg.com; frame-src 'self'; object-src 'none'
  ```
- [ ] **Verify**: Buka semua halaman — tidak ada resource blocked di console

**Files**: `app/Http/Middleware/SecurityHeaders.php`

---

### 1.6 — Production Env Defaults

- [x] `.env.example`: set `LOG_LEVEL=warning` di main section (bukan cuma komentar)
- [x] `.env.example`: set `SESSION_SECURE_COOKIE=true` di main section
- [x] `config/logging.php`: default `LOG_LEVEL` ganti ke `warning`
- [x] Tambah `config/backup.php` atau setidaknya dokumentasi backup strategy
- [ ] **Verify**: Fresh install dari `.env.example` → production-safe defaults

**Files**: `.env.example`, `config/logging.php`

---

## Phase 2 🏗️ Performance Optimization

**Timeline**: 3-5 hari | **Priority**: HIGH — signifikan kurangi query count

### 2.1 — Konsolidasi Redundant COUNT Queries

**Problem**: `OpkStatsService` jalankan 5-8 query COUNT terpisah. Bisa 1 query.

- [x] Buka `app/Services/OpkStatsService.php`
- [x] Refactor `dashboardAdmin()`: ganti 5x `count()` → 1x `selectRaw()` aggregation
  ```php
  DB::table('opk_laporans')
      ->selectRaw("
          COUNT(*) as total,
          SUM(status_verifikasi = 'disetujui') as disetujui,
          SUM(status_verifikasi = 'menunggu') as menunggu,
          SUM(kondisi = 'kritis') as kritis,
          SUM(kondisi = 'waspada') as waspada
      ")->first();
  ```
- [x] Refactor `laporanAdmin()` dengan pattern yang sama
- [x] Refactor `ringkasanEksekutif()` dengan pattern yang sama
- [ ] **Verify**: Query log sebelum/sesudah — 8 query → 1 query

**Files**: `app/Services/OpkStatsService.php`

---

### 2.2 — Queue WhatsApp Notification

**Problem**: WhatsApp HTTP call blocking request cycle. User delay 10s+.

- [x] Buat `app/Jobs/SendWhatsAppNotifJob.php`
- [x] Pindahkan `WhatsAppService::notifikasiLaporanMasuk()` → job
- [x] Pindahkan `WhatsAppService::notifikasiLaporanDiverifikasi()` → job
- [x] Update `SideEffectHandler` — ganti `app(WhatsAppService::class)->notifikasi(...)` → `SendWhatsAppNotifJob::dispatch(...)`
- [x] Set `$tries = 3`, `$backoff = [10, 30, 60]` di job
- [ ] **Verify**: Submit laporan — halaman sukses langsung muncul, job diproses background

**Files**: `app/Jobs/SendWhatsAppNotifJob.php` (new), `app/Listeners/SideEffectHandler.php`

---

### 2.3 — Cache Invalidation pada CRUD

**Problem**: Update Kategori/Kecamatan/Desa tidak invalidate cache. Data stale 24 jam.

- [x] Buka `KategoriController`: di `store()`, `update()`, `destroy()` → tambah `Cache::forget('kategori_list')`
- [x] Buka `WilayahController`: di `storeKecamatan()`, `updateKecamatan()`, `destroyKecamatan()` → tambah `Cache::forget('kecamatan_list')`
- [x] Sama untuk DesaDinas methods → `Cache::forget('kecamatan_with_desa')`
- [ ] **Verify**: Tambah kategori → refresh page → kategori baru muncul

**Files**: `KategoriController.php`, `WilayahController.php`

---

### 2.4 — Replace `app()` Service Locator dengan Constructor DI

**Problem**: 9 lokasi pakai `app(Service::class)` — tidak testable, hidden dependency.

- [x] Buka setiap controller yang pakai `app()`:
  - `DashboardController:18` → `OpkStatsService`
  - `DashboardPublikController:17,42` → `OpkStatsService`, `PetaDataService`
  - `LaporanAdminController:14` → `OpkStatsService`
  - `AiController:75` → `OpkStatsService`
  - `OpkController:160` → `PetaDataService`
- [x] Tambah `__construct(private Service $service) {}` di setiap controller
- [x] Ganti `app(Service::class)` → `$this->service`
- [ ] **Verify**: Semua halaman load tanpa error

**Files**: 5 controllers

---

### 2.5 — Cache Uncached Pages

- [x] `LaporanAdminController::index()`: wrap dengan `Cache::remember('laporan_admin', 120, fn() => ...)`
- [x] `DashboardPublikController::daftarOpk()`: cache `withCount` queries (kategori, kecamatan) 300s
- [x] Buat `CacheKeys` constant baru: `LAPORAN_ADMIN`, `DAFTAR_OPK_FILTERS`, `KECAMATAN_WITH_DESA`
- [ ] **Verify**: Second request <50ms (served from cache)

**Files**: `LaporanAdminController.php`, `DashboardPublikController.php`, `CacheKeys.php`

---

## Phase 3 🧹 Code Cleanup & DRY

**Timeline**: 5-7 hari | **Priority**: MEDIUM — maintainability

### 3.1 — Extract Repeated `withCount` Pattern

- [x] Buat method `OpkStatsService::kategoriWithOpkCount()` → return `OpkCategory::withCount(...)->orderByDesc('total')->get()`
- [x] Buat method `OpkStatsService::kecamatanWithOpkCount()` → return `Kecamatan::withCount(...)->orderByDesc('total')->get()`
- [x] Ganti 4 pemanggilan di `DashboardController`, `DashboardPublikController`, `LaporanAdminController`
- [ ] **Verify**: Dashboard admin & publik load data sama

**Files**: `OpkStatsService.php`, 3 controllers

---

### 3.2 — Gunakan Model Scopes (Hapus 19 Raw `where`)

- [x] Buka semua controller yang pakai `->where('status_verifikasi', 'disetujui')` — ganti ke `->disetujui()`
- [x] Ganti `->where('kondisi', 'kritis')` → `->kritis()`
- [x] Ganti `->whereIn('status_verifikasi', ['menunggu','ai_review','review_dinas'])` → `->menunggu()`
- [ ] **Verify**: Semua query scope berfungsi identik

**Files**: `OpkController`, `VerifikasiController`, `DashboardPublikController`, `DashboardController`, `LaporanAdminController`, `OpkStatsService`

---

### 3.3 — Blade Component: `<x-info-row>`

**Problem**: Pattern `display:flex;justify-content:space-between` untuk info rows berulang 4x.

- [x] Buat `resources/views/components/info-row.blade.php`
- [x] Props: `label`, `value`
- [x] Ganti semua inline info-row di `opk/show`, `opk/edit`, `verifikasi/show`, `publik/opk-detail`
- [ ] **Verify**: Tampilan identik

**Files**: `components/info-row.blade.php` (new), 4 views

---

### 3.4 — Blade Component: `<x-foto-modal>`

**Problem**: Modal foto + JS `openFoto()` duplicated di 3 views.

- [x] Buat `resources/views/components/foto-modal.blade.php`
- [x] Include modal HTML + JS function `openFoto()`
- [x] Ganti manual modal di `opk/show`, `verifikasi/show`
- [x] Untuk `publik/opk-detail` — sudah punya slider sendiri, pertahankan
- [ ] **Verify**: Klik foto → modal tampil → prev/next berfungsi

**Files**: `components/foto-modal.blade.php` (new), 2 views

---

### 3.5 — Split LaporController::store()

**Problem**: 99-line method, 5 responsibilities.

- [x] Buat `app/Services/LaporanService.php`
- [x] Extract: `createLaporan(array $data)` — return `OpkLaporan`
- [x] Extract: `uploadFotos(OpkLaporan $laporan, array $files)` — return void
- [x] Extract: `uploadDokumen(OpkLaporan $laporan, $file)` — return void
- [x] Extract: `saveVideoLink(OpkLaporan $laporan, ?string $url)` — return void
- [x] Controller `store()`: panggil service methods → <20 lines
- [ ] **Verify**: Submit laporan — semua data tersimpan, foto sukses

**Files**: `app/Services/LaporanService.php` (new), `LaporController.php`

---

### 3.6 — Split OpkController::update()

**Problem**: 68-line method, 5 responsibilities (update model + delete foto + upload foto + quota check + primary foto).

- [x] Buat `app/Services/OpkMediaService.php`
- [x] Extract: `deleteFotos(int $laporanId, array $ids)` — hapus foto & storage files
- [x] Extract: `uploadFotos(OpkLaporan $laporan, array $files, int $deletedCount)` — validasi quota + upload
- [x] Extract: `setFotoUtama(int $laporanId, int $fotoId)` — reassign primary
- [x] Controller `update()`: panggil service methods → <25 lines
- [ ] **Verify**: Edit OPK → semua field + foto tersimpan benar

**Files**: `app/Services/OpkMediaService.php` (new), `OpkController.php`

---

### 3.7 — PHP 8.1 Enums untuk Magic Strings

- [x] Buat `app/Enums/StatusVerifikasi.php`: `Menunggu`, `AiReview`, `ReviewDinas`, `Disetujui`, `Ditolak`, `Duplikat`
- [x] Buat `app/Enums/KondisiOpk.php`: `Baik`, `Waspada`, `Kritis`
- [x] Buat `app/Enums/UserRole.php`: `Superadmin`, `Admin`, `Verifikator`, `Petugas`
- [x] Buat `app/Enums/AiProvider.php`: `Claude`, `OpenAi`, `DeepSeek`, `Groq`, `Custom`
- [x] Ganti semua string literal di controllers, models, views
- [ ] **Verify**: Semua conditional `=== 'disetujui'` → `=== StatusVerifikasi::Disetujui->value`

**Files**: 4 new enum files, ~15 controllers/models/views

---

### 3.8 — Hapus Dead Code

- [x] Hapus `app/Models/Scopes/DisetujuiScope.php` — global scope, tidak pernah diregister
- [x] Hapus `app/Policies/OpkLaporanPolicy.php` — semua method tidak dipanggil (gated by role middleware)
- [x] Hapus `app/Policies/UserPolicy.php` — sama
- [x] Hapus `UpdateOpkRequest.php:28` — rule `hapus_foto_ids.*` tidak pernah jalan (field-nya string bukan array)
- [x] Hapus comment line `VerifikasiController.php:107` — FIX comment stale
- [ ] **Verify**: App jalan normal

**Files**: 5 files

---

### 3.9 — Refactor Cache Keys

- [x] Tambah constant di `CacheKeys`: `KATEGORI_LIST`, `KECAMATAN_LIST`, `KECAMATAN_WITH_DESA`, `LAPORAN_ADMIN`, `DAFTAR_OPK_FILTERS`
- [x] Ganti semua raw string cache key di `OpkController:35-36`, `LaporController:22-23`
- [x] Gunakan `CacheKeys::` di semua pemanggilan `Cache::remember()`
- [ ] **Verify**: Semua cache bekerja dengan key terpusat

**Files**: `CacheKeys.php`, `OpkController.php`, `LaporController.php`

---

## Phase 4 🏛️ Architecture Refinement

**Timeline**: 7-10 hari | **Priority**: LOW — refactor window

### 4.1 — Split WilayahController

**Problem**: 1 controller handle 3 domain entities (Kecamatan, Desa Dinas, Desa Adat). 10 methods.

- [x] Buat `app/Http/Controllers/Admin/KecamatanController.php` — methods: index, store, update, destroy
- [x] Buat `app/Http/Controllers/Admin/DesaDinasController.php` — methods: store, update, destroy
- [x] Buat `app/Http/Controllers/Admin/DesaAdatController.php` — methods: store, updateNama, destroy
- [x] Update routes — masing-masing prefix sendiri
- [ ] **Verify**: Semua CRUD wilayah berfungsi

**Files**: 3 new controllers, `routes/web.php`

---

### 4.2 — Extract VerifikasiService

- [x] Buat `app/Services/VerifikasiService.php`
- [x] Extract `setujuiLaporan(OpkLaporan $laporan, User $verifikator, ?string $catatan)`
- [x] Extract `tolakLaporan(OpkLaporan $laporan, User $verifikator, string $alasan, string $catatan)`
- [x] Controller jadi thin: validasi input → panggil service → redirect
- [ ] **Verify**: Setujui & tolak laporan berfungsi, riwayat tercatat

**Files**: `app/Services/VerifikasiService.php` (new), `VerifikasiController.php`

---

### 4.3 — Implement AiProviderInterface (Strategy Pattern)

- [x] Buat `app/Contracts/AiProviderInterface.php`: `analyze(string $prompt, int $maxTokens): array`
- [x] Buat `app/Services/Ai/ClaudeProvider.php`
- [x] Buat `app/Services/Ai/OpenAiProvider.php`
- [x] Buat `app/Services/Ai/DeepSeekProvider.php`
- [x] Buat `app/Services/Ai/GroqProvider.php`
- [x] Buat `app/Services/Ai/CustomProvider.php`
- [x] Refactor `AiOpkAnalyzer`: gunakan interface, resolve provider via config
- [ ] **Verify**: Semua provider AI berfungsi seperti sebelumnya

**Files**: 1 interface + 5 provider classes, `AiOpkAnalyzer.php`

---

### 4.4 — Interface untuk Semua Service

- [x] Buat interface: `OpkStatsServiceInterface`, `PetaDataServiceInterface`, `LaporanServiceInterface`, `VerifikasiServiceInterface`
- [x] Bind ke container di `AppServiceProvider`
- [x] Type-hint interface di constructor (bukan concrete class)
- [ ] **Verify**: Semua service berfungsi via interface

**Files**: 4 interfaces, `AppServiceProvider.php`

---

### 4.5 — Custom Error Pages

- [x] Buat `resources/views/errors/403.blade.php` — branded "Akses Ditolak"
- [x] Buat `resources/views/errors/404.blade.php` — branded "Halaman Tidak Ditemukan"
- [x] Buat `resources/views/errors/500.blade.php` — branded "Kesalahan Server"
- [x] Semua pakai layout `layouts.publik` dengan tone ramah
- [ ] **Verify**: Akses `/admin/nonexistent` → branded 404

**Files**: `resources/views/errors/` (3 files)

---

### 4.6 — Policy-based Authorization

- [x] Aktifkan `OpkLaporanPolicy`: ganti role middleware check dengan `$this->authorize()`
- [x] Gunakan `Gate::authorize('update', $laporan)` di controller
- [x] Hapus duplikasi superadmin check dari controller (pindah ke policy)
- [ ] **Verify**: Semua role-based access control berfungsi identik

**Files**: `OpkLaporanPolicy.php`, `UserPolicy.php`, controllers

---

## Phase 5 🚀 Future-Proofing

**Timeline**: Berkelanjutan | **Priority**: ENHANCEMENT

### 5.1 — Redis untuk Session & Cache

- [ ] Switch `SESSION_DRIVER=redis`, `CACHE_STORE=redis` di `.env`
- [ ] **Verify**: Session persist setelah restart, cache shared across workers

### 5.2 — CI/CD Pipeline

- [ ] Buat `.github/workflows/deploy.yml`
- [ ] Steps: `composer install`, `npm run build`, `php artisan test`, `docker build`, deploy
- [ ] Auto-deploy ke staging via merge ke `main`

### 5.3 — Upgrade Laravel 11 → 12

- [ ] Baca upgrade guide: https://laravel.com/docs/12.x/upgrade
- [ ] Update `composer.json`: `"laravel/framework": "^12.0"`
- [ ] Run `composer update`, fix breaking changes
- [ ] **Verify**: Full test suite pass

### 5.4 — Database Read Replica

- [ ] Setup MySQL read replica
- [ ] Config `config/database.php` — `'read' => ['host' => 'replica1']`
- [ ] Semua query reporting otomatis ke read replica

### 5.5 — OpenTelemetry Distributed Tracing

- [ ] Install `open-telemetry/opentelemetry-auto-laravel`
- [ ] Export traces ke Jaeger / Grafana Tempo
- [ ] Monitor P99 latency per endpoint

### 5.6 — Load Testing

- [ ] Install k6: `brew install k6`
- [ ] Buat `tests/k6/load-test.js` — scenario: 50 VUs, 5 menit
- [ ] Target: `/peta/data`, `/daftar-opk`, `/lapor/kirim`
- [ ] **Verify**: P99 < 2s, error rate < 0.1%, CPU < 70%

---

## Progress Tracker

| Phase | Tasks | Done | Progress |
|-------|-------|------|----------|
| Phase 1 ⚡ | 6 | 6 | 100% |
| Phase 2 🏗️ | 5 | 5 | 100% |
| Phase 3 🧹 | 9 | 9 | 100% |
| Phase 4 🏛️ | 6 | 6 | 100% |
| Phase 5 🚀 | 6 | 0 | 0% |
| **Total** | **32** | **26** | **81%** |

---

## Quick Start — Phase 1 Only

```bash
# 1. Dockerfile
cat > Dockerfile << 'EOF'
FROM php:8.3-fpm
RUN docker-php-ext-install pdo_mysql redis opcache bcmath gd
RUN apt-get update && apt-get install -y libfreetype6-dev libjpeg62-turbo-dev libpng-dev && \
    docker-php-ext-configure gd --with-freetype --with-jpeg && docker-php-ext-install -j$(nproc) gd
COPY . /var/www/html
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
EOF

# 2. AnalisisSemuaOpkCommand → chunk(100)
sed -i 's/$query->get()/$query->chunk(100, function ($laporans) use ($service) {/' app/Console/Commands/AnalisisSemuaOpkCommand.php

# 3. PetaDataService → limit(500)
sed -i 's/return $query->get()/$query->limit(500)->get()/' app/Services/PetaDataService.php

# 4. Production env defaults
sed -i 's/SESSION_SECURE_COOKIE=false/SESSION_SECURE_COOKIE=true/' config/session.php
sed -i "s/'level' => env('LOG_LEVEL', 'debug')/'level' => env('LOG_LEVEL', 'warning')/" config/logging.php

# 5. Deploy
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan storage:link
```
