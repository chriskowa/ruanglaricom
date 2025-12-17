<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Wallet;
use App\Models\City;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $malangKota = City::where('name', 'Malang Kota')->first();
        $surabaya = City::where('name', 'Surabaya')->first();
        
        // Create Coach users
        $coaches = [
            [
                'name' => 'Coach Budi',
                'email' => 'coach@ruanglari.com',
                'password' => Hash::make('coach123'),
                'role' => 'coach',
                'city_id' => $malangKota?->id,
                'phone' => '081234567890',
                'package_tier' => 'pro',
            ],
            [
                'name' => 'Coach Sari',
                'email' => 'coach2@ruanglari.com',
                'password' => Hash::make('coach123'),
                'role' => 'coach',
                'city_id' => $surabaya?->id,
                'phone' => '081234567891',
                'package_tier' => 'business',
            ],
        ];

        foreach ($coaches as $coachData) {
            $coach = User::firstOrCreate(
                ['email' => $coachData['email']],
                $coachData
            );
            
            if (!$coach->wallet) {
                $wallet = Wallet::create([
                    'user_id' => $coach->id,
                    'balance' => 0,
                    'locked_balance' => 0,
                ]);
                $coach->update(['wallet_id' => $wallet->id]);
            }
        }

        // Create Runner users
        $runners = [
            [
                'name' => 'Runner Andi',
                'email' => 'runner@ruanglari.com',
                'password' => Hash::make('runner123'),
                'role' => 'runner',
                'city_id' => $malangKota?->id,
                'phone' => '081234567892',
                'package_tier' => 'basic',
            ],
            [
                'name' => 'Runner Rina',
                'email' => 'runner2@ruanglari.com',
                'password' => Hash::make('runner123'),
                'role' => 'runner',
                'city_id' => $surabaya?->id,
                'phone' => '081234567893',
                'package_tier' => 'pro',
            ],
            [
                'name' => 'Runner Joko',
                'email' => 'runner3@ruanglari.com',
                'password' => Hash::make('runner123'),
                'role' => 'runner',
                'city_id' => $malangKota?->id,
                'phone' => '081234567894',
                'package_tier' => 'basic',
            ],
        ];

        foreach ($runners as $runnerData) {
            $runner = User::firstOrCreate(
                ['email' => $runnerData['email']],
                $runnerData
            );
            
            if (!$runner->wallet) {
                $wallet = Wallet::create([
                    'user_id' => $runner->id,
                    'balance' => 0,
                    'locked_balance' => 0,
                ]);
                $runner->update(['wallet_id' => $wallet->id]);
            }
        }

        // Create Event Organizer users
        $eventOrganizers = [
            [
                'name' => 'EO Jakarta Marathon',
                'email' => 'eo@ruanglari.com',
                'password' => Hash::make('eo123'),
                'role' => 'eo',
                'city_id' => $surabaya?->id,
                'phone' => '081234567895',
                'package_tier' => 'business',
            ],
            [
                'name' => 'EO Surabaya Run',
                'email' => 'eo2@ruanglari.com',
                'password' => Hash::make('eo123'),
                'role' => 'eo',
                'city_id' => $surabaya?->id,
                'phone' => '081234567896',
                'package_tier' => 'pro',
            ],
        ];

        foreach ($eventOrganizers as $eoData) {
            $eo = User::firstOrCreate(
                ['email' => $eoData['email']],
                $eoData
            );
            
            if (!$eo->wallet) {
                $wallet = Wallet::create([
                    'user_id' => $eo->id,
                    'balance' => 0,
                    'locked_balance' => 0,
                ]);
                $eo->update(['wallet_id' => $wallet->id]);
            }
        }

        $this->command->info('Users created successfully!');
        $this->command->info('Coach: coach@ruanglari.com / coach123');
        $this->command->info('Runner: runner@ruanglari.com / runner123');
        $this->command->info('EO: eo@ruanglari.com / eo123');
    }
}
