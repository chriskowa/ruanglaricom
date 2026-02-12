<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Services\EventInstantEmailLimiter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class InstantEmailRateLimitTest extends TestCase
{
    use RefreshDatabase;

    public function test_reserve_allows_up_to_five_emails_per_minute_per_event(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-01-31 10:00:10'));
        $event = Event::factory()->create();
        $limiter = app(EventInstantEmailLimiter::class);

        $delay1 = $limiter->reserve($event->id, 5, 5);
        $delay2 = $limiter->reserve($event->id, 1, 5);

        $this->assertSame(0, $delay1);
        $this->assertGreaterThanOrEqual(40, $delay2);
        $this->assertLessThanOrEqual(70, $delay2);
    }
}
