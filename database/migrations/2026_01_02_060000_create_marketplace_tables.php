<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Settings for dynamic configs (Commission %)
        Schema::create('app_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        Schema::create('marketplace_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('icon')->nullable();
            $table->timestamps();
        });

        Schema::create('marketplace_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Seller
            $table->foreignId('category_id')->constrained('marketplace_categories');
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description');
            $table->decimal('price', 15, 2);
            $table->integer('stock')->default(1);
            $table->enum('condition', ['new', 'used']);
            $table->enum('type', ['physical', 'digital_slot']);
            $table->json('meta_data')->nullable(); // Size, Color, Race Name, etc.
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('marketplace_product_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('marketplace_products')->onDelete('cascade');
            $table->string('image_path');
            $table->boolean('is_primary')->default(false);
            $table->timestamps();
        });

        Schema::create('marketplace_orders', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->foreignId('buyer_id')->constrained('users');
            $table->foreignId('seller_id')->constrained('users');
            $table->decimal('total_amount', 15, 2); // Price + Shipping
            $table->decimal('commission_amount', 15, 2); // 1%
            $table->decimal('seller_amount', 15, 2); // Total - Commission
            $table->enum('status', ['pending', 'paid', 'shipped', 'completed', 'cancelled', 'disputed'])->default('pending');
            $table->string('snap_token')->nullable();
            $table->string('shipping_tracking_number')->nullable();
            $table->timestamps();
        });

        Schema::create('marketplace_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('marketplace_orders')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('marketplace_products');
            $table->string('product_title_snapshot');
            $table->decimal('price_snapshot', 15, 2);
            $table->integer('quantity');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketplace_order_items');
        Schema::dropIfExists('marketplace_orders');
        Schema::dropIfExists('marketplace_product_images');
        Schema::dropIfExists('marketplace_products');
        Schema::dropIfExists('marketplace_categories');
        Schema::dropIfExists('app_settings');
    }
};
