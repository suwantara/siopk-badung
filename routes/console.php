<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

/*
|──────────────────────────────────────────────
| SIOPK Badung — Scheduled Tasks
|
| Untuk mengaktifkan scheduler di XAMPP Windows,
| tambahkan ke Windows Task Scheduler:
|   php C:\xampp\htdocs\siopk-badung\artisan schedule:run
| Jalankan setiap 1 menit.
|──────────────────────────────────────────────
*/

// Analisis laporan yang belum di-score — setiap 30 menit
Schedule::command('siopk:analisis-semua')
    ->everyThirtyMinutes()
    ->withoutOverlapping()
    ->runInBackground();

// Cek koneksi Fonnte — setiap 15 menit
Schedule::command('siopk:fonnte-check')
    ->everyFifteenMinutes()
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/fonnte-check.log'));

// Hapus cache ringkasan eksekutif — tiap Senin pagi (auto-refresh)
Schedule::call(function () {
    cache()->forget('siopk_ringkasan_eksekutif');
})->weeklyOn(1, '07:00');

// Artisan default
Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
