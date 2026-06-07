<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('program_enrollments', function (Blueprint $table) {
            $table->float('current_vdot')->nullable()->comment('VDOT pelari yang disesuaikan akibat detraining');
            $table->date('target_race_date')->nullable()->comment('Tanggal kompetisi utama pelari');
            $table->string('status_reason')->nullable()->comment('Alasan perubahan status program (injury, sick, busy)');
            $table->json('reschedule_history')->nullable()->comment('Log riwayat reschedule pelari');
        });

        Schema::create('runner_injury_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('enrollment_id')->constrained('program_enrollments')->onDelete('cascade');
            $table->string('injury_type'); // minor, moderate, severe
            $table->string('body_part'); // knee, ankle, shin_splints, etc.
            $table->date('injured_at');
            $table->date('recovered_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('runner_injury_logs');

        Schema::table('program_enrollments', function (Blueprint $table) {
            $table->dropColumn(['current_vdot', 'target_race_date', 'status_reason', 'reschedule_history']);
        });
    }
};
