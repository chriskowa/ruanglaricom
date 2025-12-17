<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\RaceCategory;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Carbon\Carbon;

class EventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get EO users
        $eos = User::where('role', 'eo')->get();
        
        if ($eos->isEmpty()) {
            $this->command->warn('No EO users found. Please run UserSeeder first.');
            return;
        }

        $eo1 = $eos->first();
        $eo2 = $eos->skip(1)->first() ?? $eos->first();

        // Event 1: Indonesia Run 2025
        $event1 = Event::firstOrCreate(
            ['slug' => 'indonesia-run-2025-jakarta'],
            [
                'user_id' => $eo1->id,
                'name' => 'Indonesia Run 2025',
                'slug' => 'indonesia-run-2025-jakarta',
                'short_description' => 'Event lari resmi dengan kategori 5K, 10K, dan 21K, fasilitas lengkap, jersey eksklusif, dan medali finisher.',
                'full_description' => 'Indonesia Run 2025 adalah event lari resmi yang diselenggarakan di Jakarta. Event ini menawarkan tiga kategori lari: 5K Fun Run untuk pemula, 10K Race untuk pelari menengah, dan 21K Half Marathon untuk pelari berpengalaman. Setiap peserta akan mendapatkan jersey eksklusif, medali finisher, dan akses ke fasilitas lengkap termasuk hydration station, medical support, dan bag drop area.',
                'start_at' => Carbon::now()->addMonths(3)->setTime(5, 30, 0),
                'end_at' => Carbon::now()->addMonths(3)->setTime(11, 0, 0),
                'location_name' => 'Gelora Bung Karno (GBK)',
                'location_address' => 'Jl. Pintu Satu Senayan, Jakarta Pusat, DKI Jakarta 10270',
                'location_lat' => -6.2275,
                'location_lng' => 106.8020,
                'hero_image_url' => 'https://via.placeholder.com/1200x600?text=Indonesia+Run+2025',
                'map_embed_url' => '<iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3964.1006816348025!2d106.802!3d-6.2275!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e69f14f0d5f9ff9%3A0x50e5afc1c8f7b0a1!2sGelora%20Bung%20Karno!5e0!3m2!1sid!2sid!4v0000000000000" width="100%" height="450" style="border:0;" allowfullscreen="" loading="lazy"></iframe>',
                'google_calendar_url' => 'https://www.google.com/calendar/render?action=TEMPLATE&text=Indonesia+Run+2025&dates=20250810T223000Z/20250810T040000Z&details=Event+lari+Indonesia+Run+2025+di+GBK,+Jakarta&location=Gelora+Bung+Karno,+Jakarta',
            ]
        );

        // Categories for Event 1
        if ($event1->categories()->count() === 0) {
            $event1->categories()->createMany([
                [
                    'name' => '5K Fun Run',
                    'distance_km' => 5.00,
                    'code' => '5K',
                    'quota' => 1000,
                    'min_age' => 13,
                    'max_age' => 99,
                    'cutoff_minutes' => 60,
                    'price_early' => 150000,
                    'price_regular' => 200000,
                    'price_late' => 250000,
                    'reg_start_at' => Carbon::now()->subDays(30),
                    'reg_end_at' => Carbon::now()->addMonths(2),
                    'is_active' => true,
                ],
                [
                    'name' => '10K Race',
                    'distance_km' => 10.00,
                    'code' => '10K',
                    'quota' => 800,
                    'min_age' => 15,
                    'max_age' => 99,
                    'cutoff_minutes' => 90,
                    'price_early' => 200000,
                    'price_regular' => 250000,
                    'price_late' => 300000,
                    'reg_start_at' => Carbon::now()->subDays(30),
                    'reg_end_at' => Carbon::now()->addMonths(2),
                    'is_active' => true,
                ],
                [
                    'name' => '21K Half Marathon',
                    'distance_km' => 21.10,
                    'code' => 'HM',
                    'quota' => 500,
                    'min_age' => 17,
                    'max_age' => 99,
                    'cutoff_minutes' => 180,
                    'price_early' => 300000,
                    'price_regular' => 350000,
                    'price_late' => 400000,
                    'reg_start_at' => Carbon::now()->subDays(30),
                    'reg_end_at' => Carbon::now()->addMonths(2),
                    'is_active' => true,
                ],
            ]);
        }

        // Event 2: Jakarta Night Run 2025
        $event2 = Event::firstOrCreate(
            ['slug' => 'jakarta-night-run-2025'],
            [
                'user_id' => $eo1->id,
                'name' => 'Jakarta Night Run 2025',
                'slug' => 'jakarta-night-run-2025',
                'short_description' => 'Lari malam di tengah kota Jakarta dengan rute yang diterangi lampu. Kategori 5K dan 10K dengan glow-in-the-dark accessories.',
                'full_description' => 'Jakarta Night Run 2025 adalah event lari malam yang unik di Jakarta. Peserta akan berlari di tengah kota dengan rute yang diterangi lampu-lampu kota. Setiap peserta akan mendapatkan glow-in-the-dark accessories, jersey neon, dan medali finisher khusus. Event ini cocok untuk semua kalangan, dari pemula hingga pelari berpengalaman.',
                'start_at' => Carbon::now()->addMonths(4)->setTime(18, 0, 0),
                'end_at' => Carbon::now()->addMonths(4)->setTime(22, 0, 0),
                'location_name' => 'Monumen Nasional (Monas)',
                'location_address' => 'Jl. Medan Merdeka Utara, Jakarta Pusat, DKI Jakarta 10110',
                'location_lat' => -6.1751,
                'location_lng' => 106.8650,
                'hero_image_url' => 'https://via.placeholder.com/1200x600?text=Jakarta+Night+Run+2025',
                'map_embed_url' => '<iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3966.935509989507!2d106.8650!3d-6.1751!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e69f5d2eafb2b39%3A0x37eefd8b0e4c8b8f!2sMonumen+Nasional!5e0!3m2!1sid!2sid!4v0000000000000" width="100%" height="450" style="border:0;" allowfullscreen="" loading="lazy"></iframe>',
                'google_calendar_url' => null,
            ]
        );

        // Categories for Event 2
        if ($event2->categories()->count() === 0) {
            $event2->categories()->createMany([
                [
                    'name' => '5K Fun Run',
                    'distance_km' => 5.00,
                    'code' => '5K',
                    'quota' => 500,
                    'min_age' => 13,
                    'max_age' => 99,
                    'cutoff_minutes' => 60,
                    'price_early' => 120000,
                    'price_regular' => 150000,
                    'price_late' => 180000,
                    'reg_start_at' => Carbon::now()->subDays(20),
                    'reg_end_at' => Carbon::now()->addMonths(3),
                    'is_active' => true,
                ],
                [
                    'name' => '10K Night Challenge',
                    'distance_km' => 10.00,
                    'code' => '10K',
                    'quota' => 300,
                    'min_age' => 15,
                    'max_age' => 99,
                    'cutoff_minutes' => 90,
                    'price_early' => 180000,
                    'price_regular' => 220000,
                    'price_late' => 260000,
                    'reg_start_at' => Carbon::now()->subDays(20),
                    'reg_end_at' => Carbon::now()->addMonths(3),
                    'is_active' => true,
                ],
            ]);
        }

        // Event 3: Surabaya Marathon 2025
        $event3 = Event::firstOrCreate(
            ['slug' => 'surabaya-marathon-2025'],
            [
                'user_id' => $eo2->id,
                'name' => 'Surabaya Marathon 2025',
                'slug' => 'surabaya-marathon-2025',
                'short_description' => 'Event lari terbesar di Surabaya dengan kategori lengkap dari 5K hingga Full Marathon 42K. Menyusuri landmark ikonik kota Surabaya.',
                'full_description' => 'Surabaya Marathon 2025 adalah event lari terbesar dan paling bergengsi di Surabaya. Event ini menawarkan kategori lengkap mulai dari 5K Fun Run untuk keluarga, 10K Race, 21K Half Marathon, hingga 42K Full Marathon untuk pelari profesional. Rute akan melewati landmark ikonik Surabaya seperti Tugu Pahlawan, Jembatan Suramadu, dan kawasan wisata kota. Setiap peserta akan mendapatkan race pack lengkap, jersey premium, medali finisher, dan e-certificate.',
                'start_at' => Carbon::now()->addMonths(5)->setTime(4, 0, 0),
                'end_at' => Carbon::now()->addMonths(5)->setTime(12, 0, 0),
                'location_name' => 'Tugu Pahlawan, Surabaya',
                'location_address' => 'Jl. Pahlawan, Alun-alun Contong, Bubutan, Surabaya, Jawa Timur 60175',
                'location_lat' => -7.2458,
                'location_lng' => 112.7378,
                'hero_image_url' => 'https://via.placeholder.com/1200x600?text=Surabaya+Marathon+2025',
                'map_embed_url' => '<iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3957.715509989507!2d112.7378!3d-7.2458!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2dd7f8b8b8b8b8b8%3A0x8b8b8b8b8b8b8b8b!2sTugu+Pahlawan!5e0!3m2!1sid!2sid!4v0000000000000" width="100%" height="450" style="border:0;" allowfullscreen="" loading="lazy"></iframe>',
                'google_calendar_url' => null,
            ]
        );

        // Categories for Event 3
        if ($event3->categories()->count() === 0) {
            $event3->categories()->createMany([
                [
                    'name' => '5K Family Run',
                    'distance_km' => 5.00,
                    'code' => '5K',
                    'quota' => 2000,
                    'min_age' => 10,
                    'max_age' => 99,
                    'cutoff_minutes' => 60,
                    'price_early' => 100000,
                    'price_regular' => 130000,
                    'price_late' => 160000,
                    'reg_start_at' => Carbon::now()->subDays(40),
                    'reg_end_at' => Carbon::now()->addMonths(4),
                    'is_active' => true,
                ],
                [
                    'name' => '10K Race',
                    'distance_km' => 10.00,
                    'code' => '10K',
                    'quota' => 1500,
                    'min_age' => 15,
                    'max_age' => 99,
                    'cutoff_minutes' => 90,
                    'price_early' => 180000,
                    'price_regular' => 220000,
                    'price_late' => 260000,
                    'reg_start_at' => Carbon::now()->subDays(40),
                    'reg_end_at' => Carbon::now()->addMonths(4),
                    'is_active' => true,
                ],
                [
                    'name' => '21K Half Marathon',
                    'distance_km' => 21.10,
                    'code' => 'HM',
                    'quota' => 1000,
                    'min_age' => 17,
                    'max_age' => 99,
                    'cutoff_minutes' => 180,
                    'price_early' => 280000,
                    'price_regular' => 330000,
                    'price_late' => 380000,
                    'reg_start_at' => Carbon::now()->subDays(40),
                    'reg_end_at' => Carbon::now()->addMonths(4),
                    'is_active' => true,
                ],
                [
                    'name' => '42K Full Marathon',
                    'distance_km' => 42.20,
                    'code' => 'FM',
                    'quota' => 500,
                    'min_age' => 18,
                    'max_age' => 99,
                    'cutoff_minutes' => 360,
                    'price_early' => 400000,
                    'price_regular' => 450000,
                    'price_late' => 500000,
                    'reg_start_at' => Carbon::now()->subDays(40),
                    'reg_end_at' => Carbon::now()->addMonths(4),
                    'is_active' => true,
                ],
            ]);
        }

        // Event 4: Bandung Trail Run 2025
        $event4 = Event::firstOrCreate(
            ['slug' => 'bandung-trail-run-2025'],
            [
                'user_id' => $eo2->id,
                'name' => 'Bandung Trail Run 2025',
                'slug' => 'bandung-trail-run-2025',
                'short_description' => 'Lari trail di kawasan pegunungan Bandung dengan pemandangan alam yang menakjubkan. Kategori 10K dan 21K trail.',
                'full_description' => 'Bandung Trail Run 2025 adalah event lari trail yang menantang di kawasan pegunungan Bandung. Peserta akan menikmati pemandangan alam yang menakjubkan sambil menaklukkan medan trail yang menantang. Event ini dirancang untuk pelari trail yang menyukai tantangan alam. Setiap peserta akan mendapatkan race pack trail, jersey teknik, medali finisher, dan akses ke fasilitas lengkap termasuk medical support dan hydration station.',
                'start_at' => Carbon::now()->addMonths(6)->setTime(6, 0, 0),
                'end_at' => Carbon::now()->addMonths(6)->setTime(14, 0, 0),
                'location_name' => 'Taman Hutan Raya Ir. H. Djuanda',
                'location_address' => 'Jl. Ir. H. Djuanda No.99, Ciburial, Kec. Cimenyan, Bandung, Jawa Barat 40198',
                'location_lat' => -6.8444,
                'location_lng' => 107.6378,
                'hero_image_url' => 'https://via.placeholder.com/1200x600?text=Bandung+Trail+Run+2025',
                'map_embed_url' => null,
                'google_calendar_url' => null,
            ]
        );

        // Categories for Event 4
        if ($event4->categories()->count() === 0) {
            $event4->categories()->createMany([
                [
                    'name' => '10K Trail',
                    'distance_km' => 10.00,
                    'code' => '10K',
                    'quota' => 200,
                    'min_age' => 16,
                    'max_age' => 99,
                    'cutoff_minutes' => 120,
                    'price_early' => 250000,
                    'price_regular' => 300000,
                    'price_late' => 350000,
                    'reg_start_at' => Carbon::now()->subDays(10),
                    'reg_end_at' => Carbon::now()->addMonths(5),
                    'is_active' => true,
                ],
                [
                    'name' => '21K Trail Challenge',
                    'distance_km' => 21.10,
                    'code' => '21K',
                    'quota' => 150,
                    'min_age' => 18,
                    'max_age' => 99,
                    'cutoff_minutes' => 240,
                    'price_early' => 350000,
                    'price_regular' => 400000,
                    'price_late' => 450000,
                    'reg_start_at' => Carbon::now()->subDays(10),
                    'reg_end_at' => Carbon::now()->addMonths(5),
                    'is_active' => true,
                ],
            ]);
        }

        $this->command->info('Events seeded successfully!');
        $this->command->info('Created ' . Event::count() . ' events with ' . RaceCategory::count() . ' categories.');
    }
}
