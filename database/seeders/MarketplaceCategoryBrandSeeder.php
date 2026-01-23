<?php

namespace Database\Seeders;

use App\Models\Marketplace\MarketplaceBrand;
use App\Models\Marketplace\MarketplaceCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MarketplaceCategoryBrandSeeder extends Seeder
{
    public function run()
    {
        $data = [
            'product_categories' => [
                [
                    'name' => 'Running Shoes',
                    'slug' => 'running-shoes',
                    'icon' => 'shoe-print',
                    'brands' => [
                        // International
                        ['name' => 'Nike', 'type' => 'International'],
                        ['name' => 'Adidas', 'type' => 'International'],
                        ['name' => 'ASICS', 'type' => 'International'],
                        ['name' => 'HOKA', 'type' => 'International'],
                        ['name' => 'New Balance', 'type' => 'International'],
                        ['name' => 'Saucony', 'type' => 'International'],
                        ['name' => 'Puma', 'type' => 'International'],
                        ['name' => 'Mizuno', 'type' => 'International'],
                        ['name' => 'Brooks', 'type' => 'International'],
                        ['name' => 'Altra', 'type' => 'International'],
                        ['name' => 'Topo Athletic', 'type' => 'International'],
                        ['name' => 'On Running', 'type' => 'International'],
                        ['name' => 'Salomon', 'type' => 'International'],
                        ['name' => 'Skechers', 'type' => 'International'],
                        ['name' => 'Under Armour', 'type' => 'International'],
                        ['name' => 'Reebok', 'type' => 'International'],
                        
                        // Local
                        ['name' => '910 Nineten', 'type' => 'Local'],
                        ['name' => 'Ortuseight', 'type' => 'Local'],
                        ['name' => 'Mills', 'type' => 'Local'],
                        ['name' => 'Ardiles', 'type' => 'Local'],
                        ['name' => 'Spotec', 'type' => 'Local'],
                        ['name' => 'Specs', 'type' => 'Local'],
                    ],
                ],
                [
                    'name' => 'Apparel',
                    'slug' => 'apparel',
                    'icon' => 'tshirt',
                    'brands' => [
                        ['name' => 'Nike', 'type' => 'International'],
                        ['name' => 'Adidas', 'type' => 'International'],
                        ['name' => '2XU', 'type' => 'International'],
                        ['name' => 'Lululemon', 'type' => 'International'],
                        ['name' => 'Salomon', 'type' => 'International'],
                        ['name' => 'Under Armour', 'type' => 'International'],
                        ['name' => 'Compressport', 'type' => 'International'],
                        ['name' => 'The North Face', 'type' => 'International'],
                        ['name' => 'Patagonia', 'type' => 'International'],
                        
                        // Local
                        ['name' => 'Tiento', 'type' => 'Local'],
                        ['name' => 'SUB Jersey', 'type' => 'Local'],
                        ['name' => 'AZA Wear', 'type' => 'Local'],
                        ['name' => 'Runhood', 'type' => 'Local'],
                        ['name' => 'Duraking', 'type' => 'Local'],
                        ['name' => 'Aspro', 'type' => 'Local'],
                        ['name' => 'Kniel', 'type' => 'Local'],
                        ['name' => 'Miniletics', 'type' => 'Local'],
                        ['name' => 'CoreNation', 'type' => 'Local'],
                    ],
                ],
                [
                    'name' => 'Watches & Tech',
                    'slug' => 'watches-tech',
                    'icon' => 'watch',
                    'brands' => [
                        ['name' => 'Garmin', 'type' => 'Tech'],
                        ['name' => 'Coros', 'type' => 'Tech'],
                        ['name' => 'Suunto', 'type' => 'Tech'],
                        ['name' => 'Polar', 'type' => 'Tech'],
                        ['name' => 'Apple', 'type' => 'Tech'],
                        ['name' => 'Samsung', 'type' => 'Tech'],
                        ['name' => 'Shokz', 'type' => 'Audio'],
                        ['name' => 'Jabra', 'type' => 'Audio'],
                        ['name' => 'Stryd', 'type' => 'Tech'],
                    ],
                ],
                [
                    'name' => 'Accessories',
                    'slug' => 'accessories',
                    'icon' => 'glasses',
                    'brands' => [
                        ['name' => 'Oakley', 'type' => 'Eyewear'],
                        ['name' => 'Goodr', 'type' => 'Eyewear'],
                        ['name' => 'Rudy Project', 'type' => 'Eyewear'],
                        ['name' => '100%', 'type' => 'Eyewear'],
                        ['name' => 'Salomon', 'type' => 'Gear'],
                        ['name' => 'Nathan', 'type' => 'Gear'],
                        ['name' => 'Ultimate Direction', 'type' => 'Gear'],
                        ['name' => 'Aonijie', 'type' => 'Gear'],
                        ['name' => 'Camelbak', 'type' => 'Gear'],
                        ['name' => 'Naked Sports', 'type' => 'Gear'],
                        ['name' => 'Injinji', 'type' => 'Socks'],
                        ['name' => 'Steigen', 'type' => 'Socks'],
                        ['name' => 'Balega', 'type' => 'Socks'],
                        ['name' => 'Feetures', 'type' => 'Socks'],
                        ['name' => 'Ciele', 'type' => 'Headwear'],
                        ['name' => 'Fractel', 'type' => 'Headwear'],
                        ['name' => 'Buff', 'type' => 'Headwear'],
                    ],
                ],
                [
                    'name' => 'Nutrition',
                    'slug' => 'nutrition',
                    'icon' => 'apple-alt',
                    'brands' => [
                        ['name' => 'GU Energy', 'type' => 'Nutrition'],
                        ['name' => 'Maurten', 'type' => 'Nutrition'],
                        ['name' => 'Saltstick', 'type' => 'Nutrition'],
                        ['name' => 'Strive', 'type' => 'Local'], // Local Nutrition
                        ['name' => 'SIS', 'type' => 'Nutrition'],
                        ['name' => 'Tailwind', 'type' => 'Nutrition'],
                        ['name' => 'Huma', 'type' => 'Nutrition'],
                        ['name' => 'Honey Stinger', 'type' => 'Nutrition'],
                        ['name' => 'Precision Hydration', 'type' => 'Nutrition'],
                    ],
                ],
                [
                    'name' => 'Recovery',
                    'slug' => 'recovery',
                    'icon' => 'heartbeat',
                    'brands' => [
                        ['name' => 'Theragun', 'type' => 'Tech'],
                        ['name' => 'Hyperice', 'type' => 'Tech'],
                        ['name' => 'Blackroll', 'type' => 'Gear'],
                        ['name' => 'TriggerPoint', 'type' => 'Gear'],
                        ['name' => 'Compex', 'type' => 'Tech'],
                        ['name' => 'Normatec', 'type' => 'Tech'],
                    ],
                ],
                [
                    'name' => 'Race Slots',
                    'slug' => 'race-slots',
                    'icon' => 'ticket-alt',
                    'brands' => [
                        ['name' => 'Borobudur Marathon', 'type' => 'Event'],
                        ['name' => 'Maybank Marathon Bali', 'type' => 'Event'],
                        ['name' => 'Pocari Sweat Run', 'type' => 'Event'],
                        ['name' => 'Jakarta Running Festival', 'type' => 'Event'],
                        ['name' => 'LPS Monas Half Marathon', 'type' => 'Event'],
                        ['name' => 'BTN Jakarta Run', 'type' => 'Event'],
                        ['name' => 'Ironman Indonesia', 'type' => 'Event'],
                        ['name' => 'BFI Run', 'type' => 'Event'],
                        ['name' => 'Milo Activ Indonesia', 'type' => 'Event'],
                        ['name' => 'IdeaRun', 'type' => 'Organizer'],
                        ['name' => 'RunID', 'type' => 'Organizer'],
                        ['name' => 'Runkost', 'type' => 'Organizer'],
                    ],
                ],
            ],
        ];

        foreach ($data['product_categories'] as $catData) {
            // Create or Update Category
            $category = MarketplaceCategory::updateOrCreate(
                ['slug' => $catData['slug']],
                [
                    'name' => $catData['name'],
                    'icon' => $catData['icon'] ?? 'box',
                ]
            );

            foreach ($catData['brands'] as $brandData) {
                // Create or Update Brand
                $brand = MarketplaceBrand::updateOrCreate(
                    ['name' => $brandData['name']],
                    [
                        'slug' => Str::slug($brandData['name']),
                        'type' => $brandData['type'],
                    ]
                );

                // Attach to Category
                $category->brands()->syncWithoutDetaching([$brand->id]);
            }
        }
    }
}
