<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Publik\{LaporController, DashboardPublikController};
use App\Http\Controllers\Admin\{
    DashboardController, VerifikasiController,
    OpkController, AiController,
    PenggunaController, LaporanAdminController,
    WilayahController, KategoriController
};

/*
|──────────────────────────────────────────────
| PUBLIC ROUTES
|──────────────────────────────────────────────
*/

// Home → dashboard publik
Route::get('/', [DashboardPublikController::class, 'index'])->name('publik.dashboard');

// Auth (rate-limited: 5 attempts per minute)
Route::get('/login',  [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])
     ->middleware('throttle:5,1')->name('login.post');
Route::post('/logout',[AuthController::class, 'logout'])->name('logout');

// Dashboard & peta publik
Route::get('/peta/data', [DashboardPublikController::class, 'petaJson'])->name('publik.peta.json');
Route::get('/opk/{opk}', [DashboardPublikController::class, 'showOpk'])->name('publik.opk.show');

// Form laporan publik
Route::prefix('lapor')->name('publik.lapor.')->group(function () {
    Route::get('/',       [LaporController::class, 'index'])->name('index');
    Route::post('/kirim', [LaporController::class, 'store'])
         ->middleware('throttle:3,1')->name('store');
    Route::get('/sukses', [LaporController::class, 'sukses'])->name('sukses');
    Route::get('/status', [LaporController::class, 'cekStatus'])->name('status');
});

// AJAX API publik
Route::prefix('api')->name('api.')->group(function () {
    Route::get('/desa-dinas', [LaporController::class, 'getDesaDinas'])->name('desa-dinas');
    Route::get('/desa-adat',  [LaporController::class, 'getDesaAdat'])->name('desa-adat');
});

