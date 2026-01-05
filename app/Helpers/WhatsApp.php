<?php

namespace App\Helpers;

class WhatsApp
{
    public static function send(string $to, string $message): void
    {
        $appkey = env('WHATSAPP_APPKEY');
        $authkey = env('WHATSAPP_AUTHKEY');
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
                'error' => $error
            ]);
        } else {
            \Illuminate\Support\Facades\Log::info('WhatsApp API Response', [
                'to' => $to,
                'http_code' => $httpCode,
                'response' => $response
            ]);
        }
    }
}
