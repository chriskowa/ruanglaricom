<?php

namespace Tests\Feature;

use App\Models\Community;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommunityProfilePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_renders_with_basic_data(): void
    {
        $community = Community::create([
            'name' => 'Volt Runners',
            'slug' => 'volt-runners',
            'pic_name' => 'Admin',
            'pic_email' => 'admin@example.com',
            'pic_phone' => '08123',
            'theme_color' => 'neon',
            'description' => 'Komunitas lari modern.',
            'schedules' => [
                ['day' => 'Tuesday', 'time' => '19:00', 'activity' => 'Interval', 'location' => 'GBK'],
            ],
            'captains' => [
                ['name' => 'Sarah', 'role' => 'Head Coach', 'image' => null],
            ],
        ]);

        $resp = $this->get(route('community.profile', ['slug' => $community->slug]));
        $resp->assertOk();
        $resp->assertSee('Volt Runners');
        $resp->assertSee('Weekly');
        $resp->assertSee('Captains');
    }
}
