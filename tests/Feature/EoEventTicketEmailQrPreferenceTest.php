<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class EoEventTicketEmailQrPreferenceTest extends TestCase
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
            $table->string('location_address')->nullable();
            $table->decimal('location_lat', 10, 7)->nullable();
            $table->decimal('location_lng', 10, 7)->nullable();
            $table->string('hardcoded')->nullable();
            $table->string('rpc_location_name')->nullable();
            $table->string('rpc_location_address')->nullable();
            $table->decimal('rpc_latitude', 10, 7)->nullable();
            $table->decimal('rpc_longitude', 10, 7)->nullable();
            $table->string('hero_image_url')->nullable();
            $table->string('hero_image')->nullable();
            $table->string('logo_image')->nullable();
            $table->string('floating_image')->nullable();
            $table->string('medal_image')->nullable();
            $table->string('jersey_image')->nullable();
            $table->string('map_embed_url')->nullable();
            $table->string('google_calendar_url')->nullable();
            $table->dateTime('registration_open_at')->nullable();
            $table->dateTime('registration_close_at')->nullable();
            $table->string('promo_code')->nullable();
            $table->integer('promo_buy_x')->nullable();
            $table->text('custom_email_message')->nullable();
            $table->boolean('ticket_email_use_qr')->default(true);
            $table->boolean('is_instant_notification')->default(false);
            $table->json('facilities')->nullable();
            $table->json('gallery')->nullable();
            $table->json('sponsors')->nullable();
            $table->json('theme_colors')->nullable();
            $table->json('jersey_sizes')->nullable();
            $table->json('addons')->nullable();
            $table->json('premium_amenities')->nullable();
            $table->string('template')->nullable();
            $table->integer('platform_fee')->default(0);
            $table->json('payment_config')->nullable();
            $table->json('whatsapp_config')->nullable();
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
            $table->string('code')->nullable();
            $table->integer('quota')->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('prizes')->nullable();
            $table->timestamps();
        });
    }

    public function test_eo_can_create_event_with_ticket_email_qr_preference(): void
    {
        $this->resetSchema();

        $eo = User::factory()->create(['role' => 'eo']);

        $response = $this->actingAs($eo)->post(route('eo.events.store'), [
            'name' => 'Event A',
            'start_at' => now()->addDays(7)->toDateTimeString(),
            'location_name' => 'GBK',
            'ticket_email_use_qr' => '0',
            'categories' => [
                [
                    'name' => '5K',
                    'distance_km' => 5,
                    'is_active' => 1,
                ],
            ],
        ]);

        $response->assertRedirect(route('eo.events.index'));

        $event = Event::query()->where('name', 'Event A')->first();
        $this->assertNotNull($event);
        $this->assertFalse((bool) $event->ticket_email_use_qr);
    }

    public function test_eo_can_update_event_ticket_email_qr_preference(): void
    {
        $this->resetSchema();

        $eo = User::factory()->create(['role' => 'eo']);
        $event = Event::query()->create([
            'user_id' => $eo->id,
            'name' => 'Event B',
            'slug' => 'event-b',
            'start_at' => now()->addDays(10),
            'location_name' => 'GBK',
            'ticket_email_use_qr' => true,
            'premium_amenities' => [],
        ]);

        $response = $this->actingAs($eo)->put(route('eo.events.update', $event), [
            'name' => 'Event B',
            'start_at' => $event->start_at->toDateTimeString(),
            'location_name' => $event->location_name,
            'ticket_email_use_qr' => '0',
        ]);

        $response->assertRedirect(route('eo.events.index'));

        $event->refresh();
        $this->assertFalse((bool) $event->ticket_email_use_qr);
    }
}
