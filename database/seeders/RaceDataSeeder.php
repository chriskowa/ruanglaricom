<?php

namespace Database\Seeders;

use App\Models\RaceDistance;
use App\Models\RaceType;
use Illuminate\Database\Seeder;

class RaceDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Seed Race Types
        $types = [
            ['name' => 'Road Run', 'slug' => 'road-run'],
            ['name' => 'Trail Run', 'slug' => 'trail-run'],
            ['name' => 'Ultra Marathon', 'slug' => 'ultra-marathon'],
            ['name' => 'Fun Run', 'slug' => 'fun-run'],
            ['name' => 'Virtual Run', 'slug' => 'virtual-run'],
            ['name' => 'Triathlon', 'slug' => 'triathlon'],
            ['name' => 'Obstacle Run', 'slug' => 'obstacle-run'],
        ];

        foreach ($types as $type) {
            RaceType::firstOrCreate(
                ['slug' => $type['slug']],
                $type
            );
        }

        $this->command->info('Race Types seeded successfully.');

        // Seed Race Distances
        $distances = [
            ['name' => '5K', 'slug' => '5k', 'distance_meter' => 5000],
            ['name' => '10K', 'slug' => '10k', 'distance_meter' => 10000],
            ['name' => 'Half Marathon (21K)', 'slug' => 'half-marathon', 'distance_meter' => 21097],
            ['name' => 'Marathon (42K)', 'slug' => 'marathon', 'distance_meter' => 42195],
            ['name' => 'Ultra 50K', 'slug' => 'ultra-50k', 'distance_meter' => 50000],
            ['name' => 'Ultra 100K', 'slug' => 'ultra-100k', 'distance_meter' => 100000],
            ['name' => 'Family Run 3K', 'slug' => 'family-run-3k', 'distance_meter' => 3000],
            ['name' => 'Kids Dash', 'slug' => 'kids-dash', 'distance_meter' => 1000],
        ];

        foreach ($distances as $distance) {
            RaceDistance::firstOrCreate(
                ['slug' => $distance['slug']],
                $distance
            );
        }

        $this->command->info('Race Distances seeded successfully.');
    }
}
