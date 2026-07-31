<?php

namespace App\Http\Controllers;

use App\Jobs\SendWhatsAppNotifJob;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FonnteWebhookController extends Controller
{
    private function validateWebhook(Request $request): void
    {
        $expected = config('services.fonnte.webhook_token') ?: config('services.whatsapp.token');

        if (empty($expected)) {
            Log::warning('Fonnte webhook: webhook_token not configured, skipping signature validation.');

            return;
        }

        $received = $request->header('x-fonnte-token', '');

        if (! hash_equals($expected, $received)) {
            Log::warning('Fonnte webhook: invalid or missing signature token');
            abort(401);
        }
    }

    public function deviceStatus(Request $request): JsonResponse
    {
        $this->validateWebhook($request);

        $data = $request->all();

        Log::warning('Fonnte device status changed', $data);

        $deviceName = $data['device'] ?? 'unknown';
        $status = $data['status'] ?? 'unknown';

        if ($status === 'disconnected') {
            Log::error("FONNTE DEVICE DISCONNECTED: {$deviceName}");

            if (config('services.whatsapp.admin_wa')) {
                SendWhatsAppNotifJob::dispatch(
                    'laporan_diterima', // reuse notif type, just for alerting admin
                    config('services.whatsapp.admin_wa'),
                    'FONNTE-DEVICE',
                    "ALERT: Device {$deviceName} disconnected. Cek segera."
                );
            }
        }

        return response()->json(['status' => 'ok']);
    }

    public function incomingMessage(Request $request): JsonResponse
    {
        $this->validateWebhook($request);

        Log::info('Fonnte incoming message', $request->all());

        return response()->json(['status' => 'ok']);
    }
}
