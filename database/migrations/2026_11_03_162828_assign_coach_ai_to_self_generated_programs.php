<?php

use App\Models\Program;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $coachAiId = User::where('email', 'ai-coach@ruanglari.com')
            ->orWhere('username', 'coach-ai')
            ->value('id') ?? 392;

        if ($coachAiId) {
            // Update all self-generated programs created by runners to Coach AI Official
            Program::where('is_self_generated', true)
                ->where(function ($query) {
                    $query->whereHas('coach', function ($q) {
                        $q->where('role', '!=', 'coach');
                    })->orWhereNull('coach_id');
                })
                ->update(['coach_id' => $coachAiId]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op
    }
};
