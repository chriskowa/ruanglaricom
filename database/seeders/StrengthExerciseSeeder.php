<?php

namespace Database\Seeders;

use App\Models\StrengthExercise;
use Illuminate\Database\Seeder;

class StrengthExerciseSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = StrengthExercise::getDefaultLibrary();

        foreach ($defaults as $cat => $items) {
            foreach ($items as $item) {
                StrengthExercise::firstOrCreate(
                    ['name' => $item['name']],
                    [
                        'category' => $cat,
                        'equipment' => $item['equipment'] ?? 'Bodyweight',
                        'default_sets' => $item['sets'] ?? '3',
                        'default_reps' => $item['reps'] ?? '10-12 reps',
                        'instructions' => $item['instructions'] ?? null,
                        'target_muscles' => $item['target_muscles'] ?? null,
                        'media_type' => 'gif',
                        'is_active' => true,
                    ]
                );
            }
        }
    }
}
