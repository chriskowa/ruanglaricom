<?php

namespace Tests\Feature;

use App\Models\Coupon;
use App\Models\Event;
use App\Models\Participant;
use App\Models\RaceCategory;
use App\Models\Transaction;
use App\Services\MootaService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CouponRegistrationTest extends TestCase
{
    private function resetSchema(): void
    {
        Schema::dropIfExists('participants');
        Schema::dropIfExists('transactions');
        Schema::dropIfExists('coupons');
        Schema::dropIfExists('race_categories');
        Schema::dropIfExists('events');
        Schema::dropIfExists('users');

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->string('password');
            $table->string('role')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('remember_token')->nullable();
            $table->timestamps();
        });

        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('hardcoded')->nullable();
            $table->timestamp('start_at')->nullable();
            $table->string('location_name')->nullable();
            $table->integer('promo_buy_x')->nullable();
            $table->decimal('platform_fee', 10, 2)->nullable();
            $table->text('addons')->nullable();
            $table->text('jersey_sizes')->nullable();
            $table->text('payment_config')->nullable();
            $table->text('whatsapp_config')->nullable();
            $table->timestamps();
        });

        Schema::create('race_categories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('event_id');
            $table->string('name');
            $table->integer('quota')->nullable();
            $table->integer('price_early')->nullable();
            $table->integer('price_regular')->nullable();
            $table->integer('price_late')->nullable();
            $table->integer('early_bird_quota')->nullable();
            $table->timestamp('early_bird_end_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('event_id')->nullable();
            $table->string('code')->unique();
            $table->string('type');
            $table->decimal('value', 10, 2);
            $table->decimal('min_transaction_amount', 10, 2)->nullable();
            $table->integer('max_uses')->nullable();
            $table->integer('usage_limit_per_user')->nullable();
            $table->integer('used_count')->default(0);
            $table->timestamp('start_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_stackable')->default(true);
            $table->text('applicable_categories')->nullable();
            $table->timestamps();
        });

        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('public_ref')->nullable()->unique();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->text('pic_data')->nullable();
            $table->decimal('total_original', 12, 2)->default(0);
            $table->unsignedBigInteger('coupon_id')->nullable();
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('admin_fee', 12, 2)->default(0);
            $table->decimal('final_amount', 12, 2)->default(0);
            $table->string('payment_status')->default('pending');
            $table->string('payment_gateway')->nullable();
            $table->string('midtrans_mode')->nullable();
            $table->integer('unique_code')->default(0);
            $table->string('payment_channel')->nullable();
            $table->string('snap_token')->nullable();
            $table->string('midtrans_order_id')->nullable();
            $table->string('midtrans_transaction_status')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });

        Schema::create('participants', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('transaction_id')->nullable();
            $table->unsignedBigInteger('race_category_id')->nullable();
            $table->string('name');
            $table->string('gender')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('id_card')->nullable();
            $table->string('address', 500)->nullable();
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_number')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('target_time')->nullable();
            $table->string('bib_number')->nullable();
            $table->string('jersey_size')->nullable();
            $table->text('addons')->nullable();
            $table->string('status')->nullable();
            $table->string('price_type')->nullable();
            $table->timestamps();
        });
    }

    public function test_coupon_code_is_applied_and_saved_on_transaction(): void
    {
        config(['cache.default' => 'file']);
        Queue::fake();
        Http::fake([
            'https://www.google.com/recaptcha/api/siteverify' => Http::response(['success' => true], 200),
        ]);
        $this->resetSchema();
        $this->app->instance(MootaService::class, new class extends MootaService
        {
            public function __construct() {}

            public function generateUniqueCode($amount)
            {
                return 0;
            }
        });

        $event = Event::create([
            'name' => 'Event Coupon Test',
            'slug' => 'event-coupon-test',
            'start_at' => now(),
            'location_name' => 'Jakarta',
            'platform_fee' => 0,
        ]);

        $category = RaceCategory::create([
            'event_id' => $event->id,
            'name' => '10K',
            'price_regular' => 100000,
            'is_active' => true,
        ]);

        $coupon = Coupon::create([
            'event_id' => $event->id,
            'code' => 'DISC10',
            'type' => 'percent',
            'value' => 10,
            'min_transaction_amount' => 0,
            'used_count' => 0,
            'is_active' => true,
            'is_stackable' => true,
        ]);

        $payload = [
            'pic_name' => 'PIC',
            'pic_email' => 'pic@example.com',
            'pic_phone' => '081234567890',
            'payment_method' => 'cod',
            'coupon_code' => 'DISC10',
            'g-recaptcha-response' => 'test',
            'participants' => [
                [
                    'name' => 'Runner 1',
                    'gender' => 'male',
                    'email' => 'runner1@example.com',
                    'phone' => '081234567891',
                    'id_card' => '1234567890',
                    'address' => 'Jl. Coupon Test No. 1, Jakarta',
                    'category_id' => $category->id,
                    'emergency_contact_name' => 'EC',
                    'emergency_contact_number' => '081234567892',
                    'date_of_birth' => '2000-01-01',
                    'jersey_size' => 'M',
                ],
            ],
        ];

        $response = $this->postJson(route('events.register.store', $event->slug), $payload);
        $response->assertOk()->assertJsonPath('success', true);

        $tx = Transaction::firstOrFail();
        $this->assertSame($coupon->id, $tx->coupon_id);
        $this->assertSame(100000.0, (float) $tx->total_original);
        $this->assertSame(10000.0, (float) $tx->discount_amount);
        $this->assertSame(90000.0, (float) $tx->final_amount);

        $participant = Participant::firstOrFail();
        $this->assertSame('2000-01-01', $participant->date_of_birth?->format('Y-m-d'));
        $this->assertSame('regular', $participant->price_type);
    }

    public function test_latbarkamis_registration_does_not_require_date_of_birth(): void
    {
        config(['cache.default' => 'file']);
        Queue::fake();
        Http::fake([
            'https://www.google.com/recaptcha/api/siteverify' => Http::response(['success' => true], 200),
        ]);
        $this->resetSchema();
        $this->app->instance(MootaService::class, new class extends MootaService
        {
            public function __construct() {}

            public function generateUniqueCode($amount)
            {
                return 0;
            }
        });

        $event = Event::create([
            'name' => 'Latbar Kamis',
            'slug' => 'latbar-kamis',
            'hardcoded' => 'latbarkamis',
            'start_at' => now(),
            'location_name' => 'Jakarta',
            'platform_fee' => 0,
        ]);

        $category = RaceCategory::create([
            'event_id' => $event->id,
            'name' => 'Latbar',
            'price_regular' => 15000,
            'is_active' => true,
        ]);

        $payload = [
            'pic_name' => 'PIC',
            'pic_email' => 'pic-latbar@example.com',
            'pic_phone' => '081234567890',
            'payment_method' => 'cod',
            'g-recaptcha-response' => 'test',
            'participants' => [
                [
                    'name' => 'Runner 1',
                    'gender' => 'male',
                    'email' => 'runner1-latbar@example.com',
                    'phone' => '081234567891',
                    'id_card' => '081234567891',
                    'address' => 'Jl. Coupon Test No. 2, Jakarta',
                    'category_id' => $category->id,
                    'emergency_contact_name' => 'EC',
                    'emergency_contact_number' => '081234567892',
                ],
            ],
        ];

        $response = $this->postJson(route('events.register.store', $event->slug), $payload);
        $response->assertOk()->assertJsonPath('success', true);
    }
}
