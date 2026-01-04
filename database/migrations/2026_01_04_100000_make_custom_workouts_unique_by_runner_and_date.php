<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        try {
            $duplicates = DB::table('custom_workouts')
                ->select('runner_id', 'workout_date', DB::raw('COUNT(*) as cnt'))
                ->groupBy('runner_id', 'workout_date')
                ->having('cnt', '>', 1)
                ->get();

            foreach ($duplicates as $dup) {
                $keepId = DB::table('custom_workouts')
                    ->where('runner_id', $dup->runner_id)
                    ->where('workout_date', $dup->workout_date)
                    ->orderByDesc('updated_at')
                    ->orderByDesc('id')
                    ->value('id');

                if ($keepId) {
                    DB::table('custom_workouts')
                        ->where('runner_id', $dup->runner_id)
                        ->where('workout_date', $dup->workout_date)
                        ->where('id', '<>', $keepId)
                        ->delete();
                }
            }
        } catch (\Throwable $e) {}

        Schema::table('custom_workouts', function (Blueprint $table) {
            try {
                $table->unique(['runner_id', 'workout_date'], 'custom_workouts_runner_date_unique');
            } catch (\Throwable $e) {
                try {
                    DB::statement('ALTER TABLE custom_workouts ADD UNIQUE INDEX custom_workouts_runner_date_unique (runner_id, workout_date)');
                } catch (\Throwable $e2) {}
            }
        });
    }

    public function down(): void
    {
        Schema::table('custom_workouts', function (Blueprint $table) {
            try {
                $table->dropUnique('custom_workouts_runner_date_unique');
            } catch (\Throwable $e) {}

            try {
                $table->index(['runner_id', 'workout_date'], 'custom_workouts_runner_id_workout_date_index');
            } catch (\Throwable $e) {}
        });
    }
};
