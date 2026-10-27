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
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'wa_subscription_expires_at')) {
                $table->timestamp('wa_subscription_expires_at')->nullable()->after('is_receive_wa');
            }
            if (!Schema::hasColumn('users', 'is_wa_whitelisted')) {
                $table->boolean('is_wa_whitelisted')->default(false)->after('wa_subscription_expires_at');
            }
        });

        Schema::create('runner_wa_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('order_id')->unique();
            $table->decimal('amount', 12, 2)->default(20000.00);
            $table->string('status')->default('pending'); // pending, paid, failed, expired
            $table->string('snap_token')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('midtrans_transaction_status')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('subscribed_from')->nullable();
            $table->timestamp('subscribed_until')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('runner_wa_subscriptions');

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'is_wa_whitelisted')) {
                $table->dropColumn('is_wa_whitelisted');
            }
            if (Schema::hasColumn('users', 'wa_subscription_expires_at')) {
                $table->dropColumn('wa_subscription_expires_at');
            }
        });
    }
};
