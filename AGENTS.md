# SIOPK Badung — Codebase Context

## Project Identity

- **Name**: SIOPK Badung (Sistem Informasi Objek Pemajuan Kebudayaan Kabupaten Badung)
- **Stack**: Laravel 12, PHP 8.4+, MySQL, Bootstrap 5, Blade, Livewire/Flux (partial)
- **Scale**: Mid-size government application (70+ PHP files, 10 models, ~30 Blade views)
- **Pattern**: Modular Monolith — single Laravel app with service layer

## Directory Map

```
app/
├── Console/Commands/       # siopk:analisis-semua, siopk:fonnte-check, siopk:test-ai
├── Contracts/              # Interfaces (LaporanServiceInterface, OpkStatsServiceInterface, PetaDataServiceInterface, VerifikasiServiceInterface) — NOT USED by controllers
├── Enums/                  # StatusVerifikasi, KondisiOpk, UserRole, AiProvider
├── Events/                 # LaporanCreated, LaporanVerified, AiAnalysisCompleted
├── Helpers/CacheKeys.php   # Centralized cache key constants
├── Http/
│   ├── Controllers/
│   │   ├── Auth/AuthController.php
│   │   ├── Publik/         # DashboardPublikController, LaporController
│   │   ├── Admin/          # DashboardController, OpkController, VerifikasiController, AiController, PenggunaController, LaporanAdminController, WilayahController, KecamatanController, DesaDinasController, DesaAdatController, KategoriController
│   │   └── FonnteWebhookController.php
│   ├── Middleware/          # RoleMiddleware, SecurityHeaders
│   ├── Requests/            # StoreLaporanRequest, UpdateOpkRequest, StoreUserRequest
│   └── View/Composers/      # SidebarComposer
├── Jobs/                    # AnalisisOpkJob (Handles AI analysis), SendWhatsAppNotifJob (WhatsApp via Fonnte)
├── Listeners/               # SideEffectHandler (cache clear + WhatsApp dispatch on events)
├── Models/                  # OpkLaporan, OpkCategory, OpkFoto, OpkDokumen, OpkVideo, OpkRiwayatStatus, User, Kecamatan, DesaDinas, DesaAdat, Observers/OpkLaporanObserver
├── Policies/                # OpkLaporanPolicy, UserPolicy
├── Providers/               # AppServiceProvider
└── Services/
    ├── LaporanService.php      # Creates laporan, uploads fotos/dokumen/videos
    ├── VerifikasiService.php   # Approve/reject flow with transaction
    ├── OpkMediaService.php     # Admin media management (delete/upload/set-foto-utama)
    ├── OpkStatsService.php     # All dashboard statistics (raw SQL aggregates)
    ├── PetaDataService.php     # Map markers with cache versioning + bounds filtering
    ├── AiOpkAnalyzer.php       # Multi-provider AI service (Claude, OpenAI, DeepSeek, Groq, Custom)
    ├── WhatsAppService.php     # Fonnte API wrapper
    └── Ai/                     # AiProviderInterface, BaseProvider, AiProviderFactory, ClaudeProvider, OpenAiProvider, OpenAiCompatibleProvider, DeepSeekProvider, GroqProvider, CustomProvider
config/
    └── services.php            # AI provider configs + WhatsApp config
routes/
    └── web.php                 # Public + Admin routes (~152 lines)
```

## Database Tables (10)

| Table | Purpose |
|-------|---------|
| `opk_laporans` | Core — OPK reports (soft-deletable) |
| `opk_categories` | 10 OPK categories (1-10) |
| `opk_fotos` | Photos per report (max 10, one is utama) |
| `opk_dokumens` | Supporting documents (PDF/DOC) |
| `opk_videos` | External video links |
| `opk_riwayat_status` | Status change audit trail |
| `users` | Admin users (superadmin, admin, verifikator, petugas) |
| `kecamatans` | Districts |
| `desa_dinas` | Administrative villages (belongs to kecamatan) |
| `desa_adats` | Traditional villages (belongs to kecamatan) |

## Key Relationships (OpkLaporan)

- `kategori()` → OpkCategory
- `kecamatan()` → Kecamatan
- `desaDinas()` → DesaDinas
- `verifikator()` → User (`diverifikasi_oleh`)
- `duplikatDari()` → OpkLaporan (`ai_duplikat_of`)
- `fotos()` → hasMany OpkFoto (ordered by urutan)
- `fotoUtama()` → hasOne OpkFoto where is_utama=true
- `dokumens()` → hasMany OpkDokumen
- `videos()` → hasMany OpkVideo
- `riwayat()` → hasMany OpkRiwayatStatus (latest first)

## Key Scopes (OpkLaporan)

- `disetujui()` = status_verifikasi = 'disetujui'
- `kritis()` = kondisi = 'kritis'
- `waspada()` = kondisi = 'waspada'
- `menunggu()` = status IN ['menunggu', 'ai_review', 'review_dinas']
- `prioritas()` = ai_urgency_score >= 7

## Verification Workflow (Status Machine)

```
menunggu → ai_review → review_dinas → disetujui
                                    ↘ ditolak
                                    ↘ duplikat
```

1. Public submits laporan → status = `menunggu`
2. `AnalisisOpkJob` dispatched → status = `ai_review`
3. AI completes analysis → status = `review_dinas`
4. Verifikator reviews → status = `disetujui` / `ditolak` / `duplikat`

## Route Structure

