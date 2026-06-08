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
        Schema::create('program_weekly_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enrollment_id')->constrained('program_enrollments')->onDelete('cascade');
            $table->integer('week_number');
            $table->text('report_text');
            $table->string('status')->default('published');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('program_weekly_reports');
    }
};
