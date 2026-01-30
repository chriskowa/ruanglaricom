<?php

namespace Tests\Feature;

use App\Http\Middleware\VerifyCsrfToken;
use App\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RunningEventRatingDuplicateDetectionTest extends TestCase
{
    use RefreshDatabase;

    private function submitRating(Event $event, array $opts): \Illuminate\Testing\TestResponse
    {
        $cookie = $opts['cookie'] ?? 'cookie-a';
        $ip = $opts['ip'] ?? '1.1.1.1';
        $fingerprint = $opts['fingerprint'] ?? 'fp-a-12345678';
        $rating = $opts['rating'] ?? 5;

        return $this->withCredentials()
            ->withCookie('rl_rating_id', $cookie)
            ->withServerVariables(['REMOTE_ADDR' => $ip])
            ->postJson(route('api.running-events.rating.store', $event->slug), [
                'rating' => $rating,
                'fingerprint' => $fingerprint,
            ]);
    }

    public function test_first_rating_is_accepted(): void
    {
        $this->withoutMiddleware(VerifyCsrfToken::class);

        $event = Event::factory()->create([
            'status' => 'published',
        ]);

        $this->submitRating($event, [
            'cookie' => 'cookie-a',
            'ip' => '1.1.1.1',
            'fingerprint' => 'fp-a-12345678',
            'rating' => 5,
        ])->assertOk()
            ->assertJsonPath('rating_count', 1);

        $this->assertDatabaseCount('event_ratings', 1);
    }

    public function test_duplicate_detected_when_cookie_and_ip_match(): void
    {
        $this->withoutMiddleware(VerifyCsrfToken::class);

        $event = Event::factory()->create(['status' => 'published']);

        $this->submitRating($event, [
            'cookie' => 'cookie-a',
            'ip' => '1.1.1.1',
            'fingerprint' => 'fp-a-12345678',
        ])->assertOk();

        $this->submitRating($event, [
            'cookie' => 'cookie-a',
            'ip' => '1.1.1.1',
            'fingerprint' => 'fp-b-87654321',
        ])->assertStatus(409);
    }

    public function test_duplicate_detected_when_cookie_and_fingerprint_match(): void
    {
        $this->withoutMiddleware(VerifyCsrfToken::class);

        $event = Event::factory()->create(['status' => 'published']);

        $this->submitRating($event, [
            'cookie' => 'cookie-a',
            'ip' => '1.1.1.1',
            'fingerprint' => 'fp-a-12345678',
        ])->assertOk();

        $this->submitRating($event, [
            'cookie' => 'cookie-a',
            'ip' => '2.2.2.2',
            'fingerprint' => 'fp-a-12345678',
        ])->assertStatus(409);
    }

    public function test_duplicate_detected_when_ip_and_fingerprint_match(): void
    {
        $this->withoutMiddleware(VerifyCsrfToken::class);

        $event = Event::factory()->create(['status' => 'published']);

        $this->submitRating($event, [
            'cookie' => 'cookie-a',
            'ip' => '1.1.1.1',
            'fingerprint' => 'fp-a-12345678',
        ])->assertOk();

        $this->submitRating($event, [
            'cookie' => 'cookie-b',
            'ip' => '1.1.1.1',
            'fingerprint' => 'fp-a-12345678',
        ])->assertStatus(409);
    }

    public function test_not_duplicate_when_only_one_identifier_matches(): void
    {
        $this->withoutMiddleware(VerifyCsrfToken::class);

        $event = Event::factory()->create(['status' => 'published']);

        $this->submitRating($event, [
            'cookie' => 'cookie-a',
            'ip' => '1.1.1.1',
            'fingerprint' => 'fp-a-12345678',
        ])->assertOk();

        $this->submitRating($event, [
            'cookie' => 'cookie-a',
            'ip' => '2.2.2.2',
            'fingerprint' => 'fp-b-87654321',
        ])->assertOk();

        $this->assertDatabaseCount('event_ratings', 2);
    }

    public function test_validation_rejects_invalid_payload(): void
    {
        $this->withoutMiddleware(VerifyCsrfToken::class);

        $event = Event::factory()->create(['status' => 'published']);

        $this->withCredentials()
            ->withCookie('rl_rating_id', 'cookie-a')
            ->withServerVariables(['REMOTE_ADDR' => '1.1.1.1'])
            ->postJson(route('api.running-events.rating.store', $event->slug), [
                'rating' => 6,
                'fingerprint' => '',
            ])
            ->assertStatus(422);
    }
}
