<?php

namespace App\Http\Controllers\Api\V1;

use App\Services\RunningAnalysis\BiomechanicsAnalysisService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AnalysisApiController extends BaseApiController
{
    /**
     * Upload running video from mobile app camera/gallery & analyze running form
     */
    public function upload(Request $request): JsonResponse
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'video' => [
                'required',
                'file',
                'max:153600', // max 150MB
                'mimetypes:video/mp4,video/quicktime,video/webm,video/x-matroska',
                'mimes:mp4,mov,webm,mkv',
            ],
            'metrics' => 'nullable|string|max:20000',
            'client_duration' => 'nullable|numeric|min:0|max:3600',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validasi upload video lari gagal', 422, $validator->errors());
        }

        try {
            $file = $request->file('video');
            $fileName = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('running_videos/' . $user->id, $fileName, 'public');
            $videoUrl = asset('storage/' . $path);

            $metrics = [];
            if ($request->filled('metrics')) {
                $decoded = json_decode($request->metrics, true);
                if (is_array($decoded)) {
                    $metrics = $decoded;
                }
            }

            /** @var BiomechanicsAnalysisService $analysisService */
            $analysisService = app(BiomechanicsAnalysisService::class);
            $report = $analysisService->analyze($metrics);

            // Save report to runner audit history
            $audit = $user->audit_history ?? [];
            $reports = $audit['running_analysis_reports'] ?? [];

            $reportId = 'rep_' . time() . '_' . Str::random(6);
            $reportRecord = [
                'id' => $reportId,
                'video_url' => $videoUrl,
                'created_at' => now()->toISOString(),
                'report' => $report,
            ];

            array_unshift($reports, $reportRecord);
            $reports = array_slice($reports, 0, 20); // Keep last 20 reports
            $audit['running_analysis_reports'] = $reports;
            $user->update(['audit_history' => $audit]);

            return $this->successResponse([
                'report_id' => $reportId,
                'video_url' => $videoUrl,
                'form_report' => $report['form_report'] ?? [],
                'coach_message' => $report['coach_message'] ?? null,
                'strengths' => $report['strengths'] ?? [],
                'action_plan' => $report['action_plan'] ?? [],
                'score' => $report['score'] ?? null,
            ], 'Analisis bentuk lari berhasil diproses', 201);
        } catch (\Throwable $e) {
            return $this->errorResponse('Gagal menganalisis video lari: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get runner's past running form reports
     */
    public function myReports(Request $request): JsonResponse
    {
        $user = $request->user();

        $audit = $user->audit_history ?? [];
        $reports = $audit['running_analysis_reports'] ?? [];

        return $this->successResponse($reports, 'Riwayat analisis lari berhasil dimuat');
    }

    /**
     * Get detail of a specific running form report
     */
    public function showReport(Request $request, string $id): JsonResponse
    {
        $user = $request->user();

        $audit = $user->audit_history ?? [];
        $reports = $audit['running_analysis_reports'] ?? [];

        $found = null;
        foreach ($reports as $r) {
            if (isset($r['id']) && $r['id'] === $id) {
                $found = $r;
                break;
            }
        }

        if (! $found) {
            return $this->errorResponse('Laporan analisis lari tidak ditemukan.', 404);
        }

        return $this->successResponse($found, 'Detail laporan analisis lari berhasil dimuat');
    }
}
