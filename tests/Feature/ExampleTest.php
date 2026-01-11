<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\RaceCategory;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/up');

        $response->assertStatus(200);
    }

    public function test_eo_can_update_race_categories_even_if_prizes_column_missing(): void
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
            $table->string('rpc_location_name')->nullable();
            $table->text('rpc_location_address')->nullable();
            $table->decimal('rpc_latitude', 10, 7)->nullable();
            $table->decimal('rpc_longitude', 10, 7)->nullable();
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
            $table->text('facilities')->nullable();
            $table->text('addons')->nullable();
            $table->text('jersey_sizes')->nullable();
            $table->text('gallery')->nullable();
            $table->text('theme_colors')->nullable();
            $table->text('premium_amenities')->nullable();
            $table->string('template')->nullable();
            $table->decimal('platform_fee', 10, 2)->nullable();
            $table->text('sponsors')->nullable();
            $table->timestamps();
        });

        Schema::create('race_categories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('event_id');
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
            $table->timestamps();
        });

        $user = User::create([
            'name' => 'EO Test',
            'email' => 'eo@example.com',
            'password' => Hash::make('password'),
            'role' => 'eo',
            'is_active' => true,
        ]);

        $event = Event::create([
            'user_id' => $user->id,
            'name' => 'Event Test',
            'slug' => 'event-test',
            'start_at' => now(),
            'location_name' => 'Jakarta',
        ]);

        $category = RaceCategory::create([
            'event_id' => $event->id,
            'name' => '10K Open',
            'quota' => 100,
            'is_active' => true,
        ]);

        $payload = [
            'name' => $event->name,
            'start_at' => $event->start_at->toISOString(),
            'location_name' => $event->location_name,
            'categories' => [
                [
                    'id' => $category->id,
                    'name' => '10K Open Updated',
                    'quota' => 200,
                    'prizes' => [
                        1 => 'Rp 5.000.000',
                        2 => 'Rp 3.000.000',
                        3 => 'Rp 1.500.000',
                    ],
                ],
            ],
        ];

        $response = $this->actingAs($user)->put(route('eo.events.update', $event), $payload);

        $response->assertRedirect(route('eo.events.index'));
        $this->assertDatabaseHas('race_categories', [
            'id' => $category->id,
            'name' => '10K Open Updated',
            'quota' => 200,
        ]);
    }
}
