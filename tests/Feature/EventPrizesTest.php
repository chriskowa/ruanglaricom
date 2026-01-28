<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\RaceCategory;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class EventPrizesTest extends TestCase
{
    private function resetSchemaWithPrizesColumn(): void
    {
        Schema::dropIfExists('race_categories');
        Schema::dropIfExists('events');
        Schema::dropIfExists('users');

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('role')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('name');
            $table->string('slug')->nullable();
            $table->string('hardcoded')->nullable();
            $table->text('short_description')->nullable();
            $table->text('full_description')->nullable();
            $table->text('terms_and_conditions')->nullable();
            $table->timestamp('start_at')->nullable();
            $table->timestamp('end_at')->nullable();
            $table->string('location_name')->nullable();
            $table->text('location_address')->nullable();
            $table->decimal('location_lat', 10, 7)->nullable();
            $table->decimal('location_lng', 10, 7)->nullable();
            $table->text('hero_image_url')->nullable();
            $table->text('hero_image')->nullable();
            $table->text('logo_image')->nullable();
            $table->text('floating_image')->nullable();
            $table->text('medal_image')->nullable();
            $table->text('jersey_image')->nullable();
            $table->text('map_embed_url')->nullable();
            $table->text('google_calendar_url')->nullable();
            $table->timestamp('registration_open_at')->nullable();
            $table->timestamp('registration_close_at')->nullable();
            $table->string('promo_code')->nullable();
            $table->integer('promo_buy_x')->nullable();
            $table->text('custom_email_message')->nullable();
            $table->boolean('is_instant_notification')->default(false);
            $table->text('facilities')->nullable();
            $table->text('addons')->nullable();
            $table->text('jersey_sizes')->nullable();
            $table->text('gallery')->nullable();
            $table->text('theme_colors')->nullable();
            $table->text('premium_amenities')->nullable();
            $table->string('template')->nullable();
            $table->decimal('platform_fee', 10, 2)->nullable();
            $table->text('sponsors')->nullable();
            $table->text('payment_config')->nullable();
            $table->text('whatsapp_config')->nullable();
            $table->timestamps();
        });

        Schema::create('race_categories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('master_gpx_id')->nullable();
            $table->string('name');
            $table->decimal('distance_km', 8, 2)->nullable();
            $table->string('code')->nullable();
            $table->integer('quota')->nullable();
            $table->integer('min_age')->nullable();
            $table->integer('max_age')->nullable();
            $table->integer('cutoff_minutes')->nullable();
            $table->integer('price_early')->nullable();
            $table->integer('price_regular')->nullable();
            $table->integer('price_late')->nullable();
            $table->timestamp('reg_start_at')->nullable();
            $table->timestamp('reg_end_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('prizes')->nullable();
            $table->integer('early_bird_quota')->nullable();
            $table->timestamp('early_bird_end_at')->nullable();
            $table->timestamps();
        });
    }

    public function test_eo_can_create_event_with_optional_prizes_and_empty_rows_are_filtered(): void
    {
        $this->resetSchemaWithPrizesColumn();

        $user = User::create([
            'name' => 'EO Test',
            'email' => 'eo_create@example.com',
            'password' => Hash::make('password'),
            'role' => 'eo',
            'is_active' => true,
        ]);

        $payload = [
            'name' => 'Event Test Create',
            'start_at' => now()->toISOString(),
            'location_name' => 'Jakarta',
            'categories' => [
                [
                    'name' => '10K Open',
                    'quota' => 100,
                    'prizes' => [
                        1 => 'Rp 5.000.000',
                        2 => '',
                        3 => '   ',
                    ],
                ],
            ],
        ];

        $response = $this->actingAs($user)->post(route('eo.events.store'), $payload);
        $response->assertRedirect(route('eo.events.index'));

        $event = Event::where('name', 'Event Test Create')->firstOrFail();
        $this->assertDatabaseHas('race_categories', [
            'event_id' => $event->id,
            'name' => '10K Open',
        ]);

        $category = RaceCategory::where('event_id', $event->id)->where('name', '10K Open')->firstOrFail();
        $this->assertSame([1 => 'Rp 5.000.000'], $category->prizes);
    }

    public function test_eo_can_update_event_and_clear_all_prizes(): void
    {
        $this->resetSchemaWithPrizesColumn();

        $user = User::create([
            'name' => 'EO Test',
            'email' => 'eo_update@example.com',
            'password' => Hash::make('password'),
            'role' => 'eo',
            'is_active' => true,
        ]);

        $event = Event::create([
            'user_id' => $user->id,
            'name' => 'Event Test Update',
            'slug' => 'event-test-update',
            'start_at' => now(),
            'location_name' => 'Jakarta',
            'premium_amenities' => [],
        ]);

        $category = RaceCategory::create([
            'event_id' => $event->id,
            'name' => '10K Open',
            'quota' => 100,
            'is_active' => true,
            'prizes' => [
                1 => 'A',
                2 => 'B',
            ],
        ]);

        $payload = [
            'name' => $event->name,
            'start_at' => $event->start_at->toISOString(),
            'location_name' => $event->location_name,
            'categories' => [
                [
                    'id' => $category->id,
                    'name' => '10K Open',
                    'quota' => 100,
                    'prizes' => [
                        1 => '',
                        2 => ' ',
                    ],
                ],
            ],
        ];

        $response = $this->actingAs($user)->put(route('eo.events.update', $event), $payload);
        $response->assertRedirect(route('eo.events.index'));

        $category->refresh();
        $this->assertSame([], $category->prizes);
    }
}

