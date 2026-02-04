<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventSubmission;
use App\Models\EventSubmissionOtp;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Tests\TestCase;

class EventSubmissionFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_can_submit_event_after_valid_otp(): void
    {
        Mail::fake();
        Http::fake([
            'https://www.google.com/recaptcha/api/siteverify' => Http::response(['success' => true], 200),
        ]);

        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $email = 'submitter@example.com';

        $otp = EventSubmissionOtp::create([
            'id' => (string) Str::uuid(),
            'email' => $email,
            'code_hash' => Hash::make('123456'),
            'expires_at' => now()->addMinutes(10),
            'attempts' => 0,
            'max_attempts' => 5,
            'used_at' => null,
            'ip_hash' => hash('sha256', '127.0.0.1|'.(string) config('app.key')),
            'ua_hash' => hash('sha256', 'Symfony|'.(string) config('app.key')),
        ]);

        $payload = [
            'otp_id' => $otp->id,
            'otp_code' => '123456',
            'event_name' => 'Test Run',
            'event_date' => now()->addDays(7)->toDateString(),
            'start_time' => '06:00',
            'location_name' => 'GBK',
            'location_address' => 'Jakarta',
            'city_id' => null,
            'city_text' => 'Jakarta',
            'race_type_id' => null,
            'race_distance_ids' => [],
            'registration_link' => 'https://example.com/register',
            'social_media_link' => 'https://instagram.com/example',
            'organizer_name' => 'EO Test',
            'organizer_contact' => 'wa.me/628123',
            'contributor_name' => 'Submitter',
            'contributor_email' => $email,
            'contributor_phone' => '08123',
            'notes' => 'Some notes',
            'started_at' => (int) floor(microtime(true) * 1000) - 10_000,
            'website' => '',
            'g-recaptcha-response' => 'token',
        ];

        $res = $this->withHeaders(['User-Agent' => 'Symfony'])
            ->postJson(route('events.submissions.store'), $payload);

        $res->assertOk()->assertJson([
            'success' => true,
        ]);

        $this->assertDatabaseHas('event_submissions', [
            'event_name' => 'Test Run',
            'contributor_email' => $email,
            'status' => 'pending',
        ]);

        $submissionId = EventSubmission::query()->where('contributor_email', $email)->value('id');
        $this->assertNotNull($submissionId);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $admin->id,
            'reference_type' => 'EventSubmission',
            'reference_id' => $submissionId,
            'is_read' => 0,
        ]);

        $this->assertNull(Notification::query()->where('user_id', $admin->id)->value('read_at'));
    }

    public function test_admin_can_approve_submission_and_create_event(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $submission = EventSubmission::create([
            'status' => 'pending',
            'event_name' => 'Approve Run',
            'event_date' => now()->addDays(10)->toDateString(),
            'start_time' => '07:00',
            'location_name' => 'Test Location',
            'location_address' => 'Test Address',
            'city_id' => null,
            'city_text' => 'Bandung',
            'race_type_id' => null,
            'race_distance_ids' => [],
            'registration_link' => 'https://example.com/r',
            'social_media_link' => null,
            'organizer_name' => null,
            'organizer_contact' => null,
            'contributor_name' => 'Someone',
            'contributor_email' => 'someone@example.com',
            'contributor_phone' => null,
            'notes' => 'Notes',
            'fingerprint' => hash('sha256', 'x'),
        ]);

        $res = $this->actingAs($admin)
            ->post(route('admin.event-submissions.approve', $submission), [
                'review_note' => 'OK',
                'publish' => 1,
            ]);

        $res->assertRedirect(route('admin.event-submissions.index'));

        $this->assertDatabaseHas('event_submissions', [
            'id' => $submission->id,
            'status' => 'approved',
            'reviewed_by' => $admin->id,
        ]);

        $this->assertDatabaseHas('events', [
            'name' => 'Approve Run',
            'event_kind' => 'directory',
            'status' => 'published',
        ]);

        $event = Event::query()->where('name', 'Approve Run')->first();
        $this->assertNotNull($event);
        $this->assertNotEmpty($event->slug);
    }
}
