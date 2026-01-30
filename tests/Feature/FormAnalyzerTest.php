<?php

namespace Tests\Feature;

use App\Http\Middleware\VerifyCsrfToken;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class FormAnalyzerTest extends TestCase
{
    private function makeJsonStringOfLength(int $targetLength): string
    {
        $prefix = '{"x":"';
        $suffix = '"}';
        $fill = $targetLength - strlen($prefix) - strlen($suffix);
        if ($fill < 0) {
            $fill = 0;
        }
        return $prefix . str_repeat('a', $fill) . $suffix;
    }

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
                'form_report',
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
            ->assertJsonPath('form_report.0.code', 'landing')
            ->assertJsonStructure([
                'ok',
                'score',
                'form_issues',
                'form_report',
                'strength_plan',
                'recovery_plan',
                'coach_message',
            ]);
    }

    public function test_analyze_accepts_metrics_at_20000_chars(): void
    {
        $this->withoutMiddleware(VerifyCsrfToken::class);

        $metrics = $this->makeJsonStringOfLength(20000);
        $this->assertSame(20000, strlen($metrics));

        $this->postJson(route('tools.form-analyzer.analyze'), [
            'metrics' => $metrics,
            'client_duration' => 9,
            'client_width' => 1280,
            'client_height' => 720,
        ])->assertOk()
            ->assertJsonPath('ok', true);
    }

    public function test_analyze_rejects_metrics_over_20000_chars(): void
    {
        $this->withoutMiddleware(VerifyCsrfToken::class);

        $metrics = $this->makeJsonStringOfLength(20001);
        $this->assertSame(20001, strlen($metrics));

        $this->postJson(route('tools.form-analyzer.analyze'), [
            'metrics' => $metrics,
            'client_duration' => 9,
            'client_width' => 1280,
            'client_height' => 720,
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['metrics']);
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
            $file = UploadedFile::fake()->create('run.mp4', 1024, 'video/mp4');
            $this->postJson(route('tools.form-analyzer.analyze'), [
                'video' => $file,
                'client_duration' => 8.5,
                'client_width' => 1280,
                'client_height' => 720,
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
