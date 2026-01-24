<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\Marketplace\MarketplaceCategory;
use App\Models\Marketplace\MarketplaceBrand;
use Illuminate\Support\Facades\DB;

class MarketplaceDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Categories Hierarchy
        $categories = [
            'Sepatu Lari' => [
                'icon' => 'fa-shoe-prints',
                'children' => ['Road Running', 'Trail Running', 'Daily Trainer', 'Race Day (Carbon)', 'Recovery', 'Spikes']
            ],
            'Pakaian Pria' => [
                'icon' => 'fa-tshirt',
                'children' => ['Jersey Lari', 'Celana Lari', 'Jaket Lari', 'Kaos Kaki', 'Compression', 'Singlet']
            ],
            'Pakaian Wanita' => [
                'icon' => 'fa-female',
                'children' => ['Jersey Lari', 'Sports Bra', 'Celana Lari', 'Jaket Lari', 'Kaos Kaki', 'Compression', 'Singlet', 'Hijab Sport']
            ],
            'Elektronik & Gadget' => [
                'icon' => 'fa-watch',
                'children' => ['Jam Tangan GPS', 'Heart Rate Monitor', 'Headphones', 'Running Pods', 'Massage Gun']
            ],
            'Nutrisi & Suplemen' => [
                'icon' => 'fa-nutrition',
                'children' => ['Energy Gel', 'Energy Bar', 'Hydration / Electrolytes', 'Recovery Drink', 'Vitamin & Mineral']
            ],
            'Aksesoris' => [
                'icon' => 'fa-hat-cowboy',
                'children' => ['Topi & Visor', 'Kacamata Lari', 'Hydration Vest & Belt', 'Arm Sleeve', 'Calf Sleeve', 'Bib Holder', 'Headband']
            ],
            'Slot & Tiket Lari' => [
                'icon' => 'fa-ticket',
                'children' => ['Bib Transfer', 'Jastip Race Pack', 'Tiket Early Bird']
            ],
        ];

        foreach ($categories as $parentName => $data) {
            $parent = MarketplaceCategory::firstOrCreate(
                ['slug' => Str::slug($parentName)],
                [
                    'name' => $parentName,
                    'icon' => $data['icon']
                ]
            );

            foreach ($data['children'] as $childName) {
                MarketplaceCategory::firstOrCreate(
                    ['slug' => Str::slug($childName)],
                    [
                        'name' => $childName,
                        'parent_id' => $parent->id,
                        'icon' => null
                    ]
                );
            }
        }

        // 2. Brands & Types
        $brands = [
            // International Shoes/Apparel
            ['name' => 'Nike', 'type' => 'International'],
            ['name' => 'Adidas', 'type' => 'International'],
            ['name' => 'Asics', 'type' => 'International'],
            ['name' => 'Hoka', 'type' => 'International'],
            ['name' => 'Saucony', 'type' => 'International'],
            ['name' => 'New Balance', 'type' => 'International'],
            ['name' => 'Brooks', 'type' => 'International'],
            ['name' => 'Mizuno', 'type' => 'International'],
            ['name' => 'Puma', 'type' => 'International'],
            ['name' => 'On Running', 'type' => 'International'],
            ['name' => 'Altra', 'type' => 'International'],
            ['name' => 'Salomon', 'type' => 'International'],
            ['name' => 'Skechers', 'type' => 'International'],
            ['name' => 'Under Armour', 'type' => 'International'],
            ['name' => 'Reebok', 'type' => 'International'],

            // Local Shoes/Apparel
            ['name' => '910 Nineten', 'type' => 'Local'],
            ['name' => 'Ortuseight', 'type' => 'Local'],
            ['name' => 'Mills', 'type' => 'Local'],
            ['name' => 'Specs', 'type' => 'Local'],
            ['name' => 'Ardiles', 'type' => 'Local'],
            ['name' => 'Spotec', 'type' => 'Local'],
            ['name' => 'Piero', 'type' => 'Local'],
            ['name' => 'League', 'type' => 'Local'],
            ['name' => 'Duraking', 'type' => 'Local'],
            ['name' => 'Avelio', 'type' => 'Local'],
            ['name' => 'Tiento', 'type' => 'Local'],
            ['name' => 'Atalon', 'type' => 'Local'],
            ['name' => 'Sub Jersey', 'type' => 'Local'],
            ['name' => 'Voltand', 'type' => 'Local'],

            // Electronics
            ['name' => 'Garmin', 'type' => 'Tech'],
            ['name' => 'Coros', 'type' => 'Tech'],
            ['name' => 'Suunto', 'type' => 'Tech'],
            ['name' => 'Polar', 'type' => 'Tech'],
            ['name' => 'Shokz', 'type' => 'Tech'],
            ['name' => 'Jabra', 'type' => 'Tech'],
            ['name' => 'Amazfit', 'type' => 'Tech'],
            ['name' => 'Samsung', 'type' => 'Tech'],
            ['name' => 'Apple', 'type' => 'Tech'],

            // Nutrition
            ['name' => 'GU Energy', 'type' => 'Nutrition'],
            ['name' => 'Maurten', 'type' => 'Nutrition'],
            ['name' => 'SIS (Science in Sport)', 'type' => 'Nutrition'],
            ['name' => 'Tailwind Nutrition', 'type' => 'Nutrition'],
            ['name' => 'Saltstick', 'type' => 'Nutrition'],
            ['name' => 'Strive', 'type' => 'Local Nutrition'], // Local
            ['name' => 'Honey Stinger', 'type' => 'Nutrition'],

            // Accessories
            ['name' => 'Ciele Athletics', 'type' => 'International'],
            ['name' => 'Goodr', 'type' => 'International'],
            ['name' => 'Oakley', 'type' => 'International'],
            ['name' => 'Compressport', 'type' => 'International'],
            ['name' => '2XU', 'type' => 'International'],
            ['name' => 'Nathan', 'type' => 'International'],
            ['name' => 'Salomon Gear', 'type' => 'International'],
            ['name' => 'Naked Sports', 'type' => 'International'],
            ['name' => 'Flipbelt', 'type' => 'International'],
        ];

        foreach ($brands as $brandData) {
            $brand = MarketplaceBrand::firstOrCreate(
                ['slug' => Str::slug($brandData['name'])],
                [
                    'name' => $brandData['name'],
                    'type' => $brandData['type'],
                    // Placeholder logo logic if needed, or leave null
                    'logo' => null 
                ]
            );

            // Auto-attach brands to categories (Logic kasar untuk seeding)
            // Ini opsional, tapi membantu agar filter berfungsi
            $this->attachBrandToCategories($brand);
        }
    }

    private function attachBrandToCategories($brand)
    {
        // Simple keyword matching to attach brands to likely categories
        $categoryKeywords = [
            'Sepatu Lari' => ['Nike', 'Adidas', 'Asics', 'Hoka', 'Saucony', 'New Balance', 'Brooks', 'Mizuno', 'Puma', 'On Running', 'Altra', 'Salomon', '910', 'Ortuseight', 'Mills', 'Specs', 'Ardiles', 'Spotec', 'League'],
            'Pakaian Pria' => ['Nike', 'Adidas', 'Asics', 'New Balance', 'Under Armour', '2XU', 'Compressport', 'Duraking', 'Tiento', 'Atalon', 'Sub Jersey'],
            'Elektronik & Gadget' => ['Garmin', 'Coros', 'Suunto', 'Polar', 'Shokz', 'Amazfit', 'Samsung', 'Apple'],
            'Nutrisi & Suplemen' => ['GU', 'Maurten', 'SIS', 'Tailwind', 'Saltstick', 'Strive', 'Honey'],
            'Aksesoris' => ['Ciele', 'Goodr', 'Oakley', 'Nathan', 'Naked', 'Flipbelt', 'Garmin', 'Coros']
        ];

        foreach ($categoryKeywords as $catName => $keywords) {
            foreach ($keywords as $keyword) {
                if (str_contains(strtolower($brand->name), strtolower($keyword)) || str_contains(strtolower($keyword), strtolower($brand->name))) {
                    $cat = MarketplaceCategory::where('name', $catName)->first();
                    if ($cat) {
                        // Use DB facade to insert into pivot table to avoid model relationship issues if not defined
                        DB::table('marketplace_brand_category')->insertOrIgnore([
                            'marketplace_category_id' => $cat->id,
                            'marketplace_brand_id' => $brand->id,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }
        }
    }
}
