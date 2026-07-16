<?php

namespace App\Http\Controllers\Admin\RunningAnalysis;

use App\Http\Controllers\Controller;
use App\Models\RunningAnalysis\Session;
use App\Models\RunningAnalysis\Trial;
use App\Models\RunningAnalysis\Artifact;
use App\Services\RunningAnalysis\ReportBuilder;
use App\Services\RunningAnalysis\TrialPdfExportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TrialController extends Controller
{
    /**
     * Creates a new Trial and returns a pre-signed or direct upload configuration.
     * Guaranteed idempotent by client-provided UUID.
     */
    public function store(Request $request, Session $session)
    {
        $validated = $request->validate([
            'id'                  => 'required|uuid',
            'runner_id'           => 'required|exists:users,id',
            'camera_device_label' => 'nullable|string',
            'camera_width'        => 'nullable|integer',
            'camera_height'       => 'nullable|integer',
            'camera_fps'          => 'nullable|numeric',
            'inference_fps'       => 'nullable|numeric',
            'pose_model'          => 'required|string',
        ]);

        $trial = DB::transaction(function () use ($validated, $session) {
            $trial = Trial::firstOrNew(['id' => $validated['id']]);
            
            if (!$trial->exists) {
                // Get attempt number
                $attemptNo = Trial::where('session_id', $session->id)
                    ->where('runner_id', $validated['runner_id'])
                    ->count() + 1;

                $trial->fill([
                    'session_id'          => $session->id,
                    'runner_id'           => $validated['runner_id'],
                    'operator_id'         => auth()->id(),
                    'attempt_no'          => $attemptNo,
                    'direction'           => Trial::DIRECTION_UNKNOWN,
                    'status'              => Trial::STATUS_CAPTURING,
                    'camera_device_label' => $validated['camera_device_label'] ?? null,
                    'camera_width'        => $validated['camera_width'] ?? null,
                    'camera_height'       => $validated['camera_height'] ?? null,
                    'camera_fps'          => $validated['camera_fps'] ?? null,
                    'inference_fps'       => $validated['inference_fps'] ?? null,
                    'pose_model'          => $validated['pose_model'] ?? 'pose_landmarker',
                    'capture_version'     => '1.0',
                ]);
                $trial->save();
            }
            
            return $trial;
        });

        return response()->json([
            'trial_id' => $trial->id,
            'status'   => $trial->status,
        ]);
    }

    /**
     * Upload an artifact chunk or full file for a Trial.
     */
    public function uploadArtifact(Request $request, Trial $trial)
    {
        $validated = $request->validate([
            'type'   => 'required|string|in:pose_landmarks,video_clip,preview_image',
            'file'   => 'required|file',
            'sha256' => 'required|string|size:64',
        ]);

        $file = $request->file('file');
        $hash = hash_file('sha256', $file->path());

        if ($hash !== $validated['sha256']) {
            return response()->json(['error' => 'Checksum mismatch', 'server_hash' => $hash, 'client_hash' => $validated['sha256']], 400);
        }

        // Determine extension from type
        $extensionMap = [
            'pose_landmarks'  => 'json',
            'video_clip'      => 'webm',
            'preview_image'   => 'gif',
        ];
        $ext = $file->getClientOriginalExtension() ?: ($extensionMap[$validated['type']] ?? 'bin');

        // Delete existing artifact of the same type to keep it clean on retries
        $existing = $trial->artifacts()->where('type', $validated['type'])->first();
        if ($existing) {
            \Illuminate\Support\Facades\Storage::disk($existing->disk)->delete($existing->path);
            $existing->delete();
        }

        $path = $file->storeAs(
            'running-analysis/' . $trial->id,
            $validated['type'] . '_' . time() . '.' . $ext,
            'local'
        );

        $trial->artifacts()->create([
            'type'        => $validated['type'],
            'disk'        => 'local',
            'path'        => $path,
            'mime_type'   => $file->getClientMimeType(),
            'sha256'      => $hash,
            'size_bytes'  => $file->getSize(),
            'created_at'  => now(),
        ]);

        return response()->json(['status' => 'uploaded']);
    }

    /**
     * Finalize the upload sequence and queue for analysis.
     */
    public function finalize(Request $request, Trial $trial, ReportBuilder $builder)
    {
        if (in_array($trial->status, [Trial::STATUS_QUEUED, Trial::STATUS_ANALYZING, Trial::STATUS_REVIEW_REQUIRED, Trial::STATUS_APPROVED, Trial::STATUS_PUBLISHED])) {
            return response()->json(['status' => 'already_finalized']);
        }

        if ($trial->status !== Trial::STATUS_CAPTURING) {
            return response()->json(['error' => 'Trial is not in capturing state.'], 400);
        }

        // Validate artifacts exist
        if (!$trial->artifacts()->where('type', 'pose_landmarks')->exists()) {
            return response()->json(['error' => 'Missing pose data artifact'], 400);
        }

        $isSync = filter_var($request->input('sync'), FILTER_VALIDATE_BOOLEAN);

        if ($isSync) {
            @set_time_limit(300);
            try {
                $trial->update(['status' => Trial::STATUS_ANALYZING]);
                $builder->process($trial);
                $trial->update(['status' => Trial::STATUS_REVIEW_REQUIRED]);
            } catch (\Exception $e) {
                $trial->update([
                    'status'         => Trial::STATUS_CAPTURING, // fallback
                    'invalid_reason' => $e->getMessage(),
                ]);
                return response()->json(['error' => 'Gagal menganalisis secara langsung: ' . $e->getMessage()], 500);
            }
        } else {
            $trial->update(['status' => Trial::STATUS_QUEUED]);
            // Queue analysis job
            dispatch(new \App\Jobs\RunningAnalysis\AnalyzeTrialJob($trial));
        }

        // Update session runner status
        $sessionRunner = \App\Models\RunningAnalysis\SessionRunner::where('session_id', $trial->session_id)
            ->where('runner_id', $trial->runner_id)
            ->first();
            
        if ($sessionRunner) {
            $sessionRunner->update(['status' => 'captured']);
        }

        return response()->json(['status' => $trial->status]);
    }

    /**
     * Display the review details page for a trial.
     */
    public function review(Trial $trial)
    {
        $trial->load([
            'runner', 
            'session', 
            'artifacts', 
            'metrics', 
            'findings', 
            'recommendations',
            'latestReport'
        ]);

        $poseData = null;
        $poseArtifact = $trial->artifacts->where('type', 'pose_landmarks')->first();
        
        if ($poseArtifact && \Illuminate\Support\Facades\Storage::disk($poseArtifact->disk)->exists($poseArtifact->path)) {
            $poseData = \Illuminate\Support\Facades\Storage::disk($poseArtifact->disk)->get($poseArtifact->path);
        }

        return view('admin.running-analysis.trials.review', compact('trial', 'poseData'));
    }

    /**
     * Generate and download a PDF report for a trial.
     */
    public function downloadPdf(Trial $trial): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $trial->load([
            'runner',
            'session',
            'artifacts',
            'metrics',
            'findings',
            'recommendations',
            'gaitEvents',
            'latestReport',
        ]);

        $pdf      = app(TrialPdfExportService::class)->generate($trial);
        $filename = 'running-analysis-'
            . \Illuminate\Support\Str::slug($trial->runner->name)
            . '-attempt-' . $trial->attempt_no
            . '.pdf';

        return response()->streamDownload(
            fn () => print($pdf->output()),
            $filename,
            ['Content-Type' => 'application/pdf']
        );
    }

    /**
     * Reject a trial (mark as invalid).
     */
    public function reject(Request $request, Trial $trial)
    {
        $validated = $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        $trial->update([
            'status'         => Trial::STATUS_INVALID,
            'invalid_reason' => $validated['reason'] ?? 'Rejected by admin.',
        ]);

        return redirect()
            ->route('admin.running-analysis.trials.review', $trial)
            ->with('success', 'Trial has been rejected.');
    }

    public function approve(Trial $trial)
    {
        $trial->update([
            'status'      => Trial::STATUS_PUBLISHED,
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'published_at'=> now(),
        ]);

        // Create Database Notification for Runner
        \App\Models\Notification::create([
            'user_id'        => $trial->runner_id,
            'type'           => 'running_analysis',
            'title'          => 'Running Form Analysis Published',
            'message'        => 'Analisis lari Anda (Attempt #' . $trial->attempt_no . ') telah selesai dinilai dan dipublikasikan.',
            'reference_type' => \App\Models\RunningAnalysis\Trial::class,
            'reference_id'   => $trial->id,
            'is_read'        => false,
        ]);

        return redirect()
            ->route('admin.running-analysis.trials.review', $trial)
            ->with('success', 'Trial has been approved and published.');
    }

    /**
     * Serve an artifact file from local storage.
     */
    public function serveArtifact(Trial $trial, Artifact $artifact)
    {
        abort_unless($artifact->trial_id === $trial->id, 404);

        $disk = \Illuminate\Support\Facades\Storage::disk($artifact->disk);
        if (!$disk->exists($artifact->path)) {
            abort(404, 'Artifact file not found.');
        }

        // For remote/cloud storage (like S3), stream via temporary redirect URL
        if ($artifact->disk === 's3') {
            $url = $disk->temporaryUrl($artifact->path, now()->addMinutes(60));
            return redirect()->away($url);
        }

        $path = $disk->path($artifact->path);
        
        if (!file_exists($path)) {
            abort(404, 'Artifact file does not exist on disk.');
        }

        $fileSize = filesize($path);
        $mime = $artifact->mime_type ?: 'video/mp4';

        $fp = @fopen($path, 'rb');
        if (!$fp) {
            abort(500, 'Cannot open file.');
        }

        $size = $fileSize;
        $start = 0;
        $end = $size - 1;

        $headers = [
            'Content-Type' => $mime,
            'Accept-Ranges' => 'bytes',
        ];

        // Clean out any output buffers that might interfere with streaming
        if (ob_get_level()) {
            ob_end_clean();
        }

        if (request()->headers->has('Range')) {
            $range = request()->header('Range');
            if (preg_match('/bytes=\s*(\d+)-(\d*)/', $range, $matches)) {
                $start = intval($matches[1]);
                if (!empty($matches[2])) {
                    $end = intval($matches[2]);
                }
            }

            if ($start > $end || $start >= $size) {
                fclose($fp);
                return response('Requested Range Not Satisfiable', 416, [
                    'Content-Range' => "bytes */$size"
                ]);
            }

            $length = $end - $start + 1;
            fseek($fp, $start);

            $headers['Content-Length'] = $length;
            $headers['Content-Range'] = "bytes $start-$end/$size";

            return response()->stream(function () use ($fp, $length) {
                $buffer = 1024 * 8;
                $bytes_sent = 0;
                while (!feof($fp) && $bytes_sent < $length) {
                    $to_read = min($buffer, $length - $bytes_sent);
                    $data = fread($fp, $to_read);
                    echo $data;
                    flush();
                    $bytes_sent += strlen($data);
                }
                fclose($fp);
            }, 206, $headers);
        }

        $headers['Content-Length'] = $size;
        return response()->stream(function () use ($fp) {
            $buffer = 1024 * 8;
            while (!feof($fp)) {
                echo fread($fp, $buffer);
                flush();
            }
            fclose($fp);
        }, 200, $headers);
    }

    /**
     * Force synchronous execution of the analysis rules engine (bypassing asynchronous queues).
     */
    public function analyzeSync(Trial $trial, ReportBuilder $builder)
    {
        @set_time_limit(300);

        try {
            $trial->update(['status' => Trial::STATUS_ANALYZING]);
            
            $builder->process($trial);

            return redirect()->back()->with('success', 'Analysis processed successfully.');
        } catch (\Exception $e) {
            $trial->update([
                'status' => Trial::STATUS_FAILED,
                'invalid_reason' => $e->getMessage()
            ]);

            return redirect()->back()->with('error', 'Analysis failed: ' . $e->getMessage());
        }
    }

    /**
     * Display the review details page for a runner (only if it belongs to them and is published).
     */
    public function runnerReview(Trial $trial)
    {
        abort_unless($trial->runner_id === auth()->id(), 403, 'Unauthorized.');
        abort_unless($trial->status === Trial::STATUS_PUBLISHED, 403, 'Trial is not published yet.');

        $trial->load([
            'runner', 
            'session', 
            'artifacts', 
            'metrics', 
            'findings', 
            'recommendations',
            'latestReport'
        ]);

        $poseData = null;
        $poseArtifact = $trial->artifacts->where('type', 'pose_landmarks')->first();
        
        if ($poseArtifact && \Illuminate\Support\Facades\Storage::disk($poseArtifact->disk)->exists($poseArtifact->path)) {
            $poseData = \Illuminate\Support\Facades\Storage::disk($poseArtifact->disk)->get($poseArtifact->path);
        }

        return view('admin.running-analysis.trials.review', compact('trial', 'poseData'));
    }

    /**
     * Serve an artifact file from local storage for a runner (only if it belongs to them and is published).
     */
    public function serveRunnerArtifact(Trial $trial, Artifact $artifact)
    {
        abort_unless($artifact->trial_id === $trial->id, 404);
        abort_unless($trial->runner_id === auth()->id(), 403, 'Unauthorized.');
        abort_unless($trial->status === Trial::STATUS_PUBLISHED, 403, 'Trial is not published yet.');

        return $this->serveArtifact($trial, $artifact);
    }

    /**
     * Download PDF report for a runner (only their own published trials).
     */
    public function runnerDownloadPdf(Trial $trial): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        abort_unless($trial->runner_id === auth()->id(), 403, 'Unauthorized.');
        abort_unless($trial->status === Trial::STATUS_PUBLISHED, 403, 'Trial is not published yet.');

        $trial->load([
            'runner',
            'session',
            'artifacts',
            'metrics',
            'findings',
            'recommendations',
            'gaitEvents',
            'latestReport',
        ]);

        $pdf      = app(TrialPdfExportService::class)->generate($trial);
        $filename = 'running-analysis-'
            . \Illuminate\Support\Str::slug($trial->runner->name)
            . '-attempt-' . $trial->attempt_no
            . '.pdf';

        return response()->streamDownload(
            fn () => print($pdf->output()),
            $filename,
            ['Content-Type' => 'application/pdf']
        );
    }
}
