<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\RunThread;
use App\Models\RunThreadParticipant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class RunConnectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Get or create some runner users
        $runners = User::where('role', 'runner')->take(5)->get();
        if ($runners->count() < 3) {
            $mockUsers = [
                ['name' => 'Aditya Pratama', 'email' => 'adit@ruanglari.com', 'username' => 'adit_run'],
                ['name' => 'Siti Aminah', 'email' => 'siti@ruanglari.com', 'username' => 'siti_run', 'gender' => 'female'],
                ['name' => 'Budi Santoso', 'email' => 'budi@ruanglari.com', 'username' => 'budi_run'],
                ['name' => 'Dewi Lestari', 'email' => 'dewi@ruanglari.com', 'username' => 'dewi_run', 'gender' => 'female']
            ];

            foreach ($mockUsers as $m) {
                User::create([
                    'name' => $m['name'],
                    'email' => $m['email'],
                    'username' => $m['username'],
                    'password' => Hash::make('password123'),
                    'role' => 'runner',
                    'gender' => $m['gender'] ?? 'male',
                    'is_active' => true,
                ]);
            }
            $runners = User::where('role', 'runner')->take(5)->get();
        }

        // 2. Clear existing threads to avoid duplicates
        RunThreadParticipant::query()->delete();
        RunThread::query()->delete();

        // 3. Define running threads around major hubs
        $demoThreads = [
            // Gelora Bung Karno (GBK), Jakarta
            [
                'title' => 'Slow Run Pagi GBK Loop',
                'description' => 'Lari santai 3 putaran luar GBK. Pace santai obrolan ringan. Kumpul di depan patung panahan. Wajib bawa botol hidrasi.',
                'type' => 'Casual Run',
                'run_distance_km' => 5.0,
                'pace_min' => '6:30',
                'pace_max' => '7:30',
                'start_date' => now()->addDays(1)->toDateString(),
                'start_time' => '06:00:00',
                'start_location_name' => 'Patung Panahan Senayan, GBK',
                'start_latitude' => -6.2185,
                'start_longitude' => 106.8015,
                'quota' => 15,
                'is_beginner_friendly' => true,
                'is_women_friendly' => false,
                'notes' => 'Pake baju Ruang Lari jika punya!'
            ],
            [
                'title' => 'Interval Speed Session GBK',
                'description' => 'Sesi interval untuk meningkatkan pace. 400m x 8 repetition. Target pace sub-5. Pemanasan mandiri.',
                'type' => 'Speed Session',
                'run_distance_km' => 8.0,
                'pace_min' => '4:30',
                'pace_max' => '5:00',
                'start_date' => now()->addDays(2)->toDateString(),
                'start_time' => '19:00:00',
                'start_location_name' => 'Stadion Madya GBK',
                'start_latitude' => -6.2163,
                'start_longitude' => 106.8018,
                'quota' => 8,
                'is_beginner_friendly' => false,
                'is_women_friendly' => false,
                'notes' => 'Bawa handuk kecil dan air minum.'
            ],
            // Monas, Jakarta
            [
                'title' => 'Monas Morning Run (Women Only)',
                'description' => 'Lari pagi santai keliling Monas khusus pelari wanita. Aman, seru, dan ada sesi foto-foto setelah lari.',
                'type' => 'Casual Run',
                'run_distance_km' => 4.5,
                'pace_min' => '7:00',
                'pace_max' => '8:00',
                'start_date' => now()->addDays(1)->toDateString(),
                'start_time' => '06:15:00',
                'start_location_name' => 'Pintu Monas IRTI',
                'start_latitude' => -6.1805,
                'start_longitude' => 106.8272,
                'quota' => 12,
                'is_beginner_friendly' => true,
                'is_women_friendly' => true,
                'notes' => 'Dresscode: Pink / Bright shirt.'
            ],
            // Lapangan Saparua, Bandung
            [
                'title' => 'Bandung Cool Run Saparua',
                'description' => 'Cari keringat pagi hari di trek Saparua yang rindang. Rute Saparua - Gedung Sate - Dago Loop.',
                'type' => 'Long Run',
                'run_distance_km' => 10.0,
                'pace_min' => '6:00',
                'pace_max' => '6:30',
                'start_date' => now()->addDays(3)->toDateString(),
                'start_time' => '05:45:00',
                'start_location_name' => 'Pintu Barat Lapangan Saparua',
                'start_latitude' => -6.9079,
                'start_longitude' => 107.6186,
                'quota' => 20,
                'is_beginner_friendly' => false,
                'is_women_friendly' => false,
                'notes' => 'Bawa uang kecil untuk jajan air kelapa sehabis lari.'
            ],
            // Niti Mandala Renon, Bali
            [
                'title' => 'Sunrise Run Renon Bali',
                'description' => 'Menikmati pagi yang asri di Renon. Lari santai 2 putaran diselingi obrolan seru.',
                'type' => 'Casual Run',
                'run_distance_km' => 6.0,
                'pace_min' => '6:15',
                'pace_max' => '7:00',
                'start_date' => now()->addDays(1)->toDateString(),
                'start_time' => '06:00:00',
                'start_location_name' => 'Monumen Bajra Sandhi, Renon',
                'start_latitude' => -8.6723,
                'start_longitude' => 115.2341,
                'quota' => 10,
                'is_beginner_friendly' => true,
                'is_women_friendly' => false,
                'notes' => 'Kumpul dekat tangga utama Monumen.'
            ],
            // Jakarta Kota Tua (Night Run)
            [
                'title' => 'Kota Tua Night Run',
                'description' => 'Mengeksplorasi keindahan heritage Kota Tua di malam hari. Start Kota Tua ke pelabuhan Sunda Kelapa.',
                'type' => 'Casual Run',
                'run_distance_km' => 7.0,
                'pace_min' => '6:00',
                'pace_max' => '7:00',
                'start_date' => now()->addDays(2)->toDateString(),
                'start_time' => '20:00:00',
                'start_location_name' => 'Halaman Stasiun Jakarta Kota',
                'start_latitude' => -6.1376,
                'start_longitude' => 106.8126,
                'quota' => 15,
                'is_beginner_friendly' => true,
                'is_women_friendly' => false,
                'notes' => 'Pake pakaian reflektif atau lampu safety!'
            ]
        ];

        foreach ($demoThreads as $idx => $t) {
            // Assign creator sequentially from runners list
            $creator = $runners[$idx % $runners->count()];

            $thread = RunThread::create(array_merge($t, [
                'creator_id' => $creator->id,
                'status' => 'open',
                'visibility' => 'public',
            ]));

            // Creator joins automatically
            RunThreadParticipant::create([
                'run_thread_id' => $thread->id,
                'user_id' => $creator->id,
                'status' => 'joined',
                'joined_at' => now()
            ]);

            // Add 1-3 additional mock participants to make the UI look alive
            $numParticipants = rand(1, 3);
            $joinedRunners = $runners->where('id', '!=', $creator->id)->random(min($numParticipants, $runners->count() - 1));
            
            foreach ($joinedRunners as $jr) {
                RunThreadParticipant::create([
                    'run_thread_id' => $thread->id,
                    'user_id' => $jr->id,
                    'status' => 'joined',
                    'joined_at' => now()
                ]);
            }
        }
    }
}
