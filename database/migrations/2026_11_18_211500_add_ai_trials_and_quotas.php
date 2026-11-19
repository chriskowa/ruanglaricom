<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'ai_program_trials_used')) {
            Schema::table('users', function (Blueprint $table) {
                $table->unsignedInteger('ai_program_trials_used')->default(0)->after('is_active');
            });
        }

        if (! Schema::hasTable('user_ai_quotas')) {
            Schema::create('user_ai_quotas', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->date('usage_date');
                $table->unsignedInteger('requests_count')->default(0);
                $table->unsignedInteger('total_tokens')->default(0);
                $table->timestamps();

                $table->unique(['user_id', 'usage_date']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('user_ai_quotas');

        if (Schema::hasColumn('users', 'ai_program_trials_used')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('ai_program_trials_used');
            });
        }
    }
};
