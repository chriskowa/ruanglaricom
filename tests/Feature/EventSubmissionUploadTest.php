<?php

namespace Tests\Feature;

use App\Models\EventSubmissionOtp;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class EventSubmissionUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_can_submit_event_with_banner_and_it_is_resized()
    {
        Storage::fake('public');
        Http::fake([
            'https://www.google.com/recaptcha/api/siteverify' => Http::response(['success' => true], 200),
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

        $file = UploadedFile::fake()->image('banner.jpg', 2000, 1000); // 2000px wide

        $payload = [
            'otp_id' => $otp->id,
            'otp_code' => '123456',
            'event_name' => 'Test Run With Banner',
            'event_date' => now()->addDays(7)->toDateString(),
            'location_name' => 'GBK',
            'contributor_email' => $email,
            'started_at' => (int) floor(microtime(true) * 1000) - 10_000,
            'g-recaptcha-response' => 'token',
            'banner' => $file,
        ];

        $res = $this->withHeaders(['User-Agent' => 'Symfony'])
            ->postJson(route('events.submissions.store'), $payload);

        $res->assertOk()->assertJson([
            'success' => true,
        ]);

        $this->assertDatabaseHas('event_submissions', [
            'event_name' => 'Test Run With Banner',
            'contributor_email' => $email,
        ]);

        // Verify storage
        $files = Storage::disk('public')->allFiles('event-submissions');
        $this->assertCount(1, $files);
        $path = $files[0];
        
        // Assert path ends with .webp (our conversion)
        $this->assertTrue(Str::endsWith($path, '.webp'));
        
        // Assert DB has path
        $this->assertDatabaseHas('event_submissions', [
            'banner' => $path,
        ]);
    }
}