/*
|──────────────────────────────────────────────
| ADMIN ROUTES
|──────────────────────────────────────────────
*/
Route::prefix('admin')->name('admin.')->middleware(['auth'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])
         ->name('dashboard')
         ->middleware('role:superadmin,admin,verifikator,petugas');

    // OPK Resmi
    Route::middleware('role:superadmin,admin,verifikator,petugas')->group(function () {
        Route::get('/opk',             [OpkController::class, 'index'])->name('opk.index');
        Route::get('/opk/arsip',       [OpkController::class, 'arsip'])->name('opk.arsip');
        Route::get('/opk/peta',        [OpkController::class, 'peta'])->name('opk.peta');
        Route::get('/opk/{laporan}',   [OpkController::class, 'show'])->name('opk.show');
    });

    Route::middleware('role:superadmin,admin')->group(function () {
        Route::get('/opk/{laporan}/edit',    [OpkController::class, 'edit'])->name('opk.edit');
        Route::put('/opk/{laporan}',         [OpkController::class, 'update'])->name('opk.update');
        Route::delete('/opk/{laporan}',      [OpkController::class, 'destroy'])->name('opk.destroy');
        Route::post('/opk/{id}/restore',     [OpkController::class, 'restore'])->name('opk.restore');
        Route::delete('/opk/{id}/force-delete', [OpkController::class, 'forceDelete'])->name('opk.force-delete');
    });

    // API Peta JSON admin
    Route::get('/peta/data', [OpkController::class, 'petaJson'])
         ->name('peta.data')
         ->middleware('role:superadmin,admin,verifikator,petugas');

    // Verifikasi
    Route::prefix('verifikasi')->name('verifikasi.')->middleware('role:superadmin,admin,verifikator')->group(function () {
        Route::get('/',                    [VerifikasiController::class, 'index'])->name('index');
        Route::get('/{laporan}',           [VerifikasiController::class, 'show'])->name('show');
        Route::post('/{laporan}/setujui',  [VerifikasiController::class, 'setujui'])->name('setujui');
        Route::post('/{laporan}/tolak',    [VerifikasiController::class, 'tolak'])->name('tolak');
        Route::post('/{laporan}/ai-score', [VerifikasiController::class, 'updateAiScore'])
             ->middleware('role:superadmin,admin')->name('ai-score');
    });

    // Laporan & Statistik
    Route::prefix('laporan')->name('laporan.')->middleware('role:superadmin,admin,verifikator,petugas')->group(function () {
        Route::get('/',       [LaporanAdminController::class, 'index'])->name('index');
        Route::get('/export', [LaporanAdminController::class, 'exportCsv'])->name('export');
    });

    // Pengguna
    Route::prefix('pengguna')->name('pengguna.')->middleware('role:superadmin,admin')->group(function () {
        Route::get('/',               [PenggunaController::class, 'index'])->name('index');
        Route::get('/tambah',         [PenggunaController::class, 'create'])->name('create');
        Route::post('/',              [PenggunaController::class, 'store'])->name('store');
        Route::get('/{pengguna}/edit',[PenggunaController::class, 'edit'])->name('edit');
        Route::put('/{pengguna}',     [PenggunaController::class, 'update'])->name('update');
        Route::post('/{pengguna}/toggle', [PenggunaController::class, 'toggleAktif'])->name('toggle');
        Route::delete('/{pengguna}',  [PenggunaController::class, 'destroy'])->name('destroy');
    });

    // Wilayah
    Route::prefix('wilayah')->name('wilayah.')->middleware('role:superadmin,admin')->group(function () {
        Route::get('/',                     [WilayahController::class, 'index'])->name('index');
        Route::post('/kecamatan',           [WilayahController::class, 'storeKecamatan'])->name('kecamatan.store');
        Route::put('/kecamatan/{kecamatan}', [WilayahController::class, 'updateKecamatan'])->name('kecamatan.update');
        Route::delete('/kecamatan/{kecamatan}', [WilayahController::class, 'destroyKecamatan'])->name('kecamatan.destroy');
        Route::post('/desa-dinas',          [WilayahController::class, 'storeDesaDinas'])->name('desa-dinas.store');
        Route::put('/desa-dinas/{desaDina}', [WilayahController::class, 'updateDesaDinas'])->name('desa-dinas.update');
        Route::delete('/desa-dinas/{desaDina}', [WilayahController::class, 'destroyDesaDinas'])->name('desa-dinas.destroy');
        Route::post('/desa-adat',           [WilayahController::class, 'storeDesaAdat'])->name('desa-adat.store');
        Route::put('/desa-adat/{desaAdat}',  [WilayahController::class, 'updateDesaAdat'])->name('desa-adat.update');
        Route::delete('/desa-adat/{desaAdat}', [WilayahController::class, 'destroyDesaAdat'])->name('desa-adat.destroy');
    });

    // Kategori OPK
    Route::prefix('kategori')->name('kategori.')->middleware('role:superadmin,admin')->group(function () {
        Route::get('/',             [KategoriController::class, 'index'])->name('index');
        Route::post('/',            [KategoriController::class, 'store'])->name('store');
        Route::put('/{kategori}',   [KategoriController::class, 'update'])->name('update');
        Route::delete('/{kategori}',[KategoriController::class, 'destroy'])->name('destroy');
    });

    // AI
    Route::prefix('ai')->name('ai.')->middleware('role:superadmin,admin,verifikator')->group(function () {
        Route::get('/ringkasan-halaman', fn() => view('admin.ai.ringkasan'))->name('ringkasan-halaman');
        Route::get('/ringkasan',         [AiController::class, 'ringkasanEksekutif'])
             ->middleware('throttle:10,1')->name('ringkasan');
        Route::post('/chat/{laporan}',   [AiController::class, 'chat'])
             ->middleware('throttle:20,1')->name('chat');
        Route::post('/re-analisis/{laporan}', [AiController::class, 'reAnalisis'])
             ->middleware('throttle:5,1')->name('re-analisis');
        Route::post('/klasifikasi',      [AiController::class, 'klasifikasi'])
             ->middleware('throttle:10,1')->name('klasifikasi');
        Route::post('/clear-cache',      [AiController::class, 'clearRingkasanCache'])
             ->middleware('role:superadmin,admin')->name('clear-cache');
    });
});
