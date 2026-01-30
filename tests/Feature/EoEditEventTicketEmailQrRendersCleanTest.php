<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class EoEditEventTicketEmailQrRendersCleanTest extends TestCase
{
    private function resetSchema(): void
    {
        Schema::dropIfExists('menu_items');
        Schema::dropIfExists('menus');
        Schema::dropIfExists('app_settings');
        Schema::dropIfExists('race_categories');
        Schema::dropIfExists('master_gpxes');
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

        Schema::create('app_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('location');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('menu_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('menu_id');
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->string('title');
            $table->string('url');
            $table->string('icon')->nullable();
            $table->string('target')->nullable();
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('slug')->unique();
            $table->string('name');
            $table->dateTime('start_at');
            $table->dateTime('end_at')->nullable();
            $table->string('location_name');
            $table->text('custom_email_message')->nullable();
            $table->boolean('ticket_email_use_qr')->default(true);
            $table->boolean('is_instant_notification')->default(false);
            $table->json('payment_config')->nullable();
            $table->json('whatsapp_config')->nullable();
            $table->json('premium_amenities')->nullable();
            $table->timestamps();
        });

        Schema::create('race_categories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('event_id');
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('master_gpxes', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->boolean('is_published')->default(true);
            $table->timestamps();
            $table->timestamp('deleted_at')->nullable();
        });
    }

    public function test_eo_edit_event_page_does_not_render_raw_blade_snippets(): void
    {
        $this->resetSchema();

        $eo = User::factory()->create(['role' => 'eo']);
        $event = Event::query()->create([
            'user_id' => $eo->id,
            'name' => 'Event C',
            'slug' => 'event-c',
            'start_at' => now()->addDays(10),
            'location_name' => 'GBK',
            'ticket_email_use_qr' => true,
            'premium_amenities' => [],
        ]);

        $response = $this->actingAs($eo)->get(route('eo.events.edit', $event));

        $response->assertOk();
        $response->assertDontSee("@error('ticket_email_use_qr')", false);
        $response->assertDontSee('ticket_email_use_qr ?? true', false);
        $response->assertDontSee("is_instant_notification) ? 'checked'", false);
    }
}
