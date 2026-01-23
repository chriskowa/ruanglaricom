<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tabel Master Paket Membership EO
        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // LITE, PRO, PREMIUM
            $table->string('slug')->unique();
            $table->decimal('price', 12, 2); // Rp 0 - Rp 999.xxx.xxx
            $table->integer('duration_days')->default(365); // Default setahun
            $table->text('description')->nullable();
            $table->json('features')->nullable(); // List fitur dalam JSON
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. Tabel Transaksi Membership EO
        Schema::create('membership_transactions', function (Blueprint $table) {
            $table->uuid('id')->primary(); // Pakai UUID biar aman untuk Order ID Midtrans
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('package_id')->constrained('packages');
            
            $table->decimal('amount', 12, 2);
            $table->decimal('admin_fee', 10, 2)->default(0);
            $table->decimal('total_amount', 12, 2);
            
            $table->string('status')->default('pending'); // pending, paid, failed, expired
            $table->string('snap_token')->nullable(); // Token Midtrans
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('expired_at')->nullable(); // Kapan membership berakhir
            
            $table->timestamps();
        });

        // 3. Update table Users untuk status membership
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('current_package_id')->nullable()->after('package_tier')->constrained('packages')->nullOnDelete();
            $table->timestamp('membership_expires_at')->nullable()->after('current_package_id');
            $table->string('membership_status')->default('inactive')->after('role'); // inactive, active, expired
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['current_package_id']);
            $table->dropColumn(['current_package_id', 'membership_expires_at', 'membership_status']);
        });
        Schema::dropIfExists('membership_transactions');
        Schema::dropIfExists('packages');
    }
};
