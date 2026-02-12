<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class JabarRunSeeder extends Seeder
{
    public function run()
    {
        $eo = User::where('role', 'eo')->first();

        if (! $eo) {
            $eo = User::factory()->create([
                'name' => 'Event Organizer',
                'email' => 'eo@ruanglari.com',
                'role' => 'eo',
            ]);
        }

        $event = Event::create([
            'user_id' => $eo->id,
            'name' => 'Jabar Run 2025',
            'slug' => 'jabar-run-2025',
            'short_description' => 'The biggest running event in West Java.',
            'full_description' => '<p>Join us for the Jabar Run 2025! Experience the scenic routes of Bandung and compete with runners from all over Indonesia.</p>',
            'location_name' => 'Gedung Sate, Bandung',
            'location_address' => 'Jl. Diponegoro No.22, Citarum, Kec. Bandung Wetan, Kota Bandung, Jawa Barat 40115',
            'start_at' => Carbon::parse('2025-07-13 05:00:00'),
            'end_at' => Carbon::parse('2025-07-13 11:00:00'),
            'registration_open_at' => Carbon::parse('2025-01-01 00:00:00'),
            'registration_close_at' => Carbon::parse('2025-06-30 23:59:59'),
            'template' => 'professional-city-run',
            'facilities' => ['Water Station', 'Medic', 'Refreshment', 'Medal', 'Jersey'],
            'addons' => [],
            'jersey_sizes' => ['XS', 'S', 'M', 'L', 'XL', 'XXL'],
            'theme_colors' => [
                'primary' => '#1a56db', // blue-600
                'secondary' => '#fbbf24', // amber-400
                'accent' => '#ef4444', // red-500
            ],
        ]);

        // Create Categories
        $event->categories()->createMany([
            [
                'name' => '5K Fun Run',
                'distance_km' => 5,
                'price_regular' => 150000,
                'quota' => 1000,
                'is_active' => true,
            ],
            [
                'name' => '10K Race',
                'distance_km' => 10,
                'price_regular' => 250000,
                'quota' => 500,
                'is_active' => true,
            ],
            [
                'name' => 'Half Marathon',
                'distance_km' => 21.1,
                'price_regular' => 400000,
                'quota' => 300,
                'is_active' => true,
            ],
        ]);

        $this->command->info('Jabar Run 2025 event seeded successfully.');
    }
}
