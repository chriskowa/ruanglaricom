<?php

namespace Tests\Feature;

use App\Jobs\SendEoReportEmail;
use App\Models\EoReportEmailDelivery;
use App\Models\Event;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class EoReportEmailDeliveryTest extends TestCase
{
    use RefreshDatabase;

    public function test_job_marks_delivery_sent_on_success(): void
    {
        $eo = User::factory()->create(['role' => 'eo']);
        $event = Event::factory()->create(['user_id' => $eo->id]);

        $delivery = EoReportEmailDelivery::create([
            'event_id' => $event->id,
            'eo_user_id' => $eo->id,
            'triggered_by_user_id' => $eo->id,
            'to_email' => 'eo@example.com',
            'to_name' => 'EO',
            'subject' => 'Laporan Event',
            'report_type' => 'event_report',
            'filters' => ['date_from' => now()->toDateString()],
            'queue' => 'emails-reports',
            'status' => 'pending',
        ]);

        $mailer = Mockery::mock(Mailer::class);
        $mailer->shouldReceive('to')->once()->with('eo@example.com')->andReturnSelf();
        $mailer->shouldReceive('send')->once();

        $job = new SendEoReportEmail($delivery->id);
        $job->handle($mailer);

        $delivery->refresh();
        $this->assertSame('sent', $delivery->status);
        $this->assertNotNull($delivery->sent_at);
        $this->assertSame(1, (int) $delivery->attempts);
        $this->assertNotNull($delivery->first_attempt_at);
        $this->assertNotNull($delivery->last_attempt_at);
        $this->assertNull($delivery->failure_code);
    }

    public function test_job_marks_delivery_failed_on_transport_exception(): void
    {
        $eo = User::factory()->create(['role' => 'eo']);
        $event = Event::factory()->create(['user_id' => $eo->id]);

        $delivery = EoReportEmailDelivery::create([
            'event_id' => $event->id,
            'eo_user_id' => $eo->id,
            'triggered_by_user_id' => $eo->id,
            'to_email' => 'eo@example.com',
            'to_name' => 'EO',
            'subject' => 'Laporan Event',
            'report_type' => 'event_report',
            'filters' => [],
            'queue' => 'emails-reports',
            'status' => 'pending',
        ]);

        $mailer = Mockery::mock(Mailer::class);
        $mailer->shouldReceive('to')->once()->with('eo@example.com')->andReturnSelf();
        $mailer->shouldReceive('send')->once()->andThrow(new \RuntimeException('Connection timeout'));

        $job = new SendEoReportEmail($delivery->id);
        $job->handle($mailer);

        $delivery->refresh();
        $this->assertSame('failed', $delivery->status);
        $this->assertSame('transport_error', $delivery->failure_code);
        $this->assertNotEmpty($delivery->failure_message);
        $this->assertSame(1, (int) $delivery->attempts);
        $this->assertNotNull($delivery->first_attempt_at);
    }

    public function test_job_marks_delivery_failed_when_email_invalid(): void
    {
        $eo = User::factory()->create(['role' => 'eo']);
        $event = Event::factory()->create(['user_id' => $eo->id]);

        $delivery = EoReportEmailDelivery::create([
            'event_id' => $event->id,
            'eo_user_id' => $eo->id,
            'triggered_by_user_id' => $eo->id,
            'to_email' => 'invalid-email',
            'to_name' => 'EO',
            'subject' => 'Laporan Event',
            'report_type' => 'event_report',
            'filters' => [],
            'queue' => 'emails-reports',
            'status' => 'pending',
        ]);

        $mailer = Mockery::mock(Mailer::class);
        $mailer->shouldNotReceive('to');
        $mailer->shouldNotReceive('send');

        $job = new SendEoReportEmail($delivery->id);
        $job->handle($mailer);

        $delivery->refresh();
        $this->assertSame('failed', $delivery->status);
        $this->assertSame('invalid_email', $delivery->failure_code);
    }

    public function test_monitor_command_notifies_eo_after_five_minutes(): void
    {
        $eo = User::factory()->create(['role' => 'eo']);
        $event = Event::factory()->create(['user_id' => $eo->id]);

        $delivery = EoReportEmailDelivery::create([
            'event_id' => $event->id,
            'eo_user_id' => $eo->id,
            'triggered_by_user_id' => $eo->id,
            'to_email' => 'eo@example.com',
            'to_name' => 'EO',
            'subject' => 'Laporan Event',
            'report_type' => 'event_report',
            'filters' => [],
            'queue' => 'emails-reports',
            'status' => 'failed',
            'attempts' => 1,
            'first_attempt_at' => now()->subMinutes(6),
            'last_attempt_at' => now()->subMinutes(6),
            'failure_code' => 'transport_error',
            'failure_message' => 'Connection timeout',
        ]);

        $this->artisan('eo-report-emails:monitor')->assertExitCode(0);

        $delivery->refresh();
        $this->assertNotNull($delivery->failure_notified_at);

        $notif = Notification::query()
            ->where('user_id', $eo->id)
            ->where('reference_type', EoReportEmailDelivery::class)
            ->where('reference_id', $delivery->id)
            ->first();

        $this->assertNotNull($notif);
        $this->assertSame('eo_report_email_failed', $notif->type);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
