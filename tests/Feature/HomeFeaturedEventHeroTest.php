<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class HomeFeaturedEventHeroTest extends TestCase
{
    public function test_home_hero_renders_featured_event_overlay(): void
    {
        Schema::dropIfExists('app_settings');
        Schema::create('app_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        $event = new Event([
            'name' => 'Featured Test Event',
            'slug' => 'featured-test-event',
            'is_featured' => true,
            'status' => 'published',
            'start_at' => now()->addDays(10),
            'location_name' => 'GBK Senayan',
            'hero_image_url' => 'https://example.test/hero.jpg',
        ]);

        $event->setRelation('user', new User(['name' => 'EO Test']));

        $response = $this->view('home.index', [
            'homepageContent' => null,
            'featuredEvent' => $event,
            'leaderboard' => null,
            'topRunner' => null,
            'topPacer' => null,
            'topCoach' => null,
            'topCoachData' => null,
            'totalUsers' => 0,
            'hideNav' => true,
            'hideFooter' => true,
            'hideChat' => true,
        ]);

        $response->assertSee('Featured Test Event');
        $response->assertSee(route('running-event.detail', $event->slug));
    }
}
