<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class EoEventMidtransDemoModeTest extends TestCase
{
    private function resetSchema(): void
    {
        Schema::dropIfExists('race_categories');
        Schema::dropIfExists('events');
        Schema::dropIfExists('users');

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('role')->nullable();
            $table->string('remember_token')->nullable();
            $table->timestamps();
        });

        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('slug')->unique();
            $table->string('name');
            $table->text('short_description')->nullable();
            $table->longText('full_description')->nullable();
            $table->longText('terms_and_conditions')->nullable();
            $table->dateTime('start_at');
            $table->dateTime('end_at')->nullable();
            $table->string('location_name');
            $table->string('hardcoded')->nullable();
            $table->text('custom_email_message')->nullable();
            $table->boolean('is_instant_notification')->default(false);
            $table->json('facilities')->nullable();
            $table->json('jersey_sizes')->nullable();
            $table->json('addons')->nullable();
            $table->json('gallery')->nullable();
            $table->json('sponsors')->nullable();
            $table->json('theme_colors')->nullable();
            $table->json('payment_config')->nullable();
            $table->json('whatsapp_config')->nullable();
            $table->json('premium_amenities')->nullable();
            $table->string('template')->nullable();
            $table->integer('platform_fee')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->string('status')->default('draft');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('race_categories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('event_id');
            $table->string('name');
            $table->decimal('distance_km', 6, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function test_eo_can_create_event_with_midtrans_demo_mode_enabled(): void
    {
        $this->resetSchema();

        $eo = User::factory()->create(['role' => 'eo']);

        $response = $this->actingAs($eo)->post(route('eo.events.store'), [
            'name' => 'Event Demo Mode',
            'start_at' => now()->addDays(7)->toDateTimeString(),
            'location_name' => 'GBK',
            'payment_config' => [
                'midtrans_demo_mode' => '1',
                'allowed_methods' => ['midtrans'],
            ],
            'categories' => [
                [
                    'name' => '5K',
                    'distance_km' => 5,
                    'is_active' => 1,
                ],
            ],
        ]);

        $response->assertRedirect(route('eo.events.index'));

        $event = Event::query()->where('name', 'Event Demo Mode')->first();
        $this->assertNotNull($event);
        $this->assertTrue((bool) ($event->payment_config['midtrans_demo_mode'] ?? false));
    }
}
