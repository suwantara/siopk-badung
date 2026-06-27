<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FonnteHealthCheck extends Command
{
    protected $signature   = 'siopk:fonnte-check';
    protected $description = 'Cek apakah device Fonnte masih connected';

    public function handle(): int
    {
        $token = config('services.whatsapp.token', '');

        if (empty($token)) {
            $this->warn('FONNTE_TOKEN belum diset. Lewati.');
            return self::SUCCESS;
        }

        try {
            $response = Http::timeout(10)
                ->connectTimeout(5)
                ->withHeaders(['Authorization' => $token])
                ->get('https://api.fonnte.com/device');

            if ($response->successful()) {
                $data = $response->json();
                $status = $data['status'] ?? false;

                if ($status === 'connected' || $status === true) {
                    $this->info('Fonnte device: CONNECTED');
                    return self::SUCCESS;
                }

                Log::warning('Fonnte device not fully connected', $data);
                $this->warn('Fonnte device: ' . ($status ?: 'unknown status'));
            } else {
                Log::error('Fonnte health check failed', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
                $this->error('Fonnte health check FAILED');
            }
        } catch (\Exception $e) {
            Log::error('Fonnte health check exception', ['message' => $e->getMessage()]);
            $this->error('Fonnte health check error: ' . $e->getMessage());
        }

        return self::SUCCESS;
    }
}
