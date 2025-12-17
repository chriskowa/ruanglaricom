<?php

namespace Database\Seeders;

use App\Models\FeeConfig;
use Illuminate\Database\Seeder;

class FeeConfigSeeder extends Seeder
{
    public function run(): void
    {
        $feeConfigs = [
            ['module' => 'program', 'fee_percentage' => 2.00, 'is_active' => true],
            ['module' => 'marketplace', 'fee_percentage' => 2.00, 'is_active' => true],
            ['module' => 'event', 'fee_percentage' => 2.00, 'is_active' => true],
            ['module' => 'pacer', 'fee_percentage' => 2.00, 'is_active' => true],
            ['module' => 'kol', 'fee_percentage' => 2.00, 'is_active' => true],
        ];

        foreach ($feeConfigs as $config) {
            FeeConfig::firstOrCreate(
                ['module' => $config['module']],
                $config
            );
        }
    }
}
