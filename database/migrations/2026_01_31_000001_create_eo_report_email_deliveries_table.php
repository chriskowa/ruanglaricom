<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('eo_report_email_deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->foreignId('eo_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('triggered_by_user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->string('to_email')->index();
            $table->string('to_name')->nullable();
            $table->string('subject');
            $table->string('report_type')->default('event_report');
            $table->json('filters')->nullable();

            $table->string('queue')->nullable()->index();
            $table->string('status')->default('pending')->index();

            $table->unsignedInteger('attempts')->default(0);
            $table->timestamp('first_attempt_at')->nullable()->index();
            $table->timestamp('last_attempt_at')->nullable()->index();
            $table->timestamp('sent_at')->nullable()->index();

            $table->string('failure_code')->nullable()->index();
            $table->text('failure_message')->nullable();
            $table->string('provider_message_id')->nullable();
            $table->timestamp('failure_notified_at')->nullable()->index();

            $table->timestamps();

            $table->index(['event_id', 'status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eo_report_email_deliveries');
    }
};
