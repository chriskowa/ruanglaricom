<?php

namespace App\Http\Controllers\Admin\RunningAnalysis;

use App\Http\Controllers\Controller;
use App\Models\RunningAnalysis\Session;
use App\Models\RunningAnalysis\Trial;
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
            'type'   => 'required|string|in:pose_landmarks,video_clip',
            'file'   => 'required|file',
            'sha256' => 'required|string|size:64',
        ]);

        $file = $request->file('file');
        $hash = hash_file('sha256', $file->path());

        if ($hash !== $validated['sha256']) {
            return response()->json(['error' => 'Checksum mismatch'], 400);
        }

        $path = $file->storeAs(
            'running-analysis/' . $trial->id,
            $validated['type'] . '_' . time() . '.' . $file->getClientOriginalExtension(),
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
    public function finalize(Request $request, Trial $trial)
    {
        if ($trial->status !== Trial::STATUS_CAPTURING) {
            return response()->json(['error' => 'Trial is not in capturing state.'], 400);
        }

        // Validate artifacts exist
        if (!$trial->artifacts()->where('type', 'pose_landmarks')->exists()) {
            return response()->json(['error' => 'Missing pose data artifact'], 400);
        }

        $trial->update(['status' => Trial::STATUS_QUEUED]);

        // Queue analysis job
        dispatch(new \App\Jobs\RunningAnalysis\AnalyzeTrialJob($trial));

        // Update session runner status
        $sessionRunner = \App\Models\RunningAnalysis\SessionRunner::where('session_id', $trial->session_id)
            ->where('runner_id', $trial->runner_id)
            ->first();
            
        if ($sessionRunner) {
            $sessionRunner->update(['status' => 'captured']);
        }

        return response()->json(['status' => $trial->status]);
    }
}
