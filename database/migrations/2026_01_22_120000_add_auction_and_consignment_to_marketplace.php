<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('marketplace_products', function (Blueprint $table) {
            $table->enum('sale_type', ['fixed', 'auction'])->default('fixed')->after('price');
            $table->enum('fulfillment_mode', ['self_ship', 'consignment'])->default('self_ship')->after('sale_type');
            $table->enum('consignment_status', ['none', 'requested', 'received', 'listed', 'sold', 'returned'])->default('none')->after('fulfillment_mode');

            $table->timestamp('auction_start_at')->nullable()->after('consignment_status');
            $table->timestamp('auction_end_at')->nullable()->after('auction_start_at');
            $table->decimal('starting_price', 15, 2)->nullable()->after('auction_end_at');
            $table->decimal('current_price', 15, 2)->nullable()->after('starting_price');
            $table->decimal('min_increment', 15, 2)->nullable()->after('current_price');
            $table->decimal('reserve_price', 15, 2)->nullable()->after('min_increment');
            $table->decimal('buy_now_price', 15, 2)->nullable()->after('reserve_price');
            $table->enum('auction_status', ['draft', 'running', 'ended', 'cancelled'])->default('draft')->after('buy_now_price');
            $table->foreignId('auction_winner_id')->nullable()->after('auction_status')->constrained('users')->nullOnDelete();
        });

        Schema::create('marketplace_bids', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('marketplace_products')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('amount', 15, 2);
            $table->timestamps();

            $table->index(['product_id', 'amount']);
        });

        Schema::create('marketplace_consignment_intakes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->unique()->constrained('marketplace_products')->cascadeOnDelete();
            $table->foreignId('seller_id')->constrained('users')->cascadeOnDelete();
            $table->enum('status', ['requested', 'received', 'listed', 'sold', 'returned'])->default('requested');
            $table->string('dropoff_method')->nullable();
            $table->string('dropoff_location')->nullable();
            $table->string('grade')->nullable();
            $table->text('qc_notes')->nullable();
            $table->text('admin_notes')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('received_at')->nullable();
            $table->timestamp('listed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketplace_consignment_intakes');
        Schema::dropIfExists('marketplace_bids');

        Schema::table('marketplace_products', function (Blueprint $table) {
            $table->dropForeign(['auction_winner_id']);
            $table->dropColumn([
                'sale_type',
                'fulfillment_mode',
                'consignment_status',
                'auction_start_at',
                'auction_end_at',
                'starting_price',
                'current_price',
                'min_increment',
                'reserve_price',
                'buy_now_price',
                'auction_status',
                'auction_winner_id',
            ]);
        });
    }
};
