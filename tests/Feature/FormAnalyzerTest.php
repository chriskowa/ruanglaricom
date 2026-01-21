<?php

namespace Tests\Feature;

use App\Http\Middleware\VerifyCsrfToken;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class FormAnalyzerTest extends TestCase
{
    public function test_analyze_accepts_video_and_returns_json(): void
    {
        $this->withoutMiddleware(VerifyCsrfToken::class);

        $file = UploadedFile::fake()->create('run.mp4', 1024, 'video/mp4');

        $this->postJson(route('tools.form-analyzer.analyze'), [
            'video' => $file,
            'client_duration' => 8.5,
            'client_width' => 1280,
            'client_height' => 720,
        ])->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonStructure([
                'ok',
                'score',
                'meta' => [
                    'original',
                    'optimized',
                    'display',
                    'compression',
                ],
                'issues',
                'suggestions',
                'positives',
                'form_issues',
                'strength_plan',
                'recovery_plan',
                'coach_message',
                'slot',
            ]);
    }

    public function test_analyze_accepts_metrics_only(): void
    {
        $this->withoutMiddleware(VerifyCsrfToken::class);

        $metrics = json_encode([
            'confidence' => 0.8,
            'samples' => 20,
            'heel_strike_pct' => 75,
            'overstride_pct' => 60,
            'knee_flex_deg' => 15,
            'trunk_lean_deg' => 12,
            'arm_cross_pct' => 50,
        ]);

        $this->postJson(route('tools.form-analyzer.analyze'), [
            'metrics' => $metrics,
            'client_duration' => 9,
            'client_width' => 1280,
            'client_height' => 720,
        ])->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonStructure([
                'ok',
                'score',
                'form_issues',
                'strength_plan',
                'recovery_plan',
                'coach_message',
            ]);
    }

    public function test_analyze_queues_when_slots_full(): void
    {
        $this->withoutMiddleware(VerifyCsrfToken::class);

        $locks = [];
        for ($i = 1; $i <= 5; $i++) {
            $lock = Cache::lock("form_analyzer:slot:{$i}", 120);
            $this->assertTrue($lock->get());
            $locks[] = $lock;
        }

        try {
            $this->postJson(route('tools.form-analyzer.analyze'), [
                'metrics' => json_encode(['confidence' => 0.6, 'samples' => 10]),
            ])->assertStatus(429)
                ->assertJsonPath('queued', true)
                ->assertJsonPath('ok', false);
        } finally {
            foreach ($locks as $l) {
                try { $l->release(); } catch (\Throwable $e) {}
            }
        }
    }

    public function test_analyze_rejects_non_video(): void
    {
        $this->withoutMiddleware(VerifyCsrfToken::class);

        $file = UploadedFile::fake()->create('x.txt', 10, 'text/plain');

        $this->postJson(route('tools.form-analyzer.analyze'), [
            'video' => $file,
        ])->assertStatus(422);
    }
}
