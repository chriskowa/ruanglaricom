<?php

namespace Tests\Feature;

use App\Models\EoReportEmailDelivery;
use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminEmailReportPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_open_email_reports_page(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $eo = User::factory()->create(['role' => 'eo']);
        $event = Event::factory()->create(['user_id' => $eo->id]);

        EoReportEmailDelivery::create([
            'event_id' => $event->id,
            'eo_user_id' => $eo->id,
            'triggered_by_user_id' => $eo->id,
            'to_email' => 'eo@example.com',
            'to_name' => 'EO',
            'subject' => 'Laporan Event',
            'report_type' => 'event_report',
            'filters' => [],
            'queue' => 'emails-reports',
            'status' => 'sent',
            'attempts' => 1,
            'first_attempt_at' => now(),
            'last_attempt_at' => now(),
            'sent_at' => now(),
        ]);

        $resp = $this->actingAs($admin)->get(route('admin.email-reports.index'));
        $resp->assertOk();
        $resp->assertSee('EMAIL');
        $resp->assertSee('REPORT');
    }
}

