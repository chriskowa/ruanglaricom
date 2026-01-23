<?php

namespace App\Services;

use App\Models\AppSettings;
use App\Models\User;
use App\Models\Wallet;

class PlatformWalletService
{
    public function getPlatformUser(): User
    {
        $configuredUserId = AppSettings::get('platform_wallet_user_id');
        if ($configuredUserId) {
            $user = User::find($configuredUserId);
            if ($user) {
                return $user;
            }
        }

        $admin = User::where('role', 'admin')->orderBy('id')->first();
        if ($admin) {
            return $admin;
        }

        return User::orderBy('id')->firstOrFail();
    }

    public function getPlatformWallet(): Wallet
    {
        $user = $this->getPlatformUser();

        $wallet = $user->wallet;
        if ($wallet) {
            return $wallet;
        }

        return Wallet::create([
            'user_id' => $user->id,
            'balance' => 0,
            'locked_balance' => 0,
        ]);
    }

    public function getPlatformFeePercent(): float
    {
        return (float) AppSettings::get('platform_fee_percent', 5);
    }
}
