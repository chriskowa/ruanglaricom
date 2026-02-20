<?php

namespace Tests\Feature;

use App\Models\Popup;
use App\Models\PopupStat;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PopupRuntimeTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_active_popups(): void
    {
        Popup::create([
            'name' => 'Promo',
            'slug' => 'promo',
            'status' => 'active',
            'content' => ['blocks' => [['type' => 'text', 'content' => 'Hello']]],
            'settings' => ['overlay' => 'rgba(0,0,0,0.5)'],
            'rules' => ['include_paths' => ['/*']],
        ]);

        $response = $this->getJson('/popups/active?path=/');
        $response->assertOk();
        $response->assertJsonCount(1, 'popups');
        $response->assertJsonPath('popups.0.slug', 'promo');
    }

    public function test_it_tracks_popup_events(): void
    {
        $popup = Popup::create([
            'name' => 'Promo',
            'slug' => 'promo',
            'status' => 'active',
            'content' => ['blocks' => []],
        ]);

        $response = $this->postJson("/popups/{$popup->id}/track", ['event' => 'view']);
        $response->assertOk();

        $stat = PopupStat::where('popup_id', $popup->id)->first();
        $this->assertNotNull($stat);
        $this->assertSame(1, $stat->views);
    }
}
