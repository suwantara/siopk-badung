<?php

namespace App\Console\Commands;

use App\Jobs\AnalisisOpkJob;
use App\Models\OpkLaporan;
use Illuminate\Console\Command;

class AnalisisSemuaOpkCommand extends Command
{
    protected $signature   = 'siopk:analisis-semua {--force : Analisis ulang semua, termasuk yang sudah punya score}';
    protected $description = 'Jalankan AI analisis untuk semua laporan yang belum punya AI score';

    public function handle(): int
    {
        $query = OpkLaporan::whereIn('status_verifikasi', ['menunggu', 'review_dinas', 'disetujui']);

        if (!$this->option('force')) {
            $query->whereNull('ai_urgency_score');
        }

        $total = $query->count();

        if ($total === 0) {
            $this->info('Tidak ada laporan yang perlu dianalisis.');
            return self::SUCCESS;
        }

        $this->info("Ditemukan {$total} laporan untuk dianalisis.");

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $processed = 0;

        $query->chunk(100, function ($laporans) use ($bar, &$processed) {
            foreach ($laporans as $laporan) {
                AnalisisOpkJob::dispatch($laporan->id);
                $bar->advance();

                if (config('queue.default') === 'sync') {
                    sleep(1);
                }
            }

            $processed += $laporans->count();
            $this->newLine();
            $this->info("Diproses {$processed} records...");
        });

        $bar->finish();
        $this->newLine();
        $this->info("✓ {$total} job AI berhasil di-dispatch.");

        return self::SUCCESS;
    }
}