| Prefix | Middleware | Purpose |
|--------|-----------|---------|
| `/` | — | Public dashboard |
| `/daftar-opk` | — | Public OPK listing |
| `/login` | throttle:5,1 | Auth |
| `/lapor/*` | throttle:3,1 | Public laporan form |
| `/api/*` | throttle:30,1 | AJAX (desa-dinas, desa-adat) |
| `/webhook/fonnte/*` | — | Fonnte webhook (no auth) |
| `/opk/{opk}` | — | Public OPK detail |
| `/peta/data` | — | Public map JSON |
| `/admin/*` | auth | All admin routes |
| `/admin/dashboard` | role:all | Admin dashboard |
| `/admin/opk/*` | role:all | OPK management |
| `/admin/verifikasi/*` | role:verifikator | Verification workflow |
| `/admin/pengguna/*` | role:admin | User management |
| `/admin/laporan/*` | role:all | Reports & stats |
| `/admin/wilayah/*` | role:admin | Territory management |
| `/admin/kategori/*` | role:admin | Category management |
| `/admin/ai/*` | role:verifikator | AI features |

## Role Hierarchy

| Role | Can |
|------|-----|
| `superadmin` | Everything including force-delete OPK |
| `admin` | CRUD OPK, users, wilayah, kategori; cannot force-delete |
| `verifikator` | Verify/reject OPK; view-only on OPK/users |
| `petugas` | View-only on dashboard, OPK, laporan, peta |

## AI Provider Architecture

- `AiProviderInterface` defines `analyze(prompt, maxTokens): array` and `isAvailable(): bool`
- `BaseProvider` implements retry logic (3x) with `Http::retry()`
- `AiProviderFactory::make($provider)` creates the right provider from config
- Provider-specific classes: `ClaudeProvider`, `OpenAiProvider` (extends `OpenAiCompatibleProvider`), `DeepSeekProvider` (extends `OpenAiCompatibleProvider`), `GroqProvider` (extends `OpenAiCompatibleProvider`), `CustomProvider` (extends `BaseProvider`, handles both OpenAI and Claude response formats via `CUSTOM_AI_TYPE`)
- `AiOpkAnalyzer` wraps providers with domain-specific methods: `analisisLaporan()`, `cekDuplikat()`, `ringkasanEksekutif()`, `klasifikasiOtomatis()`, `chatAsisten()`
- Config: `config/services.php` → `services.ai.{provider}` with env vars per provider

## Cache Strategy

- `CacheKeys` helper stores all cache key constants
- `PetaDataService` uses version-based invalidation (`Cache::increment('peta_data_version')`)
- Other caches use manual `Cache::forget()` in controllers (ad-hoc, no tags)
- Sidebar counts cached 5 min; dashboard 60s; filter lists 5 min; kategori/kecamatan 24h

## Important Conventions

1. **Naming**: Indonesian for business logic, English for technical terms
2. **Fonts**: Cormorant Garamond (headings) + Inter (body) — Balinese-inspired
3. **Colors**: Bootstrap 5 with custom Balinese palette (warm earth tones)
4. **Currency/numbers**: Indonesian locale (`APP_LOCALE=id`)
5. **Timezone**: `Asia/Makassar` (WITA)
6. **Soft Deletes**: OpkLaporan uses SoftDeletes; Observer cleans up files on `forceDelete`
7. **CSRF**: Laravel default middleware handles it
8. **Pagination**: Bootstrap 5 (`Paginator::useBootstrapFive()`)

## Known Issues (from Security Audit)

1. **CRITICAL**: `.env` file is in git with real API keys (DeepSeek, Fonnte) — must rotate
2. **CRITICAL**: Fonnte webhook has no signature validation
3. **HIGH**: CSP is `Report-Only`, not enforced
4. **HIGH**: Multiple catch blocks don't log exceptions at all
5. **HIGH**: `OpkController::update()` file operations not in DB transaction
6. **MEDIUM**: Contract interfaces defined but not used (controllers inject concrete classes)
7. **MEDIUM**: Enum values not consistently used — raw strings in services/jobs
8. **MEDIUM**: Duplicate controllers: WilayahController vs individual controllers
9. **LOW**: PII (WhatsApp numbers) logged in plain text
10. **LOW**: Log::warning used for normal business events
11. **LOW**: Test coverage very low (6 test files for 70+ source files)

## Common Tasks & Patterns

### Adding a new field to OpkLaporan
1. Add migration in `database/migrations/`
2. Add to `$fillable` in `OpkLaporan.php`
3. Add validation in `StoreLaporanRequest.php`
4. Update `LaporanService::createLaporan()`
5. If shown in admin, update `UpdateOpkRequest.php` and `OpkController::update()`
6. Clear relevant caches

### Adding a new AI provider
1. Create provider class extending `BaseProvider` or `OpenAiCompatibleProvider`
2. Add config section in `config/services.php`
3. Add `.env` variables
4. Add to `AiProviderFactory::make()` match statement
5. Add enum value in `AiProvider.php`

### Adding a new admin CRUD page
1. Create Controller extending `Controller`
2. Create Blade views in `resources/views/admin/<name>/`
3. Add routes in `routes/web.php` under admin group
4. Add sidebar link in `resources/views/layouts/app.blade.php`
5. Clear relevant caches

### Cache invalidation
- Use `CacheKeys` constants for key names
- `PetaDataService::invalidateCache()` increments version (atomic)
- For other caches: call `Cache::forget()` in controller after write operations
- Production uses `file` cache store; consider Redis for tags support

## Git Remotes

- **origin**: `infokbrc-alt/siopk-badung` — upstream (NEVER push here)
- **fork**: `suwantara/siopk-badung` — personal fork (always push & PR from here)
