<?php

namespace Tests\Feature\EO;

use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EoEventPaymentConfigSaveTest extends TestCase
{
    use RefreshDatabase;

    public function test_eo_can_save_allowed_payment_methods(): void
    {
        $eo = User::factory()->create([
            'role' => 'eo',
            'is_active' => true,
        ]);

        $event = Event::factory()->create([
            'user_id' => $eo->id,
            'payment_config' => [
                'allowed_methods' => ['midtrans'],
                'midtrans_demo_mode' => 0,
            ],
        ]);

        $payload = [
            'name' => $event->name,
            'slug' => $event->slug,
            'start_at' => $event->start_at->toDateTimeString(),
            'location_name' => $event->location_name,
            'payment_config' => [
                'midtrans_demo_mode' => '1',
                'allowed_methods' => ['moota'],
            ],
        ];

        $res = $this->actingAs($eo)->put(route('eo.events.update', $event), $payload);
        $res->assertRedirect();

        $event->refresh();
        $this->assertSame(['moota'], $event->payment_config['allowed_methods'] ?? null);
        $this->assertTrue((bool) ($event->payment_config['midtrans_demo_mode'] ?? false));
    }

    public function test_eo_all_option_is_expanded(): void
    {
        $eo = User::factory()->create([
            'role' => 'eo',
            'is_active' => true,
        ]);

        $event = Event::factory()->create([
            'user_id' => $eo->id,
        ]);

        $payload = [
            'name' => $event->name,
            'slug' => $event->slug,
            'start_at' => $event->start_at->toDateTimeString(),
            'location_name' => $event->location_name,
            'payment_config' => [
                'allowed_methods' => ['all'],
            ],
        ];

        $res = $this->actingAs($eo)->put(route('eo.events.update', $event), $payload);
        $res->assertRedirect();

        $event->refresh();
        $this->assertSame(['midtrans', 'moota'], $event->payment_config['allowed_methods'] ?? null);
    }
}

