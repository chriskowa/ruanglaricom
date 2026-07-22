<?php

namespace App\Services;

use App\Models\User;
use App\Models\OtpToken;
use App\Helpers\WhatsApp;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class OtpService
{
    /**
     * Generate and send OTP to a user via WhatsApp or Email.
     *
     * @param User $user
     * @return OtpToken
     * @throws \Exception
     */
    public function generateAndSend(User $user): OtpToken
    {
        // Check if user has requested OTP in the last 5 minutes
        $recentOtp = OtpToken::where('user_id', $user->id)
            ->where('created_at', '>=', now()->subMinutes(5))
            ->first();

        if ($recentOtp) {
            $diff = $recentOtp->created_at->addMinutes(5)->diffInSeconds(now());
            $minutes = ceil($diff / 60);
            throw new \Exception("Silakan tunggu {$minutes} menit lagi sebelum meminta OTP baru.");
        }

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        
        // Invalidate previous unused OTPs
        OtpToken::where('user_id', $user->id)
            ->where('used', false)
            ->update(['used' => true]);

        $otpToken = OtpToken::create([
            'user_id' => $user->id,
            'code' => $code,
            'expires_at' => now()->addMinutes(10),
            'used' => false,
        ]);

        $otpChannel = env('OTP_CHANNEL', 'whatsapp');
        $message = "Kode OTP RuangLari Anda: {$code} (berlaku 10 menit)";

        if ($otpChannel === 'email') {
            try {
                Mail::raw($message, function ($mail) use ($user) {
                    $mail->to($user->email)->subject('Kode OTP RuangLari');
                });
            } catch (\Exception $e) {
                Log::error('Email OTP failed: ' . $e->getMessage());
            }
        } else {
            WhatsApp::send($user->phone, $message);
        }

        return $otpToken;
    }

    /**
     * Verify OTP code for a user.
     *
     * @param User $user
     * @param string $code
     * @return bool
     */
    public function verify(User $user, string $code): bool
    {
        $otpToken = OtpToken::where('user_id', $user->id)
            ->where('code', $code)
            ->where('used', false)
            ->where('expires_at', '>', now())
            ->first();

        if ($otpToken) {
            $otpToken->update(['used' => true]);
            return true;
        }

        return false;
    }
}
