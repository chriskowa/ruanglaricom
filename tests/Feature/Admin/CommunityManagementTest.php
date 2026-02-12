<?php

namespace Tests\Feature\Admin;

use App\Models\City;
use App\Models\Community;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommunityManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Assuming admin auth is needed, acting as a user
        $this->actingAs(User::factory()->create(['role' => 'admin']));
    }

    public function test_admin_can_view_community_create_page()
    {
        $response = $this->get(route('admin.communities.create'));
        $response->assertStatus(200);
        $response->assertSee('CREATE COMMUNITY');
        // Check for pacerhub layout indicator (e.g., sidebar or specific class)
        $response->assertSee('bg-slate-900');
    }

    public function test_admin_can_view_community_edit_page()
    {
        $community = Community::factory()->create();
        $response = $this->get(route('admin.communities.edit', $community));
        $response->assertStatus(200);
        $response->assertSee('EDIT COMMUNITY');
        $response->assertSee($community->name);
    }

    public function test_admin_can_create_community()
    {
        $city = City::factory()->create();

        $data = [
            'name' => 'New Community',
            'slug' => 'new-community',
            'city_id' => $city->id,
            'pic_name' => 'PIC',
            'pic_email' => 'pic@test.com',
            'pic_phone' => '08123',
            'theme_color' => 'neon',
            'schedules' => [
                ['day' => 'Monday', 'time' => '19:00', 'activity' => 'Run', 'location' => 'Park'],
            ],
            'tiktok_link' => 'https://tiktok.com/@test',
        ];

        $response = $this->post(route('admin.communities.store'), $data);

        $response->assertRedirect(route('admin.communities.index'));
        $this->assertDatabaseHas('communities', ['name' => 'New Community', 'tiktok_link' => 'https://tiktok.com/@test']);
    }

    public function test_admin_can_update_community_clearing_schedules()
    {
        $community = Community::factory()->create([
            'schedules' => [['day' => 'Monday', 'time' => '19:00']],
        ]);

        // Update with NO schedules (simulating empty form)
        $data = [
            'name' => $community->name,
            'slug' => $community->slug,
            'pic_name' => $community->pic_name,
            'pic_email' => $community->pic_email,
            'pic_phone' => $community->pic_phone,
            // 'schedules' => missing
        ];

        $response = $this->put(route('admin.communities.update', $community), $data);

        $response->assertRedirect(route('admin.communities.index'));

        $community->refresh();
        $this->assertEmpty($community->schedules);
    }

    public function test_admin_can_manage_community_faqs()
    {
        $city = City::factory()->create();

        $data = [
            'name' => 'FAQ Community',
            'slug' => 'faq-community',
            'city_id' => $city->id,
            'pic_name' => 'PIC',
            'pic_email' => 'pic@test.com',
            'pic_phone' => '08123',
            'faqs' => [
                ['question' => 'Q1', 'answer' => 'A1'],
                ['question' => 'Q2', 'answer' => 'A2'],
            ],
        ];

        $response = $this->post(route('admin.communities.store'), $data);
        $response->assertRedirect(route('admin.communities.index'));

        $community = Community::where('slug', 'faq-community')->first();
        $this->assertCount(2, $community->faqs);
        $this->assertEquals('Q1', $community->faqs[0]['question']);

        // Now update to remove FAQs
        $updateData = [
            'name' => 'FAQ Community',
            'slug' => 'faq-community',
            'pic_name' => 'PIC',
            'pic_email' => 'pic@test.com',
            'pic_phone' => '08123',
            // 'faqs' => missing
        ];

        $response = $this->put(route('admin.communities.update', $community), $updateData);
        $response->assertRedirect(route('admin.communities.index'));

        $community->refresh();
        $this->assertEmpty($community->faqs);
    }
}
