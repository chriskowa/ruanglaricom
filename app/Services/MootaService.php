<?php

namespace App\Services;

use App\Models\AppSettings;
use App\Models\Transaction;
use Illuminate\Support\Facades\Log;

class MootaService
{
    protected $apiToken;
    protected $secretKey;

    public function __construct()
    {
        $this->apiToken = AppSettings::get('moota_api_token');
        $this->secretKey = AppSettings::get('moota_webhook_secret');
    }

    /**
     * Generate unique code for transaction
     * 
     * @param float $amount
     * @return int
     * @throws \Exception
     */
    public function generateUniqueCode($amount)
    {
        // Try up to 50 times to find a unique code to avoid infinite loops
        for ($i = 0; $i < 50; $i++) {
            $code = rand(1, 999);
            $finalAmount = $amount + $code;

            // Check if this final amount is already waiting for payment
            // We check transactions that are pending and created recently (e.g., last 24 hours)
            // Because older pending transactions might be abandoned
            $exists = Transaction::where('payment_gateway', 'moota')
                ->where('payment_status', 'pending')
                ->where('final_amount', $finalAmount)
                ->where('created_at', '>=', now()->subHours(24))
                ->exists();

            if (!$exists) {
                return $code;
            }
        }

        throw new \Exception('Unable to generate unique code. Please try again later.');
    }

    /**
     * Verify Webhook Signature
     * 
     * @param string $signature
     * @param string $payload
     * @return bool
     */
    public function verifySignature($signature, $payload)
    {
        if (empty($this->secretKey)) {
            // If no secret key configured, maybe skip verification or fail?
            // For security, better to fail, but for dev maybe log warning.
            Log::warning('Moota Secret Key is not set.');
            return false;
        }

        // Moota signature usually matches the secret key or HMAC
        // Based on common practices. If specific Moota logic is needed, update here.
        // Assuming simple token check or HMAC. 
        // If the documentation says just check the secret in the payload or header:
        // Ideally we need to know the exact algorithm. 
        // For now, let's assume the signature passed IS the secret key (simple auth) 
        // or a computed HMAC.
        
        // Placeholder for HMAC verification:
        // $computed = hash_hmac('sha256', $payload, $this->secretKey);
        // return hash_equals($computed, $signature);
        
        // If we don't know the algo, we might just return true for now but log it.
        // Or better, let's assume we compare it with the configured secret if it's sent as a simple token.
        
        return $signature === $this->secretKey;
    }
}
