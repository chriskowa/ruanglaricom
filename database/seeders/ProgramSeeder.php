<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\Program;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProgramSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get coaches
        $coaches = User::where('role', 'coach')->get();
        if ($coaches->isEmpty()) {
            $this->command->warn('No coaches found. Please run UserSeeder first.');

            return;
        }

        $coach1 = $coaches->first();
        $coach2 = $coaches->skip(1)->first() ?? $coaches->first();

        $malangKota = City::where('name', 'Malang Kota')->first();
        $surabaya = City::where('name', 'Surabaya')->first();

        // Program 1: 5K Beginner - GRATIS
        Program::firstOrCreate(
            ['slug' => 'program-lari-5k-pemula-gratis'],
            [
                'coach_id' => $coach1->id,
                'title' => 'Program Lari 5K untuk Pemula',
                'slug' => 'program-lari-5k-pemula-gratis',
                'description' => 'Program latihan lari 5K yang dirancang khusus untuk pemula. Program ini akan membantu Anda membangun dasar kekuatan dan daya tahan untuk mencapai target 5 kilometer. Dengan jadwal latihan yang terstruktur, Anda akan belajar teknik lari yang benar, pemanasan, pendinginan, dan cara meningkatkan performa secara bertahap.',
                'difficulty' => 'beginner',
                'distance_target' => '5k',
                'target_time' => '00:35:00',
                'price' => 0,
                'city_id' => $malangKota?->id,
                'program_json' => $this->generate5KProgram(),
                'is_vdot_generated' => false,
                'is_active' => true,
                'is_published' => true,
                'duration_weeks' => 8,
                'enrolled_count' => 15,
                'average_rating' => 4.5,
                'total_reviews' => 8,
            ]
        );

        // Program 2: 10K Intermediate - BERBAYAR
        Program::firstOrCreate(
            ['slug' => 'program-lari-10k-intermediate'],
            [
                'coach_id' => $coach1->id,
                'title' => 'Program Lari 10K - Tingkat Menengah',
                'slug' => 'program-lari-10k-intermediate',
                'description' => 'Program latihan intensif untuk mencapai target lari 10 kilometer. Cocok untuk pelari yang sudah memiliki dasar dan ingin meningkatkan performa. Program ini mencakup latihan interval, tempo run, dan long run untuk meningkatkan VO2 max dan daya tahan. Dapatkan teknik berlari yang lebih efisien dan rencana latihan yang terstruktur.',
                'difficulty' => 'intermediate',
                'distance_target' => '10k',
                'target_time' => '01:00:00',
                'price' => 150000,
                'city_id' => $malangKota?->id,
                'program_json' => $this->generate10KProgram(),
                'is_vdot_generated' => false,
                'is_active' => true,
                'is_published' => true,
                'duration_weeks' => 12,
                'enrolled_count' => 23,
                'average_rating' => 4.8,
                'total_reviews' => 12,
            ]
        );

        // Program 3: Half Marathon (21K) - BERBAYAR
        Program::firstOrCreate(
            ['slug' => 'program-lari-half-marathon'],
            [
                'coach_id' => $coach1->id,
                'title' => 'Program Half Marathon 21K - Siap Finisher',
                'slug' => 'program-lari-half-marathon',
                'description' => 'Program komprehensif untuk menyelesaikan Half Marathon (21 km) dengan sukses. Program ini dirancang untuk membangun endurance, mental toughness, dan strategi race. Anda akan belajar tentang pacing, nutrition, hydration, dan recovery. Cocok untuk pelari yang ingin challenge diri mereka ke level berikutnya.',
                'difficulty' => 'intermediate',
                'distance_target' => '21k',
                'target_time' => '02:15:00',
                'price' => 250000,
                'city_id' => $surabaya?->id,
                'program_json' => $this->generateHalfMarathonProgram(),
                'is_vdot_generated' => false,
                'is_active' => true,
                'is_published' => true,
                'duration_weeks' => 16,
                'enrolled_count' => 18,
                'average_rating' => 4.9,
                'total_reviews' => 10,
            ]
        );

        // Program 4: Marathon (42K) - BERBAYAR PREMIUM
        Program::firstOrCreate(
            ['slug' => 'program-lari-marathon-full'],
            [
                'coach_id' => $coach2->id,
                'title' => 'Program Full Marathon 42K - Ultimate Challenge',
                'slug' => 'program-lari-marathon-full',
                'description' => 'Program marathon paling lengkap untuk menyelesaikan 42 kilometer. Program ini mencakup periodisasi latihan 20 minggu, strategi race, manajemen energi, dan mental preparation. Dapatkan support langsung dari coach berpengalaman, custom training plan, dan tips untuk sukses di race day. Cocok untuk pelari yang sudah pernah menyelesaikan half marathon.',
                'difficulty' => 'advanced',
                'distance_target' => '42k',
                'target_time' => '04:30:00',
                'price' => 500000,
                'city_id' => $surabaya?->id,
                'program_json' => $this->generateMarathonProgram(),
                'is_vdot_generated' => false,
                'is_active' => true,
                'is_published' => true,
                'duration_weeks' => 20,
                'enrolled_count' => 12,
                'average_rating' => 5.0,
                'total_reviews' => 7,
            ]
        );

        // Program 5: 5K Advanced - BERBAYAR
        Program::firstOrCreate(
            ['slug' => 'program-lari-5k-speed-training'],
            [
                'coach_id' => $coach2->id,
                'title' => 'Program 5K Speed Training - Sub 25 Menit',
                'slug' => 'program-lari-5k-speed-training',
                'description' => 'Program speed training khusus untuk meningkatkan kecepatan lari 5K. Fokus pada interval training, VO2 max development, dan teknik lari yang lebih efisien. Cocok untuk pelari yang ingin mencapai personal best di jarak 5 kilometer.',
                'difficulty' => 'advanced',
                'distance_target' => '5k',
                'target_time' => '00:24:00',
                'price' => 200000,
                'city_id' => $malangKota?->id,
                'program_json' => $this->generate5KSpeedProgram(),
                'is_vdot_generated' => false,
                'is_active' => true,
                'is_published' => true,
                'duration_weeks' => 10,
                'enrolled_count' => 9,
                'average_rating' => 4.7,
                'total_reviews' => 5,
            ]
        );

        // Program 6: 10K GRATIS untuk Community
        Program::firstOrCreate(
            ['slug' => 'program-lari-10k-community'],
            [
                'coach_id' => $coach2->id,
                'title' => 'Program Lari 10K Community - Gratis',
                'slug' => 'program-lari-10k-community',
                'description' => 'Program latihan 10K gratis untuk komunitas lari. Program ini dirancang untuk membantu lebih banyak orang mencapai target lari 10 kilometer. Latihan terstruktur dengan progresi yang aman dan efektif.',
                'difficulty' => 'beginner',
                'distance_target' => '10k',
                'target_time' => '01:15:00',
                'price' => 0,
                'city_id' => $malangKota?->id,
                'program_json' => $this->generate10KProgram(),
                'is_vdot_generated' => false,
                'is_active' => true,
                'is_published' => true,
                'duration_weeks' => 12,
                'enrolled_count' => 45,
                'average_rating' => 4.6,
                'total_reviews' => 20,
            ]
        );

        $this->command->info('Programs seeded successfully!');
        $this->command->info('Created/Updated 6 example programs with various categories and prices.');
    }

    /**
     * Generate 5K Program JSON
     */
    private function generate5KProgram(): array
    {
        $sessions = [];
        $day = 1;

        // Week 1-2: Building Base
        for ($week = 1; $week <= 2; $week++) {
            $sessions[] = ['day' => $day++, 'type' => 'easy_run', 'distance' => 2, 'duration' => '00:20:00', 'description' => 'Easy run untuk membangun base'];
            $sessions[] = ['day' => $day++, 'type' => 'rest', 'description' => 'Rest day'];
            $sessions[] = ['day' => $day++, 'type' => 'easy_run', 'distance' => 3, 'duration' => '00:25:00', 'description' => 'Easy run 3km'];
            $sessions[] = ['day' => $day++, 'type' => 'rest', 'description' => 'Rest day'];
            $sessions[] = ['day' => $day++, 'type' => 'easy_run', 'distance' => 2.5, 'duration' => '00:22:00', 'description' => 'Easy run recovery'];
            $sessions[] = ['day' => $day++, 'type' => 'rest', 'description' => 'Rest day'];
            $sessions[] = ['day' => $day++, 'type' => 'easy_run', 'distance' => 3, 'duration' => '00:25:00', 'description' => 'Long easy run'];
        }

        // Week 3-4: Increase Distance
        for ($week = 3; $week <= 4; $week++) {
            $sessions[] = ['day' => $day++, 'type' => 'easy_run', 'distance' => 3, 'duration' => '00:25:00', 'description' => 'Easy run'];
            $sessions[] = ['day' => $day++, 'type' => 'rest', 'description' => 'Rest day'];
            $sessions[] = ['day' => $day++, 'type' => 'easy_run', 'distance' => 4, 'duration' => '00:30:00', 'description' => 'Easy run 4km'];
            $sessions[] = ['day' => $day++, 'type' => 'rest', 'description' => 'Rest day'];
            $sessions[] = ['day' => $day++, 'type' => 'easy_run', 'distance' => 3, 'duration' => '00:25:00', 'description' => 'Easy run recovery'];
            $sessions[] = ['day' => $day++, 'type' => 'rest', 'description' => 'Rest day'];
            $sessions[] = ['day' => $day++, 'type' => 'easy_run', 'distance' => 4, 'duration' => '00:30:00', 'description' => 'Long easy run'];
        }

        // Week 5-6: Introduce Speed
        for ($week = 5; $week <= 6; $week++) {
            $sessions[] = ['day' => $day++, 'type' => 'easy_run', 'distance' => 3, 'duration' => '00:25:00', 'description' => 'Easy run'];
            $sessions[] = ['day' => $day++, 'type' => 'rest', 'description' => 'Rest day'];
            $sessions[] = ['day' => $day++, 'type' => 'interval', 'distance' => 4, 'duration' => '00:28:00', 'description' => 'Interval: 5x 400m dengan recovery'];
            $sessions[] = ['day' => $day++, 'type' => 'rest', 'description' => 'Rest day'];
            $sessions[] = ['day' => $day++, 'type' => 'easy_run', 'distance' => 3, 'duration' => '00:25:00', 'description' => 'Easy run recovery'];
            $sessions[] = ['day' => $day++, 'type' => 'rest', 'description' => 'Rest day'];
            $sessions[] = ['day' => $day++, 'type' => 'easy_run', 'distance' => 4, 'duration' => '00:30:00', 'description' => 'Long easy run'];
        }

        // Week 7-8: Tapering & Race Prep
        $sessions[] = ['day' => $day++, 'type' => 'easy_run', 'distance' => 3, 'duration' => '00:25:00', 'description' => 'Easy run'];
        $sessions[] = ['day' => $day++, 'type' => 'rest', 'description' => 'Rest day'];
        $sessions[] = ['day' => $day++, 'type' => 'tempo', 'distance' => 4, 'duration' => '00:28:00', 'description' => 'Tempo run 20 menit'];
        $sessions[] = ['day' => $day++, 'type' => 'rest', 'description' => 'Rest day'];
        $sessions[] = ['day' => $day++, 'type' => 'easy_run', 'distance' => 2, 'duration' => '00:18:00', 'description' => 'Easy run recovery'];
        $sessions[] = ['day' => $day++, 'type' => 'rest', 'description' => 'Rest day'];
        $sessions[] = ['day' => $day++, 'type' => 'easy_run', 'distance' => 5, 'duration' => '00:35:00', 'description' => 'Practice 5K - target race pace'];

        return [
            'sessions' => $sessions,
            'duration_weeks' => 8,
        ];
    }

    /**
     * Generate 10K Program JSON
     */
    private function generate10KProgram(): array
    {
        $sessions = [];
        $day = 1;

        // 12-week program dengan progresi yang lebih kompleks
        for ($week = 1; $week <= 12; $week++) {
            if ($week <= 4) {
                // Base building phase
                $sessions[] = ['day' => $day++, 'type' => 'easy_run', 'distance' => 4, 'duration' => '00:30:00', 'description' => 'Easy run base building'];
                $sessions[] = ['day' => $day++, 'type' => 'rest', 'description' => 'Rest day'];
                $sessions[] = ['day' => $day++, 'type' => 'easy_run', 'distance' => 5, 'duration' => '00:35:00', 'description' => 'Easy run 5km'];
                $sessions[] = ['day' => $day++, 'type' => 'rest', 'description' => 'Rest day'];
                $sessions[] = ['day' => $day++, 'type' => 'easy_run', 'distance' => 4, 'duration' => '00:30:00', 'description' => 'Easy run recovery'];
                $sessions[] = ['day' => $day++, 'type' => 'rest', 'description' => 'Rest day'];
                $sessions[] = ['day' => $day++, 'type' => 'easy_run', 'distance' => 6, 'duration' => '00:42:00', 'description' => 'Long easy run'];
            } elseif ($week <= 8) {
                // Quality phase
                $sessions[] = ['day' => $day++, 'type' => 'easy_run', 'distance' => 5, 'duration' => '00:35:00', 'description' => 'Easy run'];
                $sessions[] = ['day' => $day++, 'type' => 'rest', 'description' => 'Rest day'];
                $sessions[] = ['day' => $day++, 'type' => 'interval', 'distance' => 6, 'duration' => '00:40:00', 'description' => 'Interval training'];
                $sessions[] = ['day' => $day++, 'type' => 'rest', 'description' => 'Rest day'];
                $sessions[] = ['day' => $day++, 'type' => 'tempo', 'distance' => 6, 'duration' => '00:38:00', 'description' => 'Tempo run'];
                $sessions[] = ['day' => $day++, 'type' => 'rest', 'description' => 'Rest day'];
                $sessions[] = ['day' => $day++, 'type' => 'easy_run', 'distance' => 8, 'duration' => '00:50:00', 'description' => 'Long run'];
            } else {
                // Tapering phase
                $sessions[] = ['day' => $day++, 'type' => 'easy_run', 'distance' => 4, 'duration' => '00:30:00', 'description' => 'Easy run'];
                $sessions[] = ['day' => $day++, 'type' => 'rest', 'description' => 'Rest day'];
                $sessions[] = ['day' => $day++, 'type' => 'tempo', 'distance' => 5, 'duration' => '00:32:00', 'description' => 'Tempo run'];
                $sessions[] = ['day' => $day++, 'type' => 'rest', 'description' => 'Rest day'];
                $sessions[] = ['day' => $day++, 'type' => 'easy_run', 'distance' => 3, 'duration' => '00:22:00', 'description' => 'Easy run recovery'];
                $sessions[] = ['day' => $day++, 'type' => 'rest', 'description' => 'Rest day'];
                if ($week < 12) {
                    $sessions[] = ['day' => $day++, 'type' => 'easy_run', 'distance' => 10, 'duration' => '01:00:00', 'description' => 'Long run practice 10K'];
                } else {
                    $sessions[] = ['day' => $day++, 'type' => 'rest', 'description' => 'Race day - Good luck!'];
                }
            }
        }

        return [
            'sessions' => $sessions,
            'duration_weeks' => 12,
        ];
    }

    /**
     * Generate Half Marathon Program JSON
     */
    private function generateHalfMarathonProgram(): array
    {
        $sessions = [];
        $day = 1;

        // 16-week program dengan struktur yang lebih kompleks
        for ($week = 1; $week <= 16; $week++) {
            if ($week <= 6) {
                // Base building
                $sessions[] = ['day' => $day++, 'type' => 'easy_run', 'distance' => 5, 'duration' => '00:35:00', 'description' => 'Easy run'];
                $sessions[] = ['day' => $day++, 'type' => 'rest', 'description' => 'Rest day'];
                $sessions[] = ['day' => $day++, 'type' => 'easy_run', 'distance' => 6, 'duration' => '00:42:00', 'description' => 'Easy run'];
                $sessions[] = ['day' => $day++, 'type' => 'rest', 'description' => 'Rest day'];
                $sessions[] = ['day' => $day++, 'type' => 'easy_run', 'distance' => 5, 'duration' => '00:35:00', 'description' => 'Easy run'];
                $sessions[] = ['day' => $day++, 'type' => 'rest', 'description' => 'Rest day'];
                $sessions[] = ['day' => $day++, 'type' => 'easy_run', 'distance' => 8 + ($week * 1), 'duration' => '00:50:00', 'description' => 'Long run'];
            } elseif ($week <= 12) {
                // Quality phase
                $sessions[] = ['day' => $day++, 'type' => 'easy_run', 'distance' => 6, 'duration' => '00:42:00', 'description' => 'Easy run'];
                $sessions[] = ['day' => $day++, 'type' => 'rest', 'description' => 'Rest day'];
                $sessions[] = ['day' => $day++, 'type' => 'interval', 'distance' => 7, 'duration' => '00:45:00', 'description' => 'Interval training'];
                $sessions[] = ['day' => $day++, 'type' => 'rest', 'description' => 'Rest day'];
                $sessions[] = ['day' => $day++, 'type' => 'tempo', 'distance' => 8, 'duration' => '00:48:00', 'description' => 'Tempo run'];
                $sessions[] = ['day' => $day++, 'type' => 'rest', 'description' => 'Rest day'];
                $sessions[] = ['day' => $day++, 'type' => 'easy_run', 'distance' => 12 + ($week - 6), 'duration' => '01:15:00', 'description' => 'Long run'];
            } else {
                // Tapering
                $sessions[] = ['day' => $day++, 'type' => 'easy_run', 'distance' => 5, 'duration' => '00:35:00', 'description' => 'Easy run'];
                $sessions[] = ['day' => $day++, 'type' => 'rest', 'description' => 'Rest day'];
                $sessions[] = ['day' => $day++, 'type' => 'tempo', 'distance' => 6, 'duration' => '00:38:00', 'description' => 'Tempo run'];
                $sessions[] = ['day' => $day++, 'type' => 'rest', 'description' => 'Rest day'];
                $sessions[] = ['day' => $day++, 'type' => 'easy_run', 'distance' => 4, 'duration' => '00:28:00', 'description' => 'Easy run'];
                $sessions[] = ['day' => $day++, 'type' => 'rest', 'description' => 'Rest day'];
                if ($week < 16) {
                    $sessions[] = ['day' => $day++, 'type' => 'easy_run', 'distance' => 15, 'duration' => '01:30:00', 'description' => 'Long run practice'];
                } else {
                    $sessions[] = ['day' => $day++, 'type' => 'rest', 'description' => 'Race day - You got this!'];
                }
            }
        }

        return [
            'sessions' => $sessions,
            'duration_weeks' => 16,
        ];
    }

    /**
     * Generate Marathon Program JSON
     */
    private function generateMarathonProgram(): array
    {
        $sessions = [];
        $day = 1;

        // 20-week marathon program
        for ($week = 1; $week <= 20; $week++) {
            if ($week <= 8) {
                // Base building
                $sessions[] = ['day' => $day++, 'type' => 'easy_run', 'distance' => 6, 'duration' => '00:42:00', 'description' => 'Easy run'];
                $sessions[] = ['day' => $day++, 'type' => 'rest', 'description' => 'Rest day'];
                $sessions[] = ['day' => $day++, 'type' => 'easy_run', 'distance' => 8, 'duration' => '00:50:00', 'description' => 'Easy run'];
                $sessions[] = ['day' => $day++, 'type' => 'rest', 'description' => 'Rest day'];
                $sessions[] = ['day' => $day++, 'type' => 'easy_run', 'distance' => 6, 'duration' => '00:42:00', 'description' => 'Easy run'];
                $sessions[] = ['day' => $day++, 'type' => 'rest', 'description' => 'Rest day'];
                $sessions[] = ['day' => $day++, 'type' => 'easy_run', 'distance' => 10 + ($week * 1.5), 'duration' => '01:00:00', 'description' => 'Long run'];
            } elseif ($week <= 16) {
                // Quality & peak phase
                $sessions[] = ['day' => $day++, 'type' => 'easy_run', 'distance' => 8, 'duration' => '00:50:00', 'description' => 'Easy run'];
                $sessions[] = ['day' => $day++, 'type' => 'rest', 'description' => 'Rest day'];
                $sessions[] = ['day' => $day++, 'type' => 'interval', 'distance' => 10, 'duration' => '01:00:00', 'description' => 'Interval training'];
                $sessions[] = ['day' => $day++, 'type' => 'rest', 'description' => 'Rest day'];
                $sessions[] = ['day' => $day++, 'type' => 'tempo', 'distance' => 10, 'duration' => '01:00:00', 'description' => 'Tempo run'];
                $sessions[] = ['day' => $day++, 'type' => 'rest', 'description' => 'Rest day'];
                $sessions[] = ['day' => $day++, 'type' => 'easy_run', 'distance' => 25 + ($week - 8), 'duration' => '02:30:00', 'description' => 'Long run'];
            } else {
                // Tapering
                $sessions[] = ['day' => $day++, 'type' => 'easy_run', 'distance' => 6, 'duration' => '00:42:00', 'description' => 'Easy run'];
                $sessions[] = ['day' => $day++, 'type' => 'rest', 'description' => 'Rest day'];
                $sessions[] = ['day' => $day++, 'type' => 'tempo', 'distance' => 6, 'duration' => '00:38:00', 'description' => 'Tempo run'];
                $sessions[] = ['day' => $day++, 'type' => 'rest', 'description' => 'Rest day'];
                $sessions[] = ['day' => $day++, 'type' => 'easy_run', 'distance' => 4, 'duration' => '00:28:00', 'description' => 'Easy run'];
                $sessions[] = ['day' => $day++, 'type' => 'rest', 'description' => 'Rest day'];
                if ($week < 20) {
                    $sessions[] = ['day' => $day++, 'type' => 'easy_run', 'distance' => 20, 'duration' => '02:00:00', 'description' => 'Long run practice'];
                } else {
                    $sessions[] = ['day' => $day++, 'type' => 'rest', 'description' => 'Race day - Finish strong!'];
                }
            }
        }

        return [
            'sessions' => $sessions,
            'duration_weeks' => 20,
        ];
    }

    /**
     * Generate 5K Speed Program JSON
     */
    private function generate5KSpeedProgram(): array
    {
        $sessions = [];
        $day = 1;

        // 10-week speed training program
        for ($week = 1; $week <= 10; $week++) {
            $sessions[] = ['day' => $day++, 'type' => 'easy_run', 'distance' => 4, 'duration' => '00:28:00', 'description' => 'Easy run recovery'];
            $sessions[] = ['day' => $day++, 'type' => 'rest', 'description' => 'Rest day'];
            $sessions[] = ['day' => $day++, 'type' => 'interval', 'distance' => 5, 'duration' => '00:35:00', 'description' => 'Interval: 400m repeats'];
            $sessions[] = ['day' => $day++, 'type' => 'rest', 'description' => 'Rest day'];
            $sessions[] = ['day' => $day++, 'type' => 'tempo', 'distance' => 5, 'duration' => '00:30:00', 'description' => 'Tempo run'];
            $sessions[] = ['day' => $day++, 'type' => 'rest', 'description' => 'Rest day'];
            $sessions[] = ['day' => $day++, 'type' => 'easy_run', 'distance' => 6, 'duration' => '00:42:00', 'description' => 'Long easy run'];
        }

        return [
            'sessions' => $sessions,
            'duration_weeks' => 10,
        ];
    }
}
