<?php

namespace App\Helpers;

class WhatsApp
{
    public static function send(?string $to, string $message): void
    {
        if ($to === null) {
            return;
        }

        $to = trim($to);
        if ($to === '') {
            return;
        }

        // Remove all non-digits (e.g. "-", "+", " ", "(", ")", etc.)
        $normalized = preg_replace('/\D+/', '', $to);

        if ($normalized === '') {
            return;
        }

        // Format to Indonesian country code 62
        if (str_starts_with($normalized, '0')) {
            $normalized = '62' . substr($normalized, 1);
        } elseif (!str_starts_with($normalized, '62')) {
            $normalized = '62' . $normalized;
        }

        $to = $normalized;

        $isActive = (bool) \App\Models\AppSettings::get('whatsapp_is_active', false);
        if (! $isActive) {
            return;
        }

        $appkey = \App\Models\AppSettings::get('whatsapp_app_key') ?: env('WHATSAPP_APPKEY');
        $authkey = \App\Models\AppSettings::get('whatsapp_auth_key') ?: env('WHATSAPP_AUTHKEY');
        if (! $appkey || ! $authkey || ! $to) {
            return;
        }

        $payload = [
            'appkey' => $appkey,
            'authkey' => $authkey,
            'to' => $to,
            'message' => $message,
            'sandbox' => 'false',
        ];

        $log = null;
        try {
            $log = \App\Models\WhatsAppLog::create([
                'to' => $to,
                'message' => $message,
                'status' => 'pending',
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('Failed to create WhatsApp DB log: ' . $e->getMessage());
        }

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => 'https://wa.jituproperty.com/api/create-message',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_TIMEOUT => 30, // Increased timeout
            CURLOPT_SSL_VERIFYPEER => false, // Optional: if SSL issues occur on local/hosting
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        // Logging for debugging
        if ($error) {
            \Illuminate\Support\Facades\Log::error('WhatsApp API Connection Error', [
                'to' => $to,
                'error' => $error,
            ]);
            if ($log) {
                try {
                    $log->update([
                        'status' => 'failed',
                        'error_message' => $error,
                    ]);
                } catch (\Exception $e) {}
            }
        } else {
            \Illuminate\Support\Facades\Log::info('WhatsApp API Response', [
                'to' => $to,
                'http_code' => $httpCode,
                'response' => $response,
            ]);
            if ($log) {
                try {
                    $log->update([
                        'status' => 'sent',
                        'http_code' => $httpCode,
                        'response' => $response,
                    ]);
                } catch (\Exception $e) {}
            }
        }
    }
}
