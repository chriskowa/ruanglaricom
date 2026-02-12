<?php

namespace Tests\Feature\Tools;

use App\Http\Middleware\VerifyCsrfToken;
use App\Models\Race;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RaceMasterApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_docs_endpoint_available(): void
    {
        $this->withoutMiddleware(VerifyCsrfToken::class);

        $this->get('/api/tools/race-master/docs')
            ->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['endpoints']);
    }

    public function test_can_create_race_with_logo_validation_and_start_session_flow(): void
    {
        Storage::fake('public');
        Storage::fake('local');

        $this->withoutMiddleware(VerifyCsrfToken::class);

        $logo = UploadedFile::fake()->image('logo.jpg', 300, 300)->size(500);

        $resp = $this->post('/api/tools/race-master/races', [
            'name' => 'Race Master Test',
            'logo' => $logo,
        ]);

        $resp->assertStatus(200)->assertJsonPath('success', true);
        $raceId = $resp->json('race.id');
        $this->assertNotNull($raceId);
        $race = Race::query()->findOrFail($raceId);
        $this->assertNotNull($race->logo_path);
        Storage::disk('public')->assertExists($race->logo_path);

        $this->postJson("/api/tools/race-master/races/{$raceId}/participants/bulk", [
            'participants' => [
                ['bib_number' => '101', 'name' => 'Runner A', 'predicted_time_ms' => 1500000],
                ['bib_number' => '102', 'name' => 'Runner B', 'predicted_time_ms' => 1700000],
            ],
        ])->assertStatus(200)->assertJsonPath('success', true);

        $sessionResp = $this->postJson("/api/tools/race-master/races/{$raceId}/sessions");
        $sessionResp->assertStatus(200)->assertJsonPath('success', true);
        $sessionId = $sessionResp->json('session.id');
        $this->assertNotNull($sessionId);

        $this->postJson("/api/tools/race-master/sessions/{$sessionId}/laps", [
            'bib_number' => '101',
            'total_time_ms' => 300000,
            'recorded_at' => now()->toISOString(),
        ])->assertStatus(200)->assertJsonPath('success', true);

        $finish = $this->postJson("/api/tools/race-master/sessions/{$sessionId}/finish");
        $finish->assertStatus(200)->assertJsonPath('success', true);
        $this->assertGreaterThanOrEqual(1, (int) $finish->json('certificates_generated'));

        $certs = $finish->json('certificates');
        $this->assertIsArray($certs);
        $this->assertNotEmpty($certs);
        $this->assertArrayHasKey('download_url', $certs[0]);
    }

    public function test_certificate_download_requires_authentication(): void
    {
        Storage::fake('public');
        Storage::fake('local');
        $this->withoutMiddleware(VerifyCsrfToken::class);

        $raceResp = $this->post('/api/tools/race-master/races', [
            'name' => 'Race Download Test',
        ]);
        $raceId = $raceResp->json('race.id');
        $sessionId = $this->postJson("/api/tools/race-master/races/{$raceId}/sessions")->json('session.id');

        $this->postJson("/api/tools/race-master/races/{$raceId}/participants/bulk", [
            'participants' => [
                ['bib_number' => '201', 'name' => 'Runner C'],
            ],
        ]);
        $this->postJson("/api/tools/race-master/sessions/{$sessionId}/laps", [
            'bib_number' => '201',
            'total_time_ms' => 1000,
        ]);

        $finish = $this->postJson("/api/tools/race-master/sessions/{$sessionId}/finish");
        $certs = $finish->json('certificates');
        $downloadUrl = $certs[0]['download_url'] ?? null;
        $this->assertNotNull($downloadUrl);

        $this->get($downloadUrl)->assertRedirect();

        $user = User::factory()->create();
        $this->actingAs($user);
        $download = $this->get($downloadUrl);
        $download->assertStatus(200);
        $this->assertStringContainsString('application/pdf', (string) $download->headers->get('content-type'));
    }

    public function test_poster_generation_returns_png_fast(): void
    {
        Storage::fake('public');
        Storage::fake('local');
        $this->withoutMiddleware(VerifyCsrfToken::class);

        $raceId = $this->post('/api/tools/race-master/races', [
            'name' => 'Race Poster Test',
        ])->json('race.id');

        $this->postJson("/api/tools/race-master/races/{$raceId}/participants/bulk", [
            'participants' => [
                ['bib_number' => '301', 'name' => 'Runner A'],
                ['bib_number' => '302', 'name' => 'Runner B'],
            ],
        ]);

        $sessionId = $this->postJson("/api/tools/race-master/races/{$raceId}/sessions")->json('session.id');
        $this->postJson("/api/tools/race-master/sessions/{$sessionId}/laps", ['bib_number' => '301', 'total_time_ms' => 1000]);
        $this->postJson("/api/tools/race-master/sessions/{$sessionId}/laps", ['bib_number' => '302', 'total_time_ms' => 2000]);
        $this->postJson("/api/tools/race-master/sessions/{$sessionId}/finish");

        $bg = UploadedFile::fake()->image('bg.jpg', 1080, 1920)->size(1000);

        $t0 = microtime(true);
        $resp = $this->post("/api/tools/race-master/sessions/{$sessionId}/poster", [
            'background' => $bg,
        ]);
        $t1 = microtime(true);

        $resp->assertStatus(200)->assertHeader('content-type', 'image/png');
        $this->assertLessThan(3.0, $t1 - $t0);
        $this->assertNotEmpty($resp->getContent());
    }

    public function test_public_results_page_and_public_media_endpoints_work(): void
    {
        Storage::fake('public');
        Storage::fake('local');
        $this->withoutMiddleware(VerifyCsrfToken::class);

        $raceId = $this->post('/api/tools/race-master/races', [
            'name' => 'Race Public Test',
        ])->json('race.id');

        $this->postJson("/api/tools/race-master/races/{$raceId}/participants/bulk", [
            'participants' => [
                ['bib_number' => '101', 'name' => 'Runner A'],
                ['bib_number' => '102', 'name' => 'Runner B'],
            ],
        ]);

        $sessionResp = $this->postJson("/api/tools/race-master/races/{$raceId}/sessions", [
            'category' => '10K',
            'distance_km' => 10,
        ]);
        $sessionId = $sessionResp->json('session.id');
        $this->assertNotNull($sessionId);

        $this->postJson("/api/tools/race-master/sessions/{$sessionId}/laps", ['bib_number' => '101', 'total_time_ms' => 1000]);
        $finish = $this->postJson("/api/tools/race-master/sessions/{$sessionId}/finish");

        $slug = $finish->json('session.slug');
        $this->assertNotEmpty($slug);

        $this->get("/tools/race-master/results/{$slug}")->assertStatus(200);

        $json = $this->get("/api/tools/race-master/public/{$slug}/results");
        $json->assertStatus(200)->assertJsonPath('success', true);
        $json->assertJsonPath('session.slug', $slug);
        $json->assertJsonPath('session.distance_km', 10);

        $bg = UploadedFile::fake()->image('bg.jpg', 1080, 1920)->size(1000);
        $poster = $this->post("/api/tools/race-master/public/{$slug}/participants/101/poster", [
            'background' => $bg,
        ]);
        $poster->assertStatus(200)->assertHeader('content-type', 'image/png');

        $cert = $this->post("/api/tools/race-master/public/{$slug}/participants/101/certificate");
        $cert->assertStatus(200)->assertJsonPath('success', true);
        $downloadUrl = $cert->json('certificate.download_url');
        $this->assertNotEmpty($downloadUrl);

        $download = $this->get($downloadUrl);
        $download->assertStatus(200);
        $this->assertStringContainsString('application/pdf', (string) $download->headers->get('content-type'));
    }

    public function test_public_endpoints_exempt_from_csrf(): void
    {
        // Setup data directly to avoid needing CSRF for setup
        Storage::fake('public');
        Storage::fake('local');

        $user = User::factory()->create();
        $race = Race::create(['name' => 'CSRF Test', 'created_by' => $user->id]);
        $session = $race->sessions()->create([
            'slug' => 'csrf-test-slug',
            'started_at' => now(),
            'ended_at' => now(),
            'created_by' => $user->id,
        ]);

        $participant = $race->participants()->create([
            'race_id' => $race->id,
            'bib_number' => '999',
            'name' => 'CSRF Runner',
        ]);

        $session->laps()->create([
            'race_id' => $race->id,
            'participant_id' => $participant->participant_id,
            'race_session_participant_id' => $participant->id,
            'lap_number' => 1,
            'lap_time_ms' => 1000,
            'total_time_ms' => 1000,
            'recorded_at' => now(),
        ]);

        // Try to access public poster endpoint WITHOUT CSRF token
        // This should pass because we added it to exceptions in bootstrap/app.php
        $bg = UploadedFile::fake()->image('bg.jpg', 1080, 1920)->size(1000);
        $poster = $this->post("/api/tools/race-master/public/{$session->slug}/participants/999/poster", [
            'background' => $bg,
        ]);

        $poster->assertStatus(200);

        // Try to access public certificate endpoint WITHOUT CSRF token
        $cert = $this->post("/api/tools/race-master/public/{$session->slug}/participants/999/certificate");
        $cert->assertStatus(200);
    }
}
