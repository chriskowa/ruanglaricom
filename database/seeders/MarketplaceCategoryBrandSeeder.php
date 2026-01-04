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
                    'id' => 'cat_001',
                    'name' => 'Running Shoes',
                    'slug' => 'running-shoes',
                    'brands' => [
                        ['id' => 'b_nike', 'name' => 'Nike', 'type' => 'International'],
                        ['id' => 'b_adidas', 'name' => 'Adidas', 'type' => 'International'],
                        ['id' => 'b_asics', 'name' => 'ASICS', 'type' => 'International'],
                        ['id' => 'b_hoka', 'name' => 'HOKA', 'type' => 'International'],
                        ['id' => 'b_newbalance', 'name' => 'New Balance', 'type' => 'International'],
                        ['id' => 'b_saucony', 'name' => 'Saucony', 'type' => 'International'],
                        ['id' => 'b_puma', 'name' => 'Puma', 'type' => 'International'],
                        ['id' => 'b_mizuno', 'name' => 'Mizuno', 'type' => 'International'],
                        ['id' => 'b_brooks', 'name' => 'Brooks', 'type' => 'International'],
                        ['id' => 'b_altra', 'name' => 'Altra', 'type' => 'International'],
                        ['id' => 'b_910', 'name' => '910 Nineten', 'type' => 'Local'],
                        ['id' => 'b_ortus', 'name' => 'Ortuseight', 'type' => 'Local'],
                        ['id' => 'b_mills', 'name' => 'Mills', 'type' => 'Local'],
                        ['id' => 'b_ardiles', 'name' => 'Ardiles', 'type' => 'Local'],
                    ],
                ],
                [
                    'id' => 'cat_002',
                    'name' => 'Jerseys',
                    'slug' => 'jerseys',
                    'brands' => [
                        ['id' => 'b_nike', 'name' => 'Nike', 'type' => 'International'],
                        ['id' => 'b_adidas', 'name' => 'Adidas', 'type' => 'International'],
                        ['id' => 'b_ua', 'name' => 'Under Armour', 'type' => 'International'],
                        ['id' => 'b_salomon', 'name' => 'Salomon', 'type' => 'International'],
                        ['id' => 'b_2xu', 'name' => '2XU', 'type' => 'International'],
                        ['id' => 'b_sub', 'name' => 'SUB Jersey', 'type' => 'Local'],
                        ['id' => 'b_duraking', 'name' => 'Duraking', 'type' => 'Local'],
                        ['id' => 'b_tiento', 'name' => 'Tiento', 'type' => 'Local'],
                        ['id' => 'b_aza', 'name' => 'AZA Wear', 'type' => 'Local'],
                        ['id' => 'b_runhood', 'name' => 'Runhood', 'type' => 'Local'],
                        ['id' => 'b_aspro', 'name' => 'Aspro', 'type' => 'Local'],
                        ['id' => 'b_kniel', 'name' => 'Kniel', 'type' => 'Local'],
                    ],
                ],
                [
                    'id' => 'cat_003',
                    'name' => 'Shorts',
                    'slug' => 'shorts',
                    'brands' => [
                        ['id' => 'b_nike', 'name' => 'Nike', 'type' => 'International'],
                        ['id' => 'b_adidas', 'name' => 'Adidas', 'type' => 'International'],
                        ['id' => 'b_2xu', 'name' => '2XU', 'type' => 'International'],
                        ['id' => 'b_compressport', 'name' => 'Compressport', 'type' => 'International'],
                        ['id' => 'b_lululemon', 'name' => 'Lululemon', 'type' => 'International'],
                        ['id' => 'b_tiento', 'name' => 'Tiento', 'type' => 'Local'],
                        ['id' => 'b_sub', 'name' => 'SUB Jersey', 'type' => 'Local'],
                        ['id' => 'b_sfidn', 'name' => 'SFIDN Fits', 'type' => 'Local'],
                        ['id' => 'b_aspro', 'name' => 'Aspro', 'type' => 'Local'],
                        ['id' => 'b_kniel', 'name' => 'Kniel', 'type' => 'Local'],
                    ],
                ],
                [
                    'id' => 'cat_004',
                    'name' => 'Accessories',
                    'description' => 'Watches, Eyewear, Hydration, Socks',
                    'slug' => 'accessories',
                    'brands' => [
                        ['id' => 'b_garmin', 'name' => 'Garmin', 'type' => 'Tech'],
                        ['id' => 'b_coros', 'name' => 'Coros', 'type' => 'Tech'],
                        ['id' => 'b_suunto', 'name' => 'Suunto', 'type' => 'Tech'],
                        ['id' => 'b_shokz', 'name' => 'Shokz', 'type' => 'Audio'],
                        ['id' => 'b_oakley', 'name' => 'Oakley', 'type' => 'Eyewear'],
                        ['id' => 'b_goodr', 'name' => 'Goodr', 'type' => 'Eyewear'],
                        ['id' => 'b_aonijie', 'name' => 'Aonijie', 'type' => 'Gear'],
                        ['id' => 'b_naked', 'name' => 'Naked Sports', 'type' => 'Gear'],
                        ['id' => 'b_injinji', 'name' => 'Injinji', 'type' => 'Socks'],
                        ['id' => 'b_steigen', 'name' => 'Steigen', 'type' => 'Socks'],
                    ],
                ],
                [
                    'id' => 'cat_005',
                    'name' => 'Race Slots',
                    'description' => 'Event organizers or Major Races',
                    'slug' => 'race-slots',
                    'brands' => [
                        ['id' => 'evt_borobudur', 'name' => 'Borobudur Marathon', 'type' => 'Event'],
                        ['id' => 'evt_bali', 'name' => 'Maybank Marathon Bali', 'type' => 'Event'],
                        ['id' => 'evt_pocari', 'name' => 'Pocari Sweat Run', 'type' => 'Event'],
                        ['id' => 'evt_jrf', 'name' => 'Jakarta Running Festival', 'type' => 'Event'],
                        ['id' => 'evt_lps', 'name' => 'LPS Monas Half Marathon', 'type' => 'Event'],
                        ['id' => 'evt_btn', 'name' => 'BTN Jakarta Run', 'type' => 'Event'],
                        ['id' => 'evt_ironman', 'name' => 'Ironman Indonesia', 'type' => 'Event'],
                        ['id' => 'org_idea', 'name' => 'IdeaRun', 'type' => 'Organizer'],
                        ['id' => 'org_runid', 'name' => 'RunID', 'type' => 'Organizer'],
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
                    // 'icon' can be mapped if needed
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

                // Attach to Category (syncWithoutDetaching to avoid duplicates if run multiple times)
                $category->brands()->syncWithoutDetaching([$brand->id]);
            }
        }
    }
}
