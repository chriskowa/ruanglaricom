<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('community_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('community_registration_id')->constrained('community_registrations')->onDelete('cascade');
            $table->foreignId('transaction_id')->nullable()->constrained('transactions')->nullOnDelete();
            $table->string('payment_method');
            $table->string('status')->default('pending');

            $table->decimal('total_original', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('admin_fee', 12, 2)->default(0);
            $table->integer('unique_code')->default(0);
            $table->decimal('final_amount', 12, 2)->default(0);

            $table->text('qris_payload')->nullable();
            $table->timestamps();

            $table->index(['community_registration_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('community_invoices');
    }
};
