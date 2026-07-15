<?php

namespace App\Services\RunningAnalysis;

use App\Models\RunningAnalysis\Trial;
use App\Models\RunningAnalysis\Metric;
use App\Models\RunningAnalysis\Recommendation;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\View;

class TrialPdfExportService
{
    /**
     * Generate a Dompdf instance rendered with the trial's report data.
     */
    public function generate(Trial $trial): Dompdf
    {
        $data = $this->prepareData($trial);

        $html = View::make('admin.running-analysis.trials.pdf', $data)->render();

        $options = new Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', false);
        $options->set('defaultMediaType', 'print');
        $options->set('isFontSubsettingEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $dompdf;
    }

    /**
     * Normalise all trial data into a flat array for the Blade PDF template.
     */
    public function prepareData(Trial $trial): array
    {
        $report    = $trial->latestReport;
        $narrative = $report?->runner_narrative_json ?? [];
        $summary   = $report?->deterministic_summary_json ?? [];

        // Form score (0–100)
        $score = $trial->quality_score ? (int) round((float) $trial->quality_score * 100) : null;

        // Coach narrative
        $coachMessage = $narrative['coach_message'] ?? null;
        if (is_array($coachMessage)) {
            $coachMessage = implode(' ', array_values($coachMessage));
        }
        $positives = $narrative['positives'] ?? [];

        // Phase form report from deterministic_summary_json['form_report']
        $formReport = $summary['form_report'] ?? [];

        // Biomechanical metrics grouped by category
        $metrics = $trial->metrics->sortBy('metric_code')->values();

        $metricsByCategory = $metrics->groupBy('category');

        // Findings sorted by severity
        $severityOrder = ['significant' => 0, 'moderate' => 1, 'minor' => 2];
        $findings = $trial->findings
            ->sortBy(fn($f) => $severityOrder[$f->severity] ?? 9)
            ->values();

        // Recommendations split by type
        $cues      = $trial->recommendations->where('type', Recommendation::TYPE_CUE)->values();
        $drills    = $trial->recommendations->where('type', Recommendation::TYPE_DRILL)->values();
        $strengths = $trial->recommendations->where('type', Recommendation::TYPE_STRENGTH)->values();

        // Gait events sorted by time
        $sortedEvents  = $trial->gaitEvents->sortBy('timestamp_ms')->values();
        $eventBaseMs   = $sortedEvents->min('timestamp_ms') ?? 0;

        // Video metadata
        $videoMeta = null;
        if ($trial->camera_width) {
            $videoMeta = $trial->camera_width . '×' . $trial->camera_height
                . ' @ ' . $trial->camera_fps . ' fps';
        }

        // Generated date
        $generatedAt = now()->timezone('Asia/Jakarta')->format('d F Y, H:i') . ' WIB';

        return compact(
            'trial',
            'score',
            'coachMessage',
            'positives',
            'formReport',
            'metrics',
            'metricsByCategory',
            'findings',
            'cues',
            'drills',
            'strengths',
            'sortedEvents',
            'eventBaseMs',
            'videoMeta',
            'generatedAt',
        );
    }
}
