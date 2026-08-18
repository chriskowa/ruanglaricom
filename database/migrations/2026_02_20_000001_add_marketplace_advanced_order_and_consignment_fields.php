<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('marketplace_consignment_intakes')) {
            Schema::table('marketplace_consignment_intakes', function (Blueprint $table) {
                if (!Schema::hasColumn('marketplace_consignment_intakes', 'owner_name')) {
                    $table->string('owner_name')->nullable()->after('seller_id');
                }
                if (!Schema::hasColumn('marketplace_consignment_intakes', 'owner_phone')) {
                    $table->string('owner_phone')->nullable()->after('owner_name');
                }
            });
        }

        if (Schema::hasTable('marketplace_orders')) {
            Schema::table('marketplace_orders', function (Blueprint $table) {
                if (!Schema::hasColumn('marketplace_orders', 'destination_city_id')) {
                    $table->unsignedBigInteger('destination_city_id')->nullable()->after('shipping_city');
                }
                if (!Schema::hasColumn('marketplace_orders', 'shipping_courier_code')) {
                    $table->string('shipping_courier_code', 50)->nullable()->after('shipping_courier');
                }
                if (!Schema::hasColumn('marketplace_orders', 'shipping_service_name')) {
                    $table->string('shipping_service_name', 100)->nullable()->after('shipping_courier_code');
                }
                if (!Schema::hasColumn('marketplace_orders', 'shipping_etd')) {
                    $table->string('shipping_etd', 50)->nullable()->after('shipping_service_name');
                }
                if (!Schema::hasColumn('marketplace_orders', 'dispute_reason')) {
                    $table->string('dispute_reason')->nullable()->after('status');
                }
                if (!Schema::hasColumn('marketplace_orders', 'dispute_notes')) {
                    $table->text('dispute_notes')->nullable()->after('dispute_reason');
                }
                if (!Schema::hasColumn('marketplace_orders', 'dispute_proof_images')) {
                    $table->json('dispute_proof_images')->nullable()->after('dispute_notes');
                }
                if (!Schema::hasColumn('marketplace_orders', 'return_tracking_number')) {
                    $table->string('return_tracking_number')->nullable()->after('shipping_tracking_number');
                }
                if (!Schema::hasColumn('marketplace_orders', 'return_courier')) {
                    $table->string('return_courier')->nullable()->after('return_tracking_number');
                }
                if (!Schema::hasColumn('marketplace_orders', 'processed_at')) {
                    $table->timestamp('processed_at')->nullable()->after('updated_at');
                }
                if (!Schema::hasColumn('marketplace_orders', 'packed_at')) {
                    $table->timestamp('packed_at')->nullable()->after('processed_at');
                }
                if (!Schema::hasColumn('marketplace_orders', 'shipped_at')) {
                    $table->timestamp('shipped_at')->nullable()->after('packed_at');
                }
                if (!Schema::hasColumn('marketplace_orders', 'delivered_at')) {
                    $table->timestamp('delivered_at')->nullable()->after('shipped_at');
                }
                if (!Schema::hasColumn('marketplace_orders', 'completed_at')) {
                    $table->timestamp('completed_at')->nullable()->after('delivered_at');
                }
                if (!Schema::hasColumn('marketplace_orders', 'disputed_at')) {
                    $table->timestamp('disputed_at')->nullable()->after('completed_at');
                }
                if (!Schema::hasColumn('marketplace_orders', 'returned_at')) {
                    $table->timestamp('returned_at')->nullable()->after('disputed_at');
                }
                if (!Schema::hasColumn('marketplace_orders', 'refunded_at')) {
                    $table->timestamp('refunded_at')->nullable()->after('returned_at');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('marketplace_consignment_intakes')) {
            Schema::table('marketplace_consignment_intakes', function (Blueprint $table) {
                $columns = ['owner_name', 'owner_phone'];
                foreach ($columns as $column) {
                    if (Schema::hasColumn('marketplace_consignment_intakes', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        if (Schema::hasTable('marketplace_orders')) {
            Schema::table('marketplace_orders', function (Blueprint $table) {
                $columns = [
                    'destination_city_id',
                    'shipping_courier_code',
                    'shipping_service_name',
                    'shipping_etd',
                    'dispute_reason',
                    'dispute_notes',
                    'dispute_proof_images',
                    'return_tracking_number',
                    'return_courier',
                    'processed_at',
                    'packed_at',
                    'shipped_at',
                    'delivered_at',
                    'completed_at',
                    'disputed_at',
                    'returned_at',
                    'refunded_at',
                ];
                foreach ($columns as $column) {
                    if (Schema::hasColumn('marketplace_orders', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
