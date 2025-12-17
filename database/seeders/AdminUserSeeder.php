<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // Check if admin already exists
        $admin = User::where('email', 'admin@ruanglari.com')->first();
        
        if (!$admin) {
            $admin = User::create([
                'name' => 'Administrator',
                'email' => 'admin@ruanglari.com',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'referral_code' => 'ADMIN' . strtoupper(substr(md5(uniqid()), 0, 4)),
            ]);

            // Create wallet for admin
            $wallet = Wallet::create([
                'user_id' => $admin->id,
                'balance' => 0,
                'locked_balance' => 0,
            ]);

            $admin->update(['wallet_id' => $wallet->id]);

            $this->command->info('Admin user created successfully!');
            $this->command->info('Email: admin@ruanglari.com');
            $this->command->info('Password: admin123');
        } else {
            $this->command->info('Admin user already exists!');
        }
    }
}
