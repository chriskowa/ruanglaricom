<?php

namespace Tests\Feature;

use App\Models\EoReportEmailDelivery;
use App\Models\Event;
use App\Models\EventEmailDeliveryLog;
use App\Models\EventEmailMinuteCounter;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EoEmailMonitoringPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_eo_only_sees_monitoring_for_own_events(): void
    {
        $eo1 = User::factory()->create(['role' => 'eo']);
        $eo2 = User::factory()->create(['role' => 'eo']);

        $event1 = Event::factory()->create(['user_id' => $eo1->id, 'name' => 'EO1 Event']);
        $event2 = Event::factory()->create(['user_id' => $eo2->id, 'name' => 'EO2 Event']);

        EventEmailMinuteCounter::create([
            'event_id' => $event1->id,
            'minute_at' => now()->startOfMinute(),
            'reserved_emails' => 3,
        ]);
        EventEmailMinuteCounter::create([
            'event_id' => $event2->id,
            'minute_at' => now()->startOfMinute(),
            'reserved_emails' => 7,
        ]);

        EventEmailDeliveryLog::create([
            'event_id' => $event1->id,
            'transaction_id' => null,
            'channel' => 'email',
            'to' => 'a@example.com',
            'status' => 'failed',
            'error_code' => 'x',
            'error_message' => 'fail',
        ]);
        EventEmailDeliveryLog::create([
            'event_id' => $event2->id,
            'transaction_id' => null,
            'channel' => 'email',
            'to' => 'b@example.com',
            'status' => 'failed',
            'error_code' => 'x',
            'error_message' => 'fail',
        ]);

        EoReportEmailDelivery::create([
            'event_id' => $event1->id,
            'eo_user_id' => $eo1->id,
            'triggered_by_user_id' => $eo1->id,
            'to_email' => 'eo1@example.com',
            'to_name' => 'EO1',
            'subject' => 'Laporan',
            'report_type' => 'event_report',
            'filters' => [],
            'queue' => 'emails-reports',
            'status' => 'sent',
            'attempts' => 1,
            'first_attempt_at' => now(),
            'last_attempt_at' => now(),
            'sent_at' => now(),
        ]);

        EoReportEmailDelivery::create([
            'event_id' => $event2->id,
            'eo_user_id' => $eo2->id,
            'triggered_by_user_id' => $eo2->id,
            'to_email' => 'eo2@example.com',
            'to_name' => 'EO2',
            'subject' => 'Laporan',
            'report_type' => 'event_report',
            'filters' => [],
            'queue' => 'emails-reports',
            'status' => 'sent',
            'attempts' => 1,
            'first_attempt_at' => now(),
            'last_attempt_at' => now(),
            'sent_at' => now(),
        ]);

        $resp = $this->actingAs($eo1)->get(route('eo.email-monitoring.index'));
        $resp->assertOk();
        $resp->assertSee('EO1 Event');
        $resp->assertDontSee('EO2 Event');
    }
}

