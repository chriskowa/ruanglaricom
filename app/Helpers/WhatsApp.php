<?php

namespace App\Helpers;

use App\Models\AppSettings;
use App\Models\WhatsAppLog;
use Illuminate\Support\Facades\Log;

class WhatsApp
{
    /**
     * Send WhatsApp message using Multi-Gateway Support & Failover.
     *
     * @param string|null $to Phone number
     * @param string $message Message body
     * @param string $category Category tag ('general', 'otp', 'reminder', 'transactional', 'broadcast')
     * @return void
     */
    public static function send(?string $to, string $message, string $category = 'general'): void
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

        $isActive = (bool) AppSettings::get('whatsapp_is_active', false);
        if (!$isActive) {
            return;
        }

        // Get candidates list
        $candidates = self::resolveGateways($category);
        if (empty($candidates)) {
            Log::warning("WhatsApp send skipped: No active WhatsApp gateway available for category '{$category}'");
            return;
        }

        // Try sending with candidates (Failover mechanism)
        $lastError = null;
        $sentSuccess = false;

        foreach ($candidates as $gateway) {
            $appkey = $gateway['app_key'] ?? '';
            $authkey = $gateway['auth_key'] ?? '';
            $gatewayName = $gateway['name'] ?? 'Default Gateway';

            if (!$appkey || !$authkey) {
                continue;
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
                $log = WhatsAppLog::create([
                    'to' => $to,
                    'gateway_name' => $gatewayName,
                    'category' => $category,
                    'message' => $message,
                    'status' => 'pending',
                ]);
            } catch (\Exception $e) {
                Log::warning('Failed to create WhatsApp DB log: ' . $e->getMessage());
            }

            if (app()->environment('testing')) {
                if ($log) {
                    try {
                        $log->update([
                            'status' => 'sent',
                            'http_code' => 200,
                            'response' => json_encode(['success' => true, 'message' => 'Testing mode: curl skipped']),
                        ]);
                    } catch (\Exception $e) {}
                }
                return;
            }

            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => 'https://wa.jituproperty.com/api/create-message',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $payload,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_SSL_VERIFYPEER => false,
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);

            if ($error || ($httpCode >= 400)) {
                $errMsg = $error ?: "HTTP Error {$httpCode}: {$response}";
                Log::error("WhatsApp Gateway [{$gatewayName}] failed to send to {$to}", [
                    'category' => $category,
                    'error' => $errMsg,
                ]);

                if ($log) {
                    try {
                        $log->update([
                            'status' => 'failed',
                            'http_code' => $httpCode,
                            'response' => $response,
                            'error_message' => $errMsg,
                        ]);
                    } catch (\Exception $e) {}
                }

                $lastError = $errMsg;
                // Continue loop to attempt failover with next candidate gateway
                continue;
            }

            // Success
            Log::info("WhatsApp message sent successfully via [{$gatewayName}]", [
                'to' => $to,
                'category' => $category,
                'http_code' => $httpCode,
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

            $sentSuccess = true;
            break; // Stop loop on success
        }

        if (!$sentSuccess && $lastError) {
            Log::error("All WhatsApp gateways failed for recipient {$to}. Last Error: {$lastError}");
        }
    }

    /**
     * Resolve candidate gateways based on Category & Active Status.
     *
     * @param string $category
     * @return array
     */
    public static function resolveGateways(string $category = 'general'): array
    {
        $rawGateways = AppSettings::get('whatsapp_gateways');
        $gateways = [];

        if ($rawGateways) {
            $decoded = is_array($rawGateways) ? $rawGateways : json_decode($rawGateways, true);
            if (is_array($decoded)) {
                $gateways = $decoded;
            }
        }

        // Filter active gateways
        $activeGateways = array_filter($gateways, function ($item) {
            return !empty($item['is_active']) && !empty($item['app_key']) && !empty($item['auth_key']);
        });

        if (!empty($activeGateways)) {
            // Filter by requested category
            $categoryMatches = array_filter($activeGateways, function ($item) use ($category) {
                $itemCat = $item['category'] ?? 'all';
                return $itemCat === 'all' || $itemCat === $category;
            });

            if (!empty($categoryMatches)) {
                // Shuffle matching gateways for Load Balancing (Round-Robin)
                $matchedList = array_values($categoryMatches);
                shuffle($matchedList);
                return $matchedList;
            }

            // If no specific category match, fallback to all active gateways
            $allActive = array_values($activeGateways);
            shuffle($allActive);
            return $allActive;
        }

        // Fallback to legacy single AppKey & AuthKey setting
        $legacyAppKey = AppSettings::get('whatsapp_app_key') ?: env('WHATSAPP_APPKEY');
        $legacyAuthKey = AppSettings::get('whatsapp_auth_key') ?: env('WHATSAPP_AUTHKEY');

        if ($legacyAppKey && $legacyAuthKey) {
            return [
                [
                    'name' => 'Default Gateway',
                    'category' => 'all',
                    'app_key' => $legacyAppKey,
                    'auth_key' => $legacyAuthKey,
                    'is_active' => true,
                ],
            ];
        }

        return [];
    }
}
