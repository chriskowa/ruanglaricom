<?php

namespace Database\Seeders;

use App\Models\Package;
use Illuminate\Database\Seeder;

class PackageSeeder extends Seeder
{
    public function run(): void
    {
        $packages = [
            [
                'name' => 'LITE',
                'slug' => 'lite',
                'price' => 0,
                'description' => 'Budget-friendly, Fun Runs, Quick Reg. Cocok untuk komunitas kecil.',
                'features' => [
                    'Registrasi cepat online',
                    'Pembayaran praktis (VA, QR, e-wallet)',
                    'Landing page standar',
                    'Manajemen Peserta Export',
                ],
                'duration_days' => 365,
            ],
            [
                'name' => 'PRO',
                'slug' => 'pro',
                'price' => 2500000,
                'description' => 'Ideal untuk event menengah dengan kebutuhan komunikasi otomatis.',
                'features' => [
                    'Semua fitur LITE',
                    'WhatsApp otomatis (Blaster)',
                    'Channel komunitas & pacer',
                    'Race Results System',
                    'Manajemen BIB Prefix',
                ],
                'duration_days' => 365,
            ],
            [
                'name' => 'PREMIUM',
                'slug' => 'premium',
                'price' => 10000000,
                'description' => 'Untuk event besar & kompleks dengan dukungan penuh Race Director.',
                'features' => [
                    'Semua fitur PRO',
                    'Landing page custom',
                    'Monitoring real-time',
                    'Support Race Director',
                    'Custom Domain',
                ],
                'duration_days' => 365,
            ],
        ];

        foreach ($packages as $pkg) {
            Package::updateOrCreate(
                ['slug' => $pkg['slug']],
                $pkg
            );
        }
    }
}
