<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class JoinNowRedirectTest extends TestCase
{
    private function resetSchema(): void
    {
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
            $table->dateTime('start_at');
            $table->dateTime('end_at')->nullable();
            $table->string('location_name');
            $table->string('hero_image_url')->nullable();
            $table->string('hero_image')->nullable();
            $table->string('organizer_name')->nullable();
            $table->string('status')->default('published');
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function test_join_now_redirects_to_featured_active_event_detail(): void
    {
        $this->resetSchema();

        $eo = User::factory()->create(['role' => 'eo']);
        $event = Event::factory()->for($eo, 'user')->create([
            'slug' => 'featured-active',
            'is_featured' => true,
            'is_active' => true,
            'status' => 'published',
            'start_at' => now()->addDays(7),
        ]);

        $this->get(route('home.join-now'))
            ->assertRedirect(route('running-event.detail', $event->slug));
    }

    public function test_join_now_redirects_to_events_index_when_featured_event_not_available(): void
    {
        $this->resetSchema();

        $eo = User::factory()->create(['role' => 'eo']);
        Event::factory()->for($eo, 'user')->create([
            'slug' => 'featured-inactive',
            'is_featured' => true,
            'is_active' => false,
            'status' => 'published',
            'start_at' => now()->addDays(7),
        ]);

        $this->get(route('home.join-now'))
            ->assertRedirect(route('events.index'));
    }

    public function test_join_now_redirects_to_events_index_when_no_featured_event_exists(): void
    {
        $this->resetSchema();

        $this->get(route('home.join-now'))
            ->assertRedirect(route('events.index'));
    }
}
