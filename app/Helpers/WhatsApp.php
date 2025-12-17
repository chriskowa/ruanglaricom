<?php

namespace App\Helpers;

class WhatsApp
{
    public static function send(string $to, string $message): void
    {
        $appkey = env('WHATSAPP_APPKEY');
        $authkey = env('WHATSAPP_AUTHKEY');
        if (!$appkey || !$authkey || !$to) return;

        $payload = [
            'appkey' => $appkey,
            'authkey' => $authkey,
            'to' => $to,
            'message' => $message,
            'sandbox' => 'false'
        ];

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => 'https://wa.jituproperty.com/api/create-message',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_TIMEOUT => 10,
        ]);
        curl_exec($ch);
        curl_close($ch);
    }
}

