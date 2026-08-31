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
        Schema::create('coach_invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number', 50)->unique();
            $table->foreignId('coach_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('runner_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('program_id')->nullable()->constrained('programs')->onDelete('set null');
            $table->foreignId('enrollment_id')->nullable()->constrained('program_enrollments')->onDelete('set null');
            
            $table->string('pricing_type', 30)->default('monthly'); // one_time, hourly, daily, weekly, monthly
            $table->decimal('amount', 12, 2)->default(0);
            $table->decimal('platform_fee', 12, 2)->default(0);
            $table->decimal('net_coach_amount', 12, 2)->default(0);
            
            $table->unsignedInteger('quantity')->default(1); // 1 month, 4 sessions, 2 weeks, etc.
            $table->date('period_start')->nullable();
            $table->date('period_end')->nullable();
            $table->date('due_date')->nullable();
            
            $table->string('payment_status', 30)->default('unpaid'); // unpaid, paid, overdue, cancelled, refunded
            $table->string('payment_method', 30)->nullable(); // gateway, bank_transfer, cash, wallet
            $table->string('payment_proof')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->boolean('verified_by_coach')->default(false);
            $table->text('notes')->nullable();
            
            $table->timestamps();
            
            $table->index(['coach_id', 'payment_status']);
            $table->index(['runner_id', 'payment_status']);
            $table->index('due_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coach_invoices');
    }
};
