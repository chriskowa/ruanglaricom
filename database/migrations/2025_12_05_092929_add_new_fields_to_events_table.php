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
        Schema::table('events', function (Blueprint $table) {
            // Ubah hero_image_url menjadi nullable karena akan ada hero_image sebagai file upload
            // Tambahkan field baru untuk upload image
            if (!Schema::hasColumn('events', 'hero_image')) {
                $table->string('hero_image')->nullable()->after('hero_image_url');
            }
            if (!Schema::hasColumn('events', 'logo_image')) {
                $table->string('logo_image')->nullable()->after('hero_image');
            }
            if (!Schema::hasColumn('events', 'floating_image')) {
                $table->string('floating_image')->nullable()->after('logo_image');
            }
            if (!Schema::hasColumn('events', 'medal_image')) {
                $table->string('medal_image')->nullable()->after('floating_image');
            }
            if (!Schema::hasColumn('events', 'jersey_image')) {
                $table->string('jersey_image')->nullable()->after('medal_image');
            }
            
            // Waktu registrasi open dan close
            if (!Schema::hasColumn('events', 'registration_open_at')) {
                $table->dateTime('registration_open_at')->nullable()->after('google_calendar_url');
            }
            if (!Schema::hasColumn('events', 'registration_close_at')) {
                $table->dateTime('registration_close_at')->nullable()->after('registration_open_at');
            }
            
            // Kode promo (bisa juga menggunakan relasi ke coupons, tapi untuk simpel kita buat field langsung dulu)
            if (!Schema::hasColumn('events', 'promo_code')) {
                $table->string('promo_code', 50)->nullable()->after('registration_close_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            if (Schema::hasColumn('events', 'promo_code')) {
                $table->dropColumn('promo_code');
            }
            if (Schema::hasColumn('events', 'registration_close_at')) {
                $table->dropColumn('registration_close_at');
            }
            if (Schema::hasColumn('events', 'registration_open_at')) {
                $table->dropColumn('registration_open_at');
            }
            if (Schema::hasColumn('events', 'jersey_image')) {
                $table->dropColumn('jersey_image');
            }
            if (Schema::hasColumn('events', 'medal_image')) {
                $table->dropColumn('medal_image');
            }
            if (Schema::hasColumn('events', 'floating_image')) {
                $table->dropColumn('floating_image');
            }
            if (Schema::hasColumn('events', 'logo_image')) {
                $table->dropColumn('logo_image');
            }
            if (Schema::hasColumn('events', 'hero_image')) {
                $table->dropColumn('hero_image');
            }
        });
    }
};
