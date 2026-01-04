<?php

namespace Database\Seeders;

use App\Models\AppSettings;
use App\Models\Marketplace\MarketplaceCategory;
use Illuminate\Database\Seeder;

class MarketplaceSeeder extends Seeder
{
    public function run()
    {
        $categories = [
            ['name' => 'Running Shoes', 'slug' => 'running-shoes', 'icon' => 'shoe-print'],
            ['name' => 'Jerseys', 'slug' => 'jerseys', 'icon' => 'tshirt'],
            ['name' => 'Shorts', 'slug' => 'shorts', 'icon' => 'shorts'],
            ['name' => 'Accessories', 'slug' => 'accessories', 'icon' => 'glasses'],
            ['name' => 'Race Slots', 'slug' => 'race-slots', 'icon' => 'ticket'],
        ];

        foreach ($categories as $cat) {
            MarketplaceCategory::updateOrCreate(['slug' => $cat['slug']], $cat);
        }

        // Set default commission
        AppSettings::set('marketplace_commission_percentage', '1');
    }
}
