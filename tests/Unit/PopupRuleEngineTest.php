<?php

namespace Tests\Unit;

use App\Models\Popup;
use App\Services\PopupRuleEngine;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Tests\TestCase;

class PopupRuleEngineTest extends TestCase
{
    public function test_it_matches_paths_and_schedule(): void
    {
        $popup = new Popup([
            'status' => 'active',
            'starts_at' => Carbon::now()->subHour(),
            'ends_at' => Carbon::now()->addHour(),
            'timezone' => 'UTC',
            'rules' => [
                'include_paths' => ['/booking*'],
                'exclude_paths' => ['/booking/checkout*'],
                'devices' => ['desktop'],
            ],
        ]);
        $engine = new PopupRuleEngine();

        $request = Request::create('/popups/active', 'GET', ['path' => '/booking']);
        $request->headers->set('User-Agent', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)');
        $this->assertTrue($engine->isEligible($popup, $request, null));

        $request2 = Request::create('/popups/active', 'GET', ['path' => '/booking/checkout']);
        $request2->headers->set('User-Agent', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)');
        $this->assertFalse($engine->isEligible($popup, $request2, null));
    }

    public function test_it_blocks_future_schedule(): void
    {
        $popup = new Popup([
            'status' => 'active',
            'starts_at' => Carbon::now()->addHours(2),
            'ends_at' => Carbon::now()->addHours(5),
            'timezone' => 'UTC',
            'rules' => [
                'include_paths' => ['/'],
            ],
        ]);
        $engine = new PopupRuleEngine();
        $request = Request::create('/popups/active', 'GET', ['path' => '/']);
        $this->assertFalse($engine->isEligible($popup, $request, null));
    }
}
