<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FonnteWebhookController extends Controller
{
    public function deviceStatus(Request $request): \Illuminate\Http\JsonResponse
    {
        $data = $request->all();

        Log::warning('Fonnte device status changed', $data);

        $deviceName = $data['device'] ?? 'unknown';
        $status     = $data['status'] ?? 'unknown';

        if ($status === 'disconnected') {
            Log::error("FONNTE DEVICE DISCONNECTED: {$deviceName}");

            if (config('services.whatsapp.admin_wa')) {
                \App\Jobs\SendWhatsAppNotifJob::dispatch(
                    'laporan_diterima', // reuse notif type, just for alerting admin
                    config('services.whatsapp.admin_wa'),
                    'FONNTE-DEVICE',
                    "ALERT: Device {$deviceName} disconnected. Cek segera."
                );
            }
        }

        return response()->json(['status' => 'ok']);
    }

    public function incomingMessage(Request $request): \Illuminate\Http\JsonResponse
    {
        Log::info('Fonnte incoming message', $request->all());
        return response()->json(['status' => 'ok']);
    }
}
