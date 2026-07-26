<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StrengthExercise extends Model
{
    protected $fillable = [
        'name',
        'category',
        'equipment',
        'default_sets',
        'default_reps',
        'media_type',
        'media_url',
        'instructions',
        'target_muscles',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * Get default initial list of exercises grouped by category with verified public media URLs.
     */
    public static function getDefaultLibrary(): array
    {
        return [
            'legs_lower_body' => [
                [
                    'name' => 'Squats',
                    'sets' => '4',
                    'reps' => '8-12',
                    'equipment' => 'Barbell/Bodyweight',
                    'media_type' => 'url',
                    'media_url' => 'https://images.unsplash.com/photo-1574680096145-d05b474e2155?w=600&auto=format&fit=crop&q=80',
                    'instructions' => 'Berdiri selebar bahu, tekuk lutut hingga paha sejajar lantai, dorong tumit untuk kembali berdiri.',
                    'target_muscles' => 'Quad, Glutes'
                ],
                [
                    'name' => 'Lunges',
                    'sets' => '3',
                    'reps' => '10/sisi',
                    'equipment' => 'Bodyweight/Dumbbell',
                    'media_type' => 'url',
                    'media_url' => 'https://images.unsplash.com/photo-1434682881908-b43d0467b798?w=600&auto=format&fit=crop&q=80',
                    'instructions' => 'Melangkah ke depan, turunkan pinggul hingga kedua lutut membentuk sudut 90 derajat.',
                    'target_muscles' => 'Quad, Glutes, Hamstring'
                ],
                [
                    'name' => 'Deadlifts',
                    'sets' => '4',
                    'reps' => '6-10',
                    'equipment' => 'Barbell',
                    'media_type' => 'url',
                    'media_url' => 'https://images.unsplash.com/photo-1517838277536-f5f99be501cd?w=600&auto=format&fit=crop&q=80',
                    'instructions' => 'Jaga punggung tetap lurus, engsel pinggul ke belakang, angkat beban dekat dengan tulang kering.',
                    'target_muscles' => 'Hamstring, Glutes, Lower Back'
                ],
                [
                    'name' => 'Glute Bridge / Hip Thrust',
                    'sets' => '3',
                    'reps' => '12-15',
                    'equipment' => 'Bodyweight/Barbell',
                    'media_type' => 'url',
                    'media_url' => 'https://images.unsplash.com/photo-1534367507873-d2d7e24c797f?w=600&auto=format&fit=crop&q=80',
                    'instructions' => 'Berbaring telentang, tekuk lutut, dorong pinggul ke atas dengan mengencangkan glutes.',
                    'target_muscles' => 'Glutes, Hamstring'
                ],
                [
                    'name' => 'Calf Raises',
                    'sets' => '3',
                    'reps' => '15-20',
                    'equipment' => 'Bodyweight/Dumbbell',
                    'media_type' => 'url',
                    'media_url' => 'https://upload.wikimedia.org/wikipedia/commons/e/eb/Standing-calf-raises-2.gif',
                    'instructions' => 'Berdiri di tepi permukaan, jinjit setinggi mungkin, turunkan tumit perlahan.',
                    'target_muscles' => 'Calves, Achilles Tendon'
                ],
            ],
            'core' => [
                [
                    'name' => 'Plank',
                    'sets' => '3',
                    'reps' => '45-60s',
                    'equipment' => 'Bodyweight',
                    'media_type' => 'url',
                    'media_url' => 'https://images.unsplash.com/photo-1566241142559-40e1dab266c6?w=600&auto=format&fit=crop&q=80',
                    'instructions' => 'Tahan posisi siku dan jari kaki, kencangkan core dan jaga tubuh dalam garis lurus.',
                    'target_muscles' => 'Core, Abs, Lower Back'
                ],
                [
                    'name' => 'Russian Twist',
                    'sets' => '3',
                    'reps' => '20 reps',
                    'equipment' => 'Bodyweight/Medicine Ball',
                    'media_type' => 'url',
                    'media_url' => 'https://upload.wikimedia.org/wikipedia/commons/6/64/Seated-ab-crunch-1.gif',
                    'instructions' => 'Duduk dengan lutut ditekuk, putar torso ke kiri dan kanan secara terkontrol.',
                    'target_muscles' => 'Obliques, Core'
                ],
                [
                    'name' => 'Leg Raises',
                    'sets' => '3',
                    'reps' => '12-15',
                    'equipment' => 'Bodyweight',
                    'media_type' => 'url',
                    'media_url' => 'https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=600&auto=format&fit=crop&q=80',
                    'instructions' => 'Berbaring telentang, angkat kedua kaki lurus ke atas lalu turunkan perlahan tanpa menyentuh lantai.',
                    'target_muscles' => 'Lower Abs, Core'
                ],
                [
                    'name' => 'Bicycle Crunch',
                    'sets' => '3',
                    'reps' => '20 reps',
                    'equipment' => 'Bodyweight',
                    'media_type' => 'url',
                    'media_url' => 'https://images.unsplash.com/photo-1518611012118-696072aa579a?w=600&auto=format&fit=crop&q=80',
                    'instructions' => 'Sentuhkan siku kanan ke lutut kiri secara bergantian dengan gerakan mengayuh.',
                    'target_muscles' => 'Abs, Obliques'
                ],
                [
                    'name' => 'Ab Rollout',
                    'sets' => '3',
                    'reps' => '8-12',
                    'equipment' => 'Ab Wheel/Barbell',
                    'media_type' => 'url',
                    'media_url' => 'https://images.unsplash.com/photo-1599058945522-28d584b6f0ff?w=600&auto=format&fit=crop&q=80',
                    'instructions' => 'Dorong wheel ke depan dengan kontrol core penuh, tarik kembali ke posisi awal.',
                    'target_muscles' => 'Abs, Core'
                ],
            ],
            'full_body' => [
                [
                    'name' => 'Burpees',
                    'sets' => '3',
                    'reps' => '12-15',
                    'equipment' => 'Bodyweight',
                    'media_type' => 'url',
                    'media_url' => 'https://upload.wikimedia.org/wikipedia/commons/b/b8/Burpees.gif',
                    'instructions' => 'Jongkok, lompat ke posisi push-up, lakukan push-up, lompat kembali ke depan dan lompat ke atas.',
                    'target_muscles' => 'Full Body, Cardio'
                ],
                [
                    'name' => 'Kettlebell Swing',
                    'sets' => '3',
                    'reps' => '15-20',
                    'equipment' => 'Kettlebell',
                    'media_type' => 'url',
                    'media_url' => 'https://images.unsplash.com/photo-1583454110551-21f2fa2afe61?w=600&auto=format&fit=crop&q=80',
                    'instructions' => 'Gunakan engsel pinggul untuk mengayunkan kettlebell sejajar dada dengan tenaga glutes.',
                    'target_muscles' => 'Posterior Chain, Glutes'
                ],
                [
                    'name' => 'Clean and Press',
                    'sets' => '4',
                    'reps' => '8-10',
                    'equipment' => 'Barbell/Dumbbell',
                    'media_type' => 'url',
                    'media_url' => 'https://images.unsplash.com/photo-1581009146145-b5ef050c2e1e?w=600&auto=format&fit=crop&q=80',
                    'instructions' => 'Angkat beban dari lantai ke bahu lalu dorong ke atas kepala secara eksplosif.',
                    'target_muscles' => 'Full Body, Shoulders'
                ],
                [
                    'name' => 'Thrusters',
                    'sets' => '3',
                    'reps' => '10-12',
                    'equipment' => 'Dumbbell/Barbell',
                    'media_type' => 'url',
                    'media_url' => 'https://images.unsplash.com/photo-1541534741688-6078c6bfb5c5?w=600&auto=format&fit=crop&q=80',
                    'instructions' => 'Kombinasi front squat langsung dilanjutkan dengan overhead press saat berdiri.',
                    'target_muscles' => 'Quads, Shoulders, Core'
                ],
            ],
            'upper_body' => [
                [
                    'name' => 'Push-Ups',
                    'sets' => '3',
                    'reps' => '12-20',
                    'equipment' => 'Bodyweight',
                    'media_type' => 'url',
                    'media_url' => 'https://images.unsplash.com/photo-1598971639058-fab3c3109a00?w=600&auto=format&fit=crop&q=80',
                    'instructions' => 'Turunkan dada hingga mendekati lantai dengan siku 45 derajat dari tubuh.',
                    'target_muscles' => 'Chest, Triceps, Shoulders'
                ],
                [
                    'name' => 'Bench Press',
                    'sets' => '4',
                    'reps' => '6-10',
                    'equipment' => 'Barbell/Dumbbell',
                    'media_type' => 'url',
                    'media_url' => 'https://images.unsplash.com/photo-1534438327276-14e5300c3a48?w=600&auto=format&fit=crop&q=80',
                    'instructions' => 'Berbaring di bench, turunkan beban ke dada lalu dorong ke atas secara stabil.',
                    'target_muscles' => 'Chest, Triceps'
                ],
                [
                    'name' => 'Pull-Ups / Chin-Ups',
                    'sets' => '3',
                    'reps' => '8-12',
                    'equipment' => 'Bodyweight',
                    'media_type' => 'url',
                    'media_url' => 'https://images.unsplash.com/photo-1526506118085-60ce8714f8c5?w=600&auto=format&fit=crop&q=80',
                    'instructions' => 'Gantung di bar, tarik tubuh ke atas hingga dagu melewati bar.',
                    'target_muscles' => 'Lats, Biceps, Back'
                ],
                [
                    'name' => 'Overhead Press',
                    'sets' => '4',
                    'reps' => '8-10',
                    'equipment' => 'Barbell/Dumbbell',
                    'media_type' => 'url',
                    'media_url' => 'https://images.unsplash.com/photo-1541534741688-6078c6bfb5c5?w=600&auto=format&fit=crop&q=80',
                    'instructions' => 'Berdiri tegak, dorong beban dari atas bahu lurus ke atas kepala.',
                    'target_muscles' => 'Shoulders, Triceps'
                ],
                [
                    'name' => 'Bent Over Row',
                    'sets' => '4',
                    'reps' => '8-12',
                    'equipment' => 'Barbell/Dumbbell',
                    'media_type' => 'url',
                    'media_url' => 'https://images.unsplash.com/photo-1605296867304-46d5465a13f1?w=600&auto=format&fit=crop&q=80',
                    'instructions' => 'Bungkukkan badan 45 derajat, tarik beban ke arah perut dengan menekuk siku.',
                    'target_muscles' => 'Upper Back, Lats'
                ],
                [
                    'name' => 'Bicep Curl',
                    'sets' => '3',
                    'reps' => '12-15',
                    'equipment' => 'Dumbbell/Barbell',
                    'media_type' => 'url',
                    'media_url' => 'https://upload.wikimedia.org/wikipedia/commons/3/30/Standing-biceps-curl-1.gif',
                    'instructions' => 'Tekuk siku untuk mengangkat beban ke arah bahu tanpa mengayunkan siku.',
                    'target_muscles' => 'Biceps'
                ],
                [
                    'name' => 'Tricep Dips',
                    'sets' => '3',
                    'reps' => '10-12',
                    'equipment' => 'Bodyweight/Bench',
                    'media_type' => 'url',
                    'media_url' => 'https://images.unsplash.com/photo-1583454110551-21f2fa2afe61?w=600&auto=format&fit=crop&q=80',
                    'instructions' => 'Turunkan pinggul dengan menekuk siku 90 derajat lalu dorong kembali ke atas.',
                    'target_muscles' => 'Triceps'
                ],
            ],
        ];
    }
}
