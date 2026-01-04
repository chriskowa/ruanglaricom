<?php

namespace Database\Seeders;

use App\Models\MasterWorkout;
use Illuminate\Database\Seeder;

class MasterWorkoutSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $workouts = [
            // 🟢 Easy & Recovery (Base)
            [
                'type' => 'easy_run',
                'title' => 'Easy Run (5K)',
                'description' => 'Lari santai di Zona 2. Fokus pada kenyamanan dan percakapan lancar.',
                'default_distance' => 5.00,
                'default_duration' => '00:35:00',
                'intensity' => 'low',
            ],
            [
                'type' => 'easy_run',
                'title' => 'Recovery Run (3K)',
                'description' => 'Lari sangat ringan & singkat untuk pemulihan aktif pasca-latihan keras.',
                'default_distance' => 3.00,
                'default_duration' => '00:20:00',
                'intensity' => 'low',
            ],
            [
                'type' => 'easy_run',
                'title' => 'Shakeout Run',
                'description' => 'Lari 15-20 menit sangat santai dengan beberapa strides ringan. Cocok untuk H-1 Race.',
                'default_distance' => 3.00,
                'default_duration' => '00:20:00',
                'intensity' => 'low',
            ],

            // 🔵 Long Runs (Endurance)
            [
                'type' => 'long_run',
                'title' => 'Long Slow Distance (10K)',
                'description' => 'Membangun endurance dengan pace konstan yang nyaman. Jangan terburu-buru.',
                'default_distance' => 10.00,
                'default_duration' => '01:10:00',
                'intensity' => 'medium',
            ],
            [
                'type' => 'long_run',
                'title' => 'Progression Long Run (12K)',
                'description' => 'Dimulai pelan, tingkatkan pace secara bertahap setiap 3-4km. Finish strong.',
                'default_distance' => 12.00,
                'default_duration' => '01:20:00',
                'intensity' => 'high',
            ],
            [
                'type' => 'long_run',
                'title' => 'Long Run w/ Surges',
                'description' => 'Lari jauh diselingi percepatan 1 menit (Surge) setiap 10 menit lari santai.',
                'default_distance' => 15.00,
                'default_duration' => '01:45:00',
                'intensity' => 'high',
            ],

            // 🟡 Speed & Power (Intervals/Tempo)
            [
                'type' => 'tempo',
                'title' => 'Tempo Run (5K)',
                'description' => '2km pemanasan + 5km Tempo (Ambrol Laktat/Comfortably Hard) + 1km pendinginan.',
                'default_distance' => 8.00,
                'default_duration' => '00:50:00',
                'intensity' => 'high',
            ],
            [
                'type' => 'interval',
                'title' => 'Interval 400m (10x)',
                'description' => '10x 400m di pace 3K/5K. Istirahat 90 detik jog ringan antar set.',
                'default_distance' => 7.00, // Termasuk warmup/cooldown
                'default_duration' => '00:45:00',
                'intensity' => 'high',
            ],
            [
                'type' => 'interval',
                'title' => 'Interval 1K (5x)',
                'description' => '5x 1000m di pace 10K. Istirahat 2-3 menit jog ringan antar set.',
                'default_distance' => 9.00, // Termasuk warmup/cooldown
                'default_duration' => '01:00:00',
                'intensity' => 'high',
            ],
            [
                'type' => 'interval',
                'title' => 'Fartlek 1/1',
                'description' => '20 menit lari dengan pola: 1 menit cepat / 1 menit lambat.',
                'default_distance' => 5.00,
                'default_duration' => '00:30:00',
                'intensity' => 'medium',
            ],
            [
                'type' => 'interval',
                'title' => 'Hill Repeats',
                'description' => '8x 45 detik lari menanjak kuat. Jog turun untuk istirahat. Fokus power kaki.',
                'default_distance' => 6.00,
                'default_duration' => '00:45:00',
                'intensity' => 'high',
            ],
            [
                'type' => 'interval',
                'title' => 'Strides Session',
                'description' => 'Lari santai 30 menit diakhiri dengan 6-8x 100m strides (lari cepat tapi rileks).',
                'default_distance' => 6.00,
                'default_duration' => '00:40:00',
                'intensity' => 'medium',
            ],

            // 🟣 Strength & Conditioning (New)
            [
                'type' => 'strength',
                'title' => 'Runners Leg Strength',
                'description' => '3 Sets: 15 Squats, 12 Lunges (tiap kaki), 15 Calf Raises, 10 Step-ups. Istirahat 1 min antar set.',
                'default_distance' => 0.00,
                'default_duration' => '00:45:00',
                'intensity' => 'medium',
            ],
            [
                'type' => 'strength',
                'title' => 'Core Blaster',
                'description' => 'Sirkuit 3 putaran: 1 min Plank, 30s Side Plank (kiri/kanan), 20 Russian Twists, 15 Leg Raises.',
                'default_distance' => 0.00,
                'default_duration' => '00:30:00',
                'intensity' => 'medium',
            ],
            [
                'type' => 'strength',
                'title' => 'Full Body Gym',
                'description' => 'Latihan beban di Gym: Deadlift, Bench Press, Lat Pulldown, Shoulder Press. Fokus kekuatan umum.',
                'default_distance' => 0.00,
                'default_duration' => '01:00:00',
                'intensity' => 'high',
            ],
            [
                'type' => 'strength',
                'title' => 'Yoga for Runners',
                'description' => 'Sesi Yoga fokus pada fleksibilitas Hip Flexor, Hamstring, dan Lower Back.',
                'default_distance' => 0.00,
                'default_duration' => '00:45:00',
                'intensity' => 'low',
            ],
            [
                'type' => 'strength',
                'title' => 'Cross Training (Bike/Swim)',
                'description' => 'Latihan kardio low-impact (Sepeda atau Renang) untuk menjaga kebugaran tanpa beban pada kaki.',
                'default_distance' => 0.00,
                'default_duration' => '00:45:00',
                'intensity' => 'low',
            ],
        ];

        foreach ($workouts as $workout) {
            MasterWorkout::create($workout);
        }
    }
}
